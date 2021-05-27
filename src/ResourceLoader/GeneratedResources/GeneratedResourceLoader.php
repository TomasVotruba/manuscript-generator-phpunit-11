<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader\GeneratedResources;

use BookTools\FileOperations\FileOperations;
use BookTools\Markua\Parser\Node\IncludedResource;
use BookTools\ResourceLoader\CouldNotLoadFile;
use BookTools\ResourceLoader\LoadedResource;
use BookTools\ResourceLoader\ResourceLoader;

final class GeneratedResourceLoader implements ResourceLoader
{
    /**
     * @param array<ResourceGenerator> $resourceGenerators
     */
    public function __construct(
        private array $resourceGenerators,
        private FileOperations $fileOperations
    ) {
    }

    public function load(IncludedResource $includedResource): LoadedResource
    {
        foreach ($this->resourceGenerators as $resourceGenerator) {
            if ($resourceGenerator->supportsResource($includedResource)) {
                $expectedPath = $includedResource->expectedFilePathname();

                $generatedResource = $resourceGenerator->generateResource($includedResource);

                $this->fileOperations->putContents($expectedPath, $generatedResource);

                return LoadedResource::createFromIncludedResource($includedResource, $generatedResource);
            }
        }

        throw new CouldNotLoadFile(
            sprintf('None of the generators was able to generate resource "%s"', $includedResource->link)
        );
    }
}
