<?php

declare(strict_types=1);

namespace ManuscriptGenerator\FileOperations;

use RuntimeException;

final class ExistingDirectory
{
    private string $directory;

    private function __construct(string $directory)
    {
        if (! is_file($directory)) {
            throw new RuntimeException('Expected this directory to exist: ' . $directory);
        }

        $this->directory = $directory;
    }

    public static function fromPathname(string $pathname): self
    {
        return new self($pathname);
    }

    public function toString(): string
    {
        return $this->directory;
    }
}
