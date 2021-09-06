<?php

declare(strict_types=1);

namespace ManuscriptGenerator\FileOperations;

use Assert\Assertion;
use Symfony\Component\Filesystem\Filesystem;

final class Directory
{
    private function __construct(
        private string $pathname
    ) {
        Assertion::notEmpty($this->pathname);
        Assertion::notEq($this->pathname, '/');
    }

    public static function fromPathname(string $pathname): self
    {
        return new self($pathname);
    }

    public function createIfNotExists(): ExistingDirectory
    {
        if (! is_dir($this->pathname)) {
            mkdir($this->pathname, 0777, true);
        }

        return ExistingDirectory::fromPathname($this->pathname);
    }

    public function existing(): ExistingDirectory
    {
        return ExistingDirectory::fromPathname($this->pathname);
    }

    public function tmpFile(string $prefix, string $extension): File
    {
        return $this->createIfNotExists()
            ->tmpFile($prefix, $extension);
    }

    public function pathname(): string
    {
        return $this->pathname;
    }

    public function makeEmpty(): ExistingDirectory
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->pathname);

        return $this->createIfNotExists();
    }
}
