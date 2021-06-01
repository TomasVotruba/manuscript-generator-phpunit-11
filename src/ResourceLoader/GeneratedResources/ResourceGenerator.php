<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;

interface ResourceGenerator
{
    public function supportsResource(IncludedResource $resource): bool;

    public function generateResource(IncludedResource $resource): string;
}
