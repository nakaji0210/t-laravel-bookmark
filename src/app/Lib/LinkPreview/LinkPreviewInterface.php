<?php

namespace App\Lib\LinkPreview;

interface LinkPreviewInterface
{
    public function getLinkPreview(string $url): GetLinkPreviewResponse;
}
