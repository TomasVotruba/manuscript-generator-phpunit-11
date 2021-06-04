<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

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
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function load(IncludedResource $includedResource): LoadedResource
    {
        foreach ($this->resourceGenerators as $resourceGenerator) {
            if (! $resourceGenerator->supportsResource($includedResource)) {
                continue;
            }

            $expectedPath = $includedResource->expectedFilePathname();
            if (is_file($expectedPath)
                && $this->isFresh($expectedPath, $resourceGenerator->sourcePathForResource($includedResource))
            ) {
                $this->eventDispatcher->dispatch(new GeneratedResourceWasStillFresh($includedResource->link));

                // The file actually exists, so we can load it from disk
                return $this->fileResourceLoader->load($includedResource);
            }
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

    private function isFresh(string $targetFilePath, string $sourcePath): bool
    {
        $generatedFileLastModified = (int) filemtime($targetFilePath);

        foreach (
            [
                $sourcePath, // the source path as provided by the resource generator
                getcwd() . '/vendor', // the vendor directory, since it may influence the generated output
            ]
            as $filePathToCheck
        ) {
            if (! file_exists($filePathToCheck)) {
                continue;
            }

            if ((int) filemtime($filePathToCheck) > $generatedFileLastModified) {
                return false;
            }
        }

        return true;
    }
}
