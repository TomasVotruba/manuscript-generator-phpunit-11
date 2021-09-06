<?php

declare(strict_types=1);

namespace ManuscriptGenerator\FileOperations;

use Assert\Assertion;

final class Directory
{
    private function __construct(
        private string $pathname
    ) {
        Assertion::notEmpty($this->pathname);
    }

    public static function fromPathname(string $pathname): self
    {
        return new self($pathname);
    }

    public function create(): ExistingDirectory
    {
        if (! is_dir($this->pathname)) {
            mkdir($this->pathname, 0777, true);
        }

        return ExistingDirectory::fromPathname($this->pathname);
    }
}
