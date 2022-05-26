<?php

namespace App\Bookmark\UseCase;

use App\Models\Bookmark;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UpdateBookmarkUseCase
{
    public function handle(int $id, int $categoryId, string $comment)
    {
        $model = Bookmark::query()->findOrFail($id);

        if ($model->can_not_delete_or_edit) {
            throw ValidationException::withMessages([
                'can_edit' => 'ブックマーク後24時間経過したものは編集できません'
            ]);
        }

        if ($model->user_id !== Auth::id()) {
            abort(403);
        }

        $model->category_id = $categoryId;
        $model->comment = $comment;
        $model->save();
    }
}
