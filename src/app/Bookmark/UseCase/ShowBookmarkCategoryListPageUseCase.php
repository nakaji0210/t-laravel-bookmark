<?php

namespace App\Bookmark\UseCase;

use App\Models\Bookmark;
use App\Models\BookmarkCategory;
use App\Models\User;
use Artesaos\SEOTools\Facades\SEOTools;

final class ShowBookmarkCategoryListPageUseCase
{
    /**
     * @param int $categoryId
     * @return array
     */
    public function handle($categoryId): array
    {
        $category = BookmarkCategory::query()->findOrFail($categoryId);

        SEOTools::setTitle("{$category->display_name}のブックマーク一覧");
        SEOTools::setDescription("{$category->display_name}に特化したブックマーク一覧です。みんなが投稿した{$category->display_name}のブックマークが投稿順に並んでいます。全部で{$category->bookmarks->count()}件のブックマークが投稿されています");

        $bookmarks = Bookmark::query()
            ->with(['category', 'user'])
            ->where('category_id', '=', $categoryId)
            ->latest('id')
            ->paginate(10);

        // 表示してるページのカテゴリ以外で多い順に表示する
        $topCategories = BookmarkCategory::query()
            ->withCount('bookmarks')
            ->orderBy('bookmarks_count', 'desc')
            ->orderBy('id')
            ->where('id', '<>', $categoryId)
            ->take(10)
            ->get();

        $topUsers = User::query()
            ->withCount('bookmarks')
            ->orderBy('bookmarks_count', 'desc')
            ->take(10)
            ->get();

        return [
            'categoryDisplayName' => $category->display_name,
            'bookmarks' => $bookmarks,
            'topCategories' => $topCategories,
            'topUsers' => $topUsers
        ];
    }
}
