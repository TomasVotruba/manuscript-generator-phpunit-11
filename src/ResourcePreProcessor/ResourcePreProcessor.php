<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\ResourceAttributes;
use Symplify\SmartFileSystem\SmartFileInfo;

interface ResourcePreProcessor
{
    public function process(
        string $fileContents,
        SmartFileInfo $resourceFile,
        ResourceAttributes $resourceAttributes
    ): string;
}
