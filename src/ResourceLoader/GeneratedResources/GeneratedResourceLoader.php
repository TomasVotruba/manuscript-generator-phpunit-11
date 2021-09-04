<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\FileOperations\Filesystem;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\ResourceLoader\CouldNotLoadFile;
use ManuscriptGenerator\ResourceLoader\FileResourceLoader;
use ManuscriptGenerator\ResourceLoader\LoadedResource;
use ManuscriptGenerator\ResourceLoader\ResourceLoader;
use Psr\Log\LoggerInterface;

final class GeneratedResourceLoader implements ResourceLoader
{
    /**
     * @param array<ResourceGenerator> $resourceGenerators
     */
    public function __construct(
        private array $resourceGenerators,
        private FileResourceLoader $fileResourceLoader,
        private Filesystem $filesystem,
        private DependenciesInstaller $dependenciesInstaller,
        private DetermineLastModifiedTimestamp $determineLastModifiedTimestamp,
        private LoggerInterface $logger
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
                $this->logger->debug('Generated resource {link} was still fresh', [
                    'link' => $includedResource->link,
                ]);

                // The file actually exists, so we can load it from disk
                return $this->fileResourceLoader->load($includedResource);
            }

            if (is_dir($sourcePath)) {
                $directory = $sourcePath;
            } else {
                $directory = dirname($sourcePath);
            }
            $this->dependenciesInstaller->install($directory);

            $generatedResource = $resourceGenerator->generateResource($includedResource);
            $this->filesystem->putContents($expectedPath, $generatedResource);

            $this->logger->info('Generated resource {link}', [
                'link' => $includedResource->link,
            ]);

            // If we run in dry-mode, the file may still not exist, so we should not attempt to load it from disk
            return LoadedResource::createFromIncludedResource($includedResource, $generatedResource);
        }

        throw new CouldNotLoadFile(
            sprintf('None of the generators was able to generate resource "%s"', $includedResource->link)
        );
    }
}
