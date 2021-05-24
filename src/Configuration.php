<?php

declare(strict_types=1);

namespace BookTools;

final class Configuration
{
    private string $manuscriptSrcDir;

    private string $manuscriptTargetDir;

    private bool $capitalizeHeadlines;

    private bool $readOnlyFilesystem;

    public function __construct(
        string $manuscriptSrcDir,
        string $manuscriptTargetDir,
        bool $capitalizeHeadlines = false,
        bool $readOnlyFilesystem = false
    ) {
        $this->manuscriptSrcDir = $manuscriptSrcDir;
        $this->manuscriptTargetDir = $manuscriptTargetDir;
        $this->capitalizeHeadlines = $capitalizeHeadlines;
        $this->readOnlyFilesystem = $readOnlyFilesystem;
    }

    public function manuscriptSrcDir(): string
    {
        return $this->manuscriptSrcDir;
    }

    public function manuscriptTargetDir(): string
    {
        return $this->manuscriptTargetDir;
    }

    public function capitalizeHeadlines(): bool
    {
        return $this->capitalizeHeadlines;
    }

    public function readOnlyFilesystem(): bool
    {
        return $this->readOnlyFilesystem;
    }
}
