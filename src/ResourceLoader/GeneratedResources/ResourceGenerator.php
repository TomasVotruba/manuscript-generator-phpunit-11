<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;

interface ResourceGenerator
{
    public function supportsResource(IncludedResource $resource): bool;

    /**
     * Return the source path on which this generated resource is based. The last modified timestamp of this path will
     * be used by CachedResourceLoader to determine if the resource needs to be regenerated
     */
    public function sourcePathForResource(IncludedResource $resource): string;

    public function generateResource(IncludedResource $resource): string;
}
