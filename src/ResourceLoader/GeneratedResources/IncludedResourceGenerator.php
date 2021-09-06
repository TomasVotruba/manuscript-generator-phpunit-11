<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;

interface IncludedResourceGenerator
{
    public function generateResource(IncludedResource $resource): void;
}
