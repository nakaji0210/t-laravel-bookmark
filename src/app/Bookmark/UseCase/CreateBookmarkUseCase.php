<?php

namespace App\Bookmark\UseCase;

use App\Lib\LinkPreview\LinkPreview;
use App\Models\Bookmark;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CreateBookmarkUseCase
{
    /**
     * ブックマーク作成処理
     *
     * @param string $url
     * @param int $categoryId
     * @param string $comment
     * @throws ValidationException
     */
    public function handle(string $url, int $categoryId, string $comment): void
    {
        try {
            $linkPreview = (new LinkPreview())->get($url);

            $model = new Bookmark();
            $model->url = $url;
            $model->category_id = $categoryId;
            $model->user_id = Auth::id();
            $model->comment = $comment;
            $model->page_title = $linkPreview->title;
            $model->page_description = $linkPreview->description;
            $model->page_thumbnail_url = $linkPreview->cover;
            $model->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw ValidationException::withMessages([
                'url' => 'URLが存在しない等の理由で読み込めませんでした。変更して再度投稿してください'
            ]);
        }
    }
}
