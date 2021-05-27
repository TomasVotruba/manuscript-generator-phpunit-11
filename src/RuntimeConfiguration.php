<?php

declare(strict_types=1);

namespace BookTools;

use BookTools\ResourceLoader\GeneratedResources\ResourceGenerator;

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

    /**
     * @return array<ResourceGenerator>
     */
    public function additionalResourceGenerators(): array
    {
        return $this->bookProjectConfiguration->resourceGenerators();
    }
}
