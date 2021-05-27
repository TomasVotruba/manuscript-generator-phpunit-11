<?php

declare(strict_types=1);

namespace BookTools\ResourceProcessor;

use BookTools\Markua\Parser\Node\AttributeList;
use BookTools\ResourceLoader\LoadedResource;

final class CropResourceProcessor implements ResourceProcessor
{
    private const CROP_END_MARKER = '// crop-end';

    private const CROP_START_MARKER = "// crop-start\n";

    private TextCropper $textCropper;

    public function __construct()
    {
        $this->textCropper = new TextCropper(self::CROP_START_MARKER, self::CROP_END_MARKER);
    }

    public function process(LoadedResource $includedResource, AttributeList $resourceAttributes): void
    {
        $includedResource->setContents($this->textCropper->crop($includedResource->contents()));
    }
}
