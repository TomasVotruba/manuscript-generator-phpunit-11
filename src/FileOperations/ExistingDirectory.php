<?php

declare(strict_types=1);

namespace ManuscriptGenerator\FileOperations;

use Assert\Assertion;
use LogicException;
use RuntimeException;

final class ExistingDirectory
{
    private readonly string $pathname;

    private function __construct(string $directory)
    {
        if (! is_dir($directory)) {
            throw new RuntimeException('Expected this directory to exist: ' . $directory);
        }

        $this->pathname = $directory;
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
        return FileOrDirectory::fromPathname(rtrim($this->pathname, '/') . '/' . ltrim($append, '/'));
    }

    public function pathname(): string
    {
        return $this->pathname;
    }

    public function tmpFile(string $prefix, string $extension): File
    {
        return $this->appendPath(uniqid($prefix) . '.' . ltrim($extension, '.'))->file();
    }

    public function absolute(): self
    {
        $realPathname = realpath($this->pathname);
        if (! $realPathname) {
            throw new LogicException('Could not determine absolute path of ' . $this->pathname);
        }

        return self::fromPathname($realPathname);
    }
}
