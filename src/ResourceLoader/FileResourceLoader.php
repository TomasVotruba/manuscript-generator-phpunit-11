<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader;

use Assert\Assertion;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;

final class FileResourceLoader implements ResourceLoader
{
    public function load(IncludedResource $includedResource): LoadedResource
    {
        $filePathname = $includedResource->expectedFilePathname();
        if (! is_file($filePathname)) {
            throw new CouldNotLoadFile('File not found: ' . $filePathname);
        }

        $contents = file_get_contents($filePathname);
        Assertion::string($contents);

        return LoadedResource::createFromIncludedResource($includedResource, $contents);
    }
}
