<?php

declare(strict_types=1);

namespace ManuscriptGenerator\FileOperations;

final class FileWasCreated
{
    public function __construct(
        private string $filepath,
        private string $contents
    ) {
    }

    public function contents(): string
    {
        return $this->contents;
    }

    public function filepath(): string
    {
        return $this->filepath;
    }
}
