<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;

final class CopyFromVendorResourceGenerator implements CacheableResourceGenerator
{
    private const EXPECTED_PREFIX = 'copy-from-vendor/';

    public function name(): string
    {
        return 'copy_from_vendor';
    }

    public function sourcePathForResource(IncludedResource $resource): string
    {
        return getcwd() . '/' . str_replace(self::EXPECTED_PREFIX, 'vendor/', $resource->link);
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        $sourceFilePathname = $this->sourcePathForResource($resource);

        if (! is_file($sourceFilePathname)) {
            throw CouldNotGenerateResource::becauseSourceFileNotFound($sourceFilePathname);
        }

        return (string) file_get_contents($sourceFilePathname);
    }

    public function sourceLastModified(
        IncludedResource $resource,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $determineLastModifiedTimestamp->ofFile($this->sourcePathForResource($resource));
    }
}
