<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use Symplify\SmartFileSystem\SmartFileInfo;

interface ResourcePreProcessor
{
    public function process(string $fileContents, SmartFileInfo $resourceFile): string;
}
