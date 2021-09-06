<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\FileOperations\File;

final class Source
{
    public function __construct(
        private string $pathname
    ) {
    }

    public function existingDirectory(): ExistingDirectory
    {
        return ExistingDirectory::fromPathname($this->pathname);
    }

    public function existingFile(): ExistingFile
    {
        return ExistingFile::fromPathname($this->pathname);
    }

    public function file(): File
    {
        return File::fromPathname($this->pathname);
    }
}
