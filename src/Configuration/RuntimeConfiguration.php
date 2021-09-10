<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Configuration;

use ManuscriptGenerator\FileOperations\Directory;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\ResourceGenerator;
use ManuscriptGenerator\ResourceProcessor\ResourceProcessor;

final class RuntimeConfiguration
{
    public function __construct(
        private BookProjectConfiguration $bookProjectConfiguration,
        private bool $readOnlyFilesystem = false,
        private bool $regenerateGeneratedResources = false,
        private bool $updateDependencies = false
    ) {
    }

    public function manuscriptSrcDir(): ExistingDirectory
    {
        return $this->bookProjectConfiguration->manuscriptSrcDir();
    }

    public function manuscriptTargetDir(): Directory
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

    public function regenerateAllGeneratedResources(): bool
    {
        return $this->regenerateGeneratedResources;
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

    public function tmpDir(): Directory
    {
        return $this->bookProjectConfiguration->tmpDir();
    }

    public function updateDependencies(): bool
    {
        return $this->updateDependencies;
    }

    public function maximumLineLengthForInlineResources(): int
    {
        return 72;
    }

    public function titlePageConfiguration(): TitlePageConfiguration
    {
        return $this->bookProjectConfiguration->titlePageConfiguration();
    }
}
