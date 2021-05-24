<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\Markua\Parser\Attributes;
use BookTools\ResourceLoader\IncludedResource;

final class CropResourcePreProcessor implements ResourcePreProcessor
{
    private const CROP_END_MARKER = '// crop-end';

    private const CROP_START_MARKER = "// crop-start\n";

    private TextCropper $textCropper;

    public function __construct()
    {
        $this->textCropper = new TextCropper(self::CROP_START_MARKER, self::CROP_END_MARKER);
    }

    public function process(IncludedResource $includedResource, Attributes $resourceAttributes): IncludedResource
    {
        // @TODO determine if text-based and ignore if not

        return $includedResource->withContents($this->textCropper->crop($includedResource->contents()));
    }
}
