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

    public function exists(): bool
    {
        return is_file($this->pathname);
    }

    public function pathname(): string
    {
        return $this->pathname;
    }

    public function getContents(): string
    {
        return $this->existing()
            ->getContents();
    }

    public function putContents(string $contents): void
    {
        $this->createIfNotExists()
            ->putContents($contents);
    }

    public function unlink(): void
    {
        if (is_file($this->pathname)) {
            unlink($this->pathname);
        }
    }
}
