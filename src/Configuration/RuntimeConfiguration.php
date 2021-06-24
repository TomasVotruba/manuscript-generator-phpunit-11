<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Configuration;

use ManuscriptGenerator\ResourceLoader\GeneratedResources\ResourceGenerator;
use ManuscriptGenerator\ResourceProcessor\ResourceProcessor;

final class RuntimeConfiguration
{
    public function __construct(
        private BookProjectConfiguration $bookProjectConfiguration,
        private bool $readOnlyFilesystem = false,
        private bool $updateDependencies = false,
        private bool $runTests = false
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

    public function isLinkRegistryEnabled(): bool
    {
        return $this->bookProjectConfiguration->isLinkRegistryEnabled();
    }

    public function linkRegistryConfiguration(): LinkRegistryConfiguration
    {
        return $this->bookProjectConfiguration->linkRegistryConfiguration();
    }

    /**
     * @return array<ResourceProcessor>
     */
    public function additionalResourceProcessors(): array
    {
        return $this->bookProjectConfiguration->additionalResourceProcessors();
    }

    public function tmpDir(): string
    {
        return $this->bookProjectConfiguration->tmpDir();
    }

    public function updateDependencies(): bool
    {
        return $this->updateDependencies;
    }

    public function runTests(): bool
    {
        return $this->runTests;
    }

    public function maximumLineWidthForInlineResources(): int
    {
        return 72;
    }
}
