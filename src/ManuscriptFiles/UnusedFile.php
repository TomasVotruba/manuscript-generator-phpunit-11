<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ManuscriptFiles;

final readonly class UnusedFile implements File
{
    public function __construct(
        private string $filePathname,
        private string $contents
    ) {
    }

    public function filePathname(): string
    {
        return $this->filePathname;
    }

    public function oldContents(): string
    {
        return $this->contents;
    }

    public function newContents(): string
    {
        return '';
    }
}
