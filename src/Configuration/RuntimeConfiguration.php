<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Configuration;

final class RuntimeConfiguration
{
    public function __construct(
        private bool $readOnlyFilesystem = false,
        private bool $regenerateGeneratedResources = false,
        private bool $updateDependencies = false
    ) {
    }

    public function readOnlyFilesystem(): bool
    {
        return $this->readOnlyFilesystem;
    }

    public function regenerateAllGeneratedResources(): bool
    {
        return $this->regenerateGeneratedResources;
    }

    public function updateDependencies(): bool
    {
        return $this->updateDependencies;
    }
}
