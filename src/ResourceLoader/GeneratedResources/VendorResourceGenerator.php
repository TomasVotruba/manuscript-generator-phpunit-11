<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use function str_starts_with;

final class VendorResourceGenerator implements ResourceGenerator
{
    public function supportsResource(IncludedResource $resource): bool
    {
        return str_starts_with($resource->link, 'vendor/');
    }

    public function sourcePathForResource(IncludedResource $resource): string
    {
        return getcwd() . '/' . $resource->link;
    }

    public function generateResource(IncludedResource $resource): string
    {
        // @TODO use project root dir instead of getcwd here
        $sourceFilePathname = $this->sourcePathForResource($resource);

        if (! is_file($sourceFilePathname)) {
            throw CouldNotGenerateResource::becauseSourceFileNotFound($sourceFilePathname);
        }

        return (string) file_get_contents($sourceFilePathname);
    }
}
