<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\ResourceAttributes;
use BookTools\ResourceLoader\IncludedResource;

interface ResourcePreProcessor
{
    public function process(
        string $fileContents,
        IncludedResource $includedResource,
        ResourceAttributes $resourceAttributes
    ): string;
}
