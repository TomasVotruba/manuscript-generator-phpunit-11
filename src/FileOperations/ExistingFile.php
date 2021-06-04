<?php

declare(strict_types=1);

namespace ManuscriptGenerator\FileOperations;

use RuntimeException;

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

    public function contents(): string
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

    public function directory(): string
    {
        return dirname($this->pathname);
    }

    public function pathnameRelativeTo(string $dirname): string
    {
        $dirname = rtrim($dirname, '/') . '/';
        if (str_starts_with($this->pathname, $dirname)) {
            return substr($this->pathname, strlen($dirname));
        }

        return $this->pathname;
    }
}