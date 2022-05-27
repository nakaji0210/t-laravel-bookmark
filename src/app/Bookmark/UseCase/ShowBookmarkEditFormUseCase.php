<?php

namespace App\Bookmark\UseCase;

use App\Models\Bookmark;
use App\Models\BookmarkCategory;
use Artesaos\SEOTools\Facades\SEOTools;

final class ShowBookmarkEditFormUseCase
{
    /**
     * @return array
     */
    public function handle($id, $authUserId): array
    {
        SEOTools::setTitle('ブックマーク編集');

        $bookmark = Bookmark::query()->findOrFail($id);
        if ($bookmark->user_id !== $authUserId) {
            abort(403);
        }

        $master_categories = BookmarkCategory::query()->withCount('bookmarks')->orderBy('bookmarks_count', 'desc')->orderBy('id')->take(10)->get();

        // DTOクラスを作るのが理想かも
        return [
            'bookmark' => $bookmark,
            'master_categories' => $master_categories
        ];
    }
}
