<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader\GeneratedResources;

use BookTools\Markua\Parser\Node\IncludedResource;
use BookTools\ResourceLoader\LoadedResource;
use BookTools\ResourceLoader\ResourceLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class CachedResourceLoader implements ResourceLoader
{
    public function __construct(
        private ResourceLoader $realLoader,
        private ResourceLoader $fileLoader,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function load(IncludedResource $includedResource): LoadedResource
    {
        $expectedFilePathname = $includedResource->expectedFilePathname();
        if (is_file($expectedFilePathname) && $this->stillFresh($expectedFilePathname)) {
            $this->eventDispatcher->dispatch(new GeneratedResourceWasStillFresh($includedResource->link));

            return $this->fileLoader->load($includedResource);
        }

        $resource = $this->realLoader->load($includedResource);

        $this->eventDispatcher->dispatch(new ResourceWasGenerated($includedResource->link));

        return $resource;
    }

    private function stillFresh(string $filePath): bool
    {
        $generatedFileLastModified = (int) filemtime($filePath);

        foreach (
            [
                dirname($filePath), // the directory that contains the file
                getcwd() . '/vendor', // the vendor directory, since it may influence the generated output
            ]
            as $filePathToCheck
        ) {
            if ((int) filemtime($filePathToCheck) > $generatedFileLastModified) {
                return false;
            }
        }

        return true;
    }
}
