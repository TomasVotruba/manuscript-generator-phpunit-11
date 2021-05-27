<?php

declare(strict_types=1);

namespace BookTools;

final class RuntimeConfiguration
{
    public function __construct(
        private BookProjectConfiguration $bookProjectConfiguration,
        private bool $readOnlyFilesystem = false
    ) {
    }

    public function manuscriptSrcDir(): string
    {
        return $this->bookProjectConfiguration->manuscriptSrcDir();
    }

    public function manuscriptTargetDir(): string
    {
        return $this->bookProjectConfiguration->manuscriptTargetDir();
    }

    public function capitalizeHeadlines(): bool
    {
        return $this->bookProjectConfiguration->capitalizeHeadlines();
    }

    public function readOnlyFilesystem(): bool
    {
        return $this->readOnlyFilesystem;
    }
}
