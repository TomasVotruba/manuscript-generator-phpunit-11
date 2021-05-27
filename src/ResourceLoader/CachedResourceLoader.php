<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use Symplify\SmartFileSystem\SmartFileInfo;

final class CachedResourceLoader implements ResourceLoader
{
    public function __construct(
        private ResourceLoader $realLoader,
        private ResourceLoader $fileLoader
    ) {
    }

    public function load(SmartFileInfo $includedFromFile, string $link): LoadedResource
    {
        $expectedFilePathname = $includedFromFile->getPath() . '/resources/' . $link;
        if (is_file($expectedFilePathname) && $this->stillFresh($expectedFilePathname)) {
            return $this->fileLoader->load($includedFromFile, $link);
        }

        return $this->realLoader->load($includedFromFile, $link);
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
