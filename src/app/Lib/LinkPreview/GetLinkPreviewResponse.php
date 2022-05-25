<?php

namespace App\Lib\LinkPreview;

final class GetLinkPreviewResponse
{
    // PHP8.0以降なら省略構文で書ける
    public string $title;
    public string $description;
    public string $cover;

    public function __construct(
        string $title,
        string $description,
        string $cover
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->cover = $cover;
    }
}
