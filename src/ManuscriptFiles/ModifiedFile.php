<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ManuscriptFiles;

final class ModifiedFile implements File
{
    public function __construct(
        private string $filePathname,
        private string $oldContents,
        private string $newContents
    ) {
    }

    public function filePathname(): string
    {
        return $this->filePathname;
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
