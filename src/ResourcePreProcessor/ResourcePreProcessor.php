<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\Markua\Parser\Attributes;
use BookTools\ResourceLoader\LoadedResource;

interface ResourcePreProcessor
{
    public function process(LoadedResource $includedResource, Attributes $resourceAttributes): LoadedResource;
}
