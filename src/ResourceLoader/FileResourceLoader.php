<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use RuntimeException;

final class FileResourceLoader implements ResourceLoader
{
    public function load(IncludedResource $includedResource): LoadedResource
    {
        try {
            $includedFile = $includedResource->includedFile();
        } catch (RuntimeException $exception) {
            throw new CouldNotLoadFile('File not found: ' . $exception->getMessage());
        }

        $contents = $includedFile->contents();

        return LoadedResource::createFromIncludedResource($includedResource, $contents);
    }
}
