<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use Symplify\SmartFileSystem\SmartFileInfo;

interface MarkuaProcessor
{
    /**
     * @throws FailedToProcessMarkua
     */
    public function process(SmartFileInfo $markuaFileInfo, string $markua): string;
}
