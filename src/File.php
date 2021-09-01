<?php
declare(strict_types=1);

namespace ManuscriptGenerator;

final class File
{
    public function __construct(private string $filePathname, private string $contents)
    {
    }

    public function filePathname(): string
    {
        return $this->filePathname;
    }

    public function contents(): string
    {
        return $this->contents;
    }
}
