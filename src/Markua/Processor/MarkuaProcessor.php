<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor;

use Symplify\SmartFileSystem\SmartFileInfo;

interface MarkuaProcessor
{
    public function process(self $markuaProcessor, SmartFileInfo $markuaFileInfo, string $markua): string;
}
