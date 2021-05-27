<?php

declare(strict_types=1);

namespace BookTools\ResourceProcessor;

use BookTools\Markua\Parser\Node\AttributeList;
use BookTools\ResourceLoader\LoadedResource;

interface ResourceProcessor
{
    public function process(LoadedResource $includedResource, AttributeList $resourceAttributes): void;
}
