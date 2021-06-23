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
use Psr\Log\LoggerInterface;
use RuntimeException;
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
                && $this->isFresh($expectedPath, $sourcePath)
            ) {
                $this->eventDispatcher->dispatch(new GeneratedResourceWasStillFresh($includedResource->link));

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

        if (! file_exists($sourcePath)) {
            $this->logger->debug(
                'Could not determine freshness of target path {targetPath}. Source file not found: {sourcePath}',
                [
                    'targetPath' => $targetFilePath,
                    'sourcePath' => $sourcePath,
                ]
            );
            return true;
        }

        /*
         * If the source for this generator is a single file we also take other files in this directory into
         * consideration to determine the freshness
         */
        $sourceDir = $sourcePath;
        if (is_file($sourceDir)) {
            $sourceDir = dirname($sourcePath);
        }

        return $this->sourceFileLastModified($sourceDir) <= $generatedFileLastModified;
    }

    private function sourceFileLastModified(string $directory): int
    {
        $files = $this->relevantFilesIn($directory);
        if ($files === []) {
            return 0;
        }

        $lastModifiedTimes = array_map(fn (string $pathname) => (int) filemtime($pathname), $files);
        arsort($lastModifiedTimes);

        return $lastModifiedTimes[array_key_first($lastModifiedTimes)];
    }

    /**
     * @return array<string>
     */
    private function relevantFilesIn(string $directory): array
    {
        $files = [];

        $dh = opendir($directory);
        if ($dh === false) {
            throw new RuntimeException('Could not open directory ' . $directory . ' for reading');
        }

        while (($filename = readdir($dh)) !== false) {
            if ($filename === '.') {
                continue;
            }
            if ($filename === '..') {
                continue;
            }
            // @TODO get from DependenciesInstaller
            $ignoreFileNames = ['vendor'];
            if (in_array($filename, $ignoreFileNames, true)) {
                continue;
            }

            $pathname = $directory . '/' . $filename;
            if (is_dir($pathname)) {
                $files = array_merge($files, $this->relevantFilesIn($pathname));
            } else {
                $files[] = $pathname;
            }
        }

        closedir($dh);

        return $files;
    }
}
