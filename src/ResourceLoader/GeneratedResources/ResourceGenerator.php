<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;

interface ResourceGenerator
{
    public function name(): string;

    public function generateResource(IncludedResource $resource, Source $source): string;

    public function sourceLastModified(
        IncludedResource $resource,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int;
}
