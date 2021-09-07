<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;

final class FileResourceLoader implements ResourceLoader
{
    public function load(IncludedResource $includedResource): LoadedResource
    {
        $file = $includedResource->expectedFile();
        if (! $file->exists()) {
            throw new CouldNotLoadFile('File not found: ' . $file->pathname());
        }

        return LoadedResource::createFromIncludedResource($includedResource, $file->getContents());
    }
}
