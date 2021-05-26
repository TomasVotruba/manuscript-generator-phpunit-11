<?php

declare(strict_types=1);

namespace BookTools;

final class Configuration
{
    public function __construct(
        private string $manuscriptSrcDir,
        private string $manuscriptTargetDir,
        private bool $capitalizeHeadlines = false,
        private bool $readOnlyFilesystem = false
    ) {
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
