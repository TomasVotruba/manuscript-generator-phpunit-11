<?php

declare(strict_types=1);

namespace ManuscriptGenerator\FileOperations;

final class FileOrDirectory
{
    private function __construct(
        private string $pathname
    ) {
    }

    public static function fromPathname(string $pathname): self
    {
        return new self($pathname);
    }

    public function file(): File
    {
        return File::fromPathname($this->pathname);
    }

    public function directory(): Directory
    {
        return Directory::fromPathname($this->pathname);
    }

    public function pathname(): string
    {
        return $this->pathname;
    }
}
