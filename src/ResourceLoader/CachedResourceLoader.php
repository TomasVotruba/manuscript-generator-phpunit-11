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
        if (is_file($expectedFilePathname)) {
            // @TODO check freshness
            // - vendor dir
            // - dirname of expectedFilePathName
            // -
            return $this->fileLoader->load($includedFromFile, $link);
        }

        return $this->realLoader->load($includedFromFile, $link);
        // Touch file was last modified date
    }
}
