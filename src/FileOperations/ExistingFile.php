<?php

declare(strict_types=1);

namespace ManuscriptGenerator\FileOperations;

use RuntimeException;
use SplFileInfo;

final class ExistingFile
{
    private string $pathname;

    private function __construct(string $pathname)
    {
        if (! is_file($pathname)) {
            throw new RuntimeException('Expected this file to exist: ' . $pathname);
        }

        $this->pathname = $pathname;
    }

    public static function fromPathname(string $pathname): self
    {
        return new self($pathname);
    }

    public function getContents(): string
    {
        $contents = file_get_contents($this->pathname);

        if ($contents === false) {
            throw new RuntimeException('Could not read contents of file ' . $this->pathname);
        }

        return $contents;
    }

    public function pathname(): string
    {
        return $this->pathname;
    }

    public function containingDirectory(): ExistingDirectory
    {
        return ExistingDirectory::fromPathname(dirname($this->pathname));
    }

    public function pathnameRelativeTo(string $dirname): string
    {
        $dirname = rtrim($dirname, '/') . '/';
        if (str_starts_with($this->pathname, $dirname)) {
            return substr($this->pathname, strlen($dirname));
        }

        return $this->pathname;
    }

    public function basename(): string
    {
        $fileInfo = (new SplFileInfo($this->pathname));

        return $fileInfo->getBasename();
    }

    public function putContents(string $contents): void
    {
        file_put_contents($this->pathname, $contents);
    }

    public function lastModifiedTime(): int
    {
        return filemtime($this->pathname) ?: 0;
    }
}
