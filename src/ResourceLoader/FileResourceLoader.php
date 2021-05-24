<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use Symplify\SmartFileSystem\SmartFileInfo;

final class FileResourceLoader implements ResourceLoader
{
    public function load(SmartFileInfo $includedFromFile, string $link): IncludedResource
    {
        $expectedFilePathname = $includedFromFile->getPath() . '/resources/' . $link;

        if (! is_file($expectedFilePathname)) {
            throw new CouldNotLoadFile('File not found: ' . $expectedFilePathname);
        }

        $contents = file_get_contents($expectedFilePathname);
        assert(is_string($contents));

        return new IncludedResource(pathinfo($link, PATHINFO_EXTENSION), $contents);
    }
}
