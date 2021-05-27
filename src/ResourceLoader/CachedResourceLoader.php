<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use BookTools\Markua\Parser\Node\IncludedResource;

final class CachedResourceLoader implements ResourceLoader
{
    public function __construct(
        private ResourceLoader $realLoader,
        private ResourceLoader $fileLoader
    ) {
    }

    public function load(IncludedResource $includedResource): LoadedResource
    {
        $expectedFilePathname = $includedResource->expectedFilePathname();
        if (is_file($expectedFilePathname) && $this->stillFresh($expectedFilePathname)) {
            return $this->fileLoader->load($includedResource);
        }

        return $this->realLoader->load($includedResource);
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
