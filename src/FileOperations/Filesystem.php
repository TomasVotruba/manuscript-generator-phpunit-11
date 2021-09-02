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
            // @TODO consider throwing an exception here, since it will also make the generate manuscript command fail
            return;
        }

        $containingDir = dirname($pathname);
        if (! is_dir($containingDir)) {
            mkdir(dirname($pathname), 0777, true);
        }

        file_put_contents($pathname, $contents);
    }
}
