<?php

declare(strict_types=1);

namespace ManuscriptGenerator\FileOperations;

use Assert\Assertion;
use RuntimeException;

final class ExistingDirectory
{
    private string $directory;

    private function __construct(string $directory)
    {
        if (! is_dir($directory)) {
            throw new RuntimeException('Expected this directory to exist: ' . $directory);
        }

        $this->directory = $directory;
    }

    public static function fromPathname(string $pathname): self
    {
        return new self($pathname);
    }

    public static function currentWorkingDirectory(): self
    {
        $cwd = getcwd();
        Assertion::string($cwd);

        return self::fromPathname($cwd);
    }

    public function appendPath(string $append): FileOrDirectory
    {
        return FileOrDirectory::fromPathname(rtrim($this->directory, '/') . '/' . ltrim($append, '/'));
    }

    public function toString(): string
    {
        return $this->directory;
    }
}
