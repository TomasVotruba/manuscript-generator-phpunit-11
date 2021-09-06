<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;

final class CopyFromVendorResourceGenerator implements ResourceGenerator
{
    public function __construct(
        private DependenciesInstaller $dependenciesInstaller
    ) {
    }

    public function name(): string
    {
        return 'copy_from_vendor';
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        $this->dependenciesInstaller->install($resource->includedFromFile()->containingDirectory());

        return $source->existingFile()
            ->getContents();
    }

    public function sourceLastModified(
        IncludedResource $resource,
        Source $source,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $source->existingFile()
            ->lastModifiedTime();
    }
}
