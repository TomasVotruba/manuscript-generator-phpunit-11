<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use ManuscriptGenerator\ResourceLoader\LoadedResource;

final readonly class CropResourceProcessor implements ResourceProcessor
{
    private const CROP_END_MARKER = '// crop-end';

    private const CROP_START_MARKER = "// crop-start\n";

    private TextCropper $textCropper;

    public function __construct()
    {
        $this->textCropper = new TextCropper(self::CROP_START_MARKER, self::CROP_END_MARKER);
    }

    public function process(LoadedResource $resource): void
    {
        $resource->setContents($this->textCropper->crop($resource->contents()));
    }
}
