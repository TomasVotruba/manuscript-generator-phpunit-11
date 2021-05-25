<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\Markua\Parser\Node\Attributes;
use BookTools\ResourceLoader\LoadedResource;

interface ResourcePreProcessor
{
    /**
     * @TODO merge arguments
     */
    public function process(LoadedResource $includedResource, Attributes $resourceAttributes): LoadedResource;
}
