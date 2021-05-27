<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use BookTools\Markua\Parser\Node\IncludedResource;

final class FileResourceLoader implements ResourceLoader
{
    public function load(IncludedResource $includedResource): LoadedResource
    {
        $expectedFilePathname = $includedResource->expectedFilePathname();

        if (! is_file($expectedFilePathname)) {
            throw new CouldNotLoadFile('File not found: ' . $expectedFilePathname);
        }

        $contents = file_get_contents($expectedFilePathname);
        assert(is_string($contents));

        return LoadedResource::createFromIncludedResource($includedResource, $contents);
    }
}
