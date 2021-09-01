<?php
declare(strict_types=1);

namespace ManuscriptGenerator;

final class ModifiedFile
{
    public function __construct(private string $filePathname, private string $newContents, private string $oldContents)
    {
    }

    public function filePathname(): string
    {
        return $this->filePathname;
    }

    public function newContents(): string
    {
        return $this->newContents;
    }

    public function oldContents(): string
    {
        return $this->oldContents;
    }
}
