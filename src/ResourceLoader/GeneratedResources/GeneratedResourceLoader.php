<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\FileOperations\FileOperations;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\ResourceLoader\CouldNotLoadFile;
use ManuscriptGenerator\ResourceLoader\FileResourceLoader;
use ManuscriptGenerator\ResourceLoader\LoadedResource;
use ManuscriptGenerator\ResourceLoader\ResourceLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class GeneratedResourceLoader implements ResourceLoader
{
    /**
     * @param array<ResourceGenerator> $resourceGenerators
     */
    public function __construct(
        private array $resourceGenerators,
        private FileResourceLoader $fileResourceLoader,
        private FileOperations $fileOperations,
        private EventDispatcherInterface $eventDispatcher,
        private DependenciesInstaller $dependenciesInstaller,
        private DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ) {
    }

    public function load(IncludedResource $includedResource): LoadedResource
    {
        foreach ($this->resourceGenerators as $resourceGenerator) {
            if (! $resourceGenerator->supportsResource($includedResource)) {
                continue;
            }

            $expectedPath = $includedResource->expectedFilePathname();
            $sourcePath = $resourceGenerator->sourcePathForResource($includedResource);
            if (is_file($expectedPath)
                && $resourceGenerator->sourceLastModified($includedResource, $this->determineLastModifiedTimestamp)
                <= ((int) filemtime($expectedPath))
            ) {
                // @TODO directly call the logger
                $this->eventDispatcher->dispatch(new GeneratedResourceWasStillFresh($includedResource->link));

                // The file actually exists, so we can load it from disk
                return $this->fileResourceLoader->load($includedResource);
            }

            // @TODO remove duplication
            // @TODO move to generator so it can determine itself if we need to do this
            if (is_dir($sourcePath)) {
                $directory = $sourcePath;
            } else {
                $directory = dirname($sourcePath);
            }
            $this->dependenciesInstaller->install($directory);

            $generatedResource = $resourceGenerator->generateResource($includedResource);
            $this->fileOperations->putContents($expectedPath, $generatedResource);
            $this->eventDispatcher->dispatch(new ResourceWasGenerated($includedResource->link));
            // If we run in dry-mode, the file may still not exist, so we should not attempt to load it from disk
            return LoadedResource::createFromIncludedResource($includedResource, $generatedResource);
        }

        throw new CouldNotLoadFile(
            sprintf('None of the generators was able to generate resource "%s"', $includedResource->link)
        );
    }
}
