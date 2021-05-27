<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader\GeneratedResources;

use BookTools\Markua\Parser\Node\IncludedResource;

interface ResourceGenerator
{
    public function supportsResource(IncludedResource $resource): bool;

    public function generateResource(IncludedResource $resource): string;
}
