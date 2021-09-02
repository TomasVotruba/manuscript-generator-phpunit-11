<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\Meta;

use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\ManuscriptFiles\ManuscriptFiles;

final class MetaAttributes
{
    /**
     * @see ManuscriptFiles
     */
    public const MANUSCRIPT_FILES = 'manuscript_files';

    /**
     * @see ExistingFile
     */
    public const FILE = 'file';
}
