<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\ManuscriptFiles\ManuscriptFiles;

interface MarkuaProcessor
{
    /**
     * @throws FailedToLoadMarkuaFile
     */
    public function process(ExistingFile $markuaFile, ManuscriptFiles $manuscriptFiles): string;
}
