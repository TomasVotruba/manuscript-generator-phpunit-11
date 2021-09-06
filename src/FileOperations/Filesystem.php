<?php

declare(strict_types=1);

namespace ManuscriptGenerator\FileOperations;

final class Filesystem
{
    public function __construct(
        private bool $readOnly
    ) {
    }

    public function fileExists(string $pathname): bool
    {
        return is_file($pathname);
    }

    public function getContents(string $pathname): string
    {
        $contents = file_get_contents($pathname);
        assert(is_string($contents));

        return $contents;
    }

    public function putContents(string $pathname, string $contents): void
    {
        if ($this->readOnly) {
            return;
        }

        Directory::fromPathname(dirname($pathname))->create();

        file_put_contents($pathname, $contents);
    }
}
