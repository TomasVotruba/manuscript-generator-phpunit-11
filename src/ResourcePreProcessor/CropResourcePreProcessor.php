<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\ResourceAttributes;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CropResourcePreProcessor implements ResourcePreProcessor
{
    private const CROP_END_MARKER = '// crop-end';

    private const CROP_START_MARKER = "// crop-start\n";

    private TextCropper $textCropper;

    public function __construct()
    {
        $this->textCropper = new TextCropper(self::CROP_START_MARKER, self::CROP_END_MARKER);
    }

    public function process(
        string $fileContents,
        SmartFileInfo $resourceFile,
        ResourceAttributes $resourceAttributes
    ): string {
        // @TODO determine if text-based and ignore if not

        return $this->textCropper->crop($fileContents);
    }
}
