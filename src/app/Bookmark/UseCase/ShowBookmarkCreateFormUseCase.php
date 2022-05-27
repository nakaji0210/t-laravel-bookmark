<?php

namespace App\Bookmark\UseCase;

use App\Models\BookmarkCategory;
use Artesaos\SEOTools\Facades\SEOTools;

use Illuminate\Database\Eloquent\Collection;

final class ShowBookmarkCreateFormUseCase
{
    /**
     * @return Collection
     */
    public function handle(): Collection
    {
        SEOTools::setTitle('ブックマーク作成');

        $master_categories = BookmarkCategory::query()->oldest('id')->get();

        return $master_categories;
    }
}
