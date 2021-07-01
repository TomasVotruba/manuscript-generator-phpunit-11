<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use function str_starts_with;

final class CopyFromVendorResourceGenerator implements ResourceGenerator
{
    private const EXPECTED_PREFIX = 'copy-from-vendor/';

    public function supportsResource(IncludedResource $resource): bool
    {
        return str_starts_with($resource->link, self::EXPECTED_PREFIX);
    }

    public function sourcePathForResource(IncludedResource $resource): string
    {
        // @TODO use project root dir instead of getcwd() here
        return getcwd() . '/' . str_replace(self::EXPECTED_PREFIX, 'vendor/', $resource->link);
    }

    public function generateResource(IncludedResource $resource): string
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
