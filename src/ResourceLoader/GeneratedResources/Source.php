<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\FileOperations\ExistingFile;

final class Source
{
    public function __construct(
        private string $pathname
    ) {
    }

    public function directory(): ExistingDirectory
    {
        return ExistingDirectory::fromPathname($this->pathname);
    }

    public function file(): ExistingFile
    {
        return ExistingFile::fromPathname($this->pathname);
    }
}
