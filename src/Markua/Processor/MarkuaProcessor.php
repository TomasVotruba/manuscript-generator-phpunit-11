<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\ManuscriptFiles;

interface MarkuaProcessor
{
    /**
     * @throws FailedToProcessMarkua
     */
    public function process(ExistingFile $markuaFile, string $markua, ManuscriptFiles $manuscriptFiles): string;
}
