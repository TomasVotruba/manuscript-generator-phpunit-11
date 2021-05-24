<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\Markua\Attributes;
use BookTools\ResourceLoader\IncludedResource;

interface ResourcePreProcessor
{
    public function process(IncludedResource $includedResource, Attributes $resourceAttributes): IncludedResource;
}
