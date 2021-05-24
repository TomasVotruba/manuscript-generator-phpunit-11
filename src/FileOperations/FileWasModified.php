<?php

declare(strict_types=1);

namespace BookTools\FileOperations;

final class FileWasModified
{
    public function __construct(
        private string $filepath,
        private string $oldContents,
        private string $newContents
    ) {
    }

    public function filepath(): string
    {
        return $this->filepath;
    }

    public function oldContents(): string
    {
        return $this->oldContents;
    }

    public function newContents(): string
    {
        return $this->newContents;
    }
}
