<?php

declare(strict_types=1);

namespace ManuscriptGenerator\FileOperations;

final class File
{
    private function __construct(
        private string $pathname
    ) {
    }

    public static function fromPathname(string $pathname): self
    {
        return new self($pathname);
    }

    public function createIfNotExists(): ExistingFile
    {
        Directory::fromPathname(dirname($this->pathname))->createIfNotExists();

        touch($this->pathname);

        return ExistingFile::fromPathname($this->pathname);
    }

    public function existing(): ExistingFile
    {
        return ExistingFile::fromPathname($this->pathname);
    }
}
