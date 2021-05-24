<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

final class IncludedResource
{
    public function __construct(
        private string $fileExtension,
        private string $contents
    ) {
    }

    public function fileExtension(): string
    {
        return $this->fileExtension;
    }

    public function contents(): string
    {
        return $this->contents;
    }

    public function withContents(string $newContents): self
    {
        return new self($this->fileExtension, $newContents);
    }

    public static function createFromPathAndContents(string $pathname, string $contents): self
    {
        return new self(pathinfo($pathname, PATHINFO_EXTENSION), $contents);
    }
}
