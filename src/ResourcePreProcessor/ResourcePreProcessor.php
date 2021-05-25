<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\Markua\Parser\Node\AttributeList;
use BookTools\ResourceLoader\LoadedResource;

interface ResourcePreProcessor
{
    /**
     * @TODO merge arguments
     */
    public function process(LoadedResource $includedResource, AttributeList $resourceAttributes): LoadedResource;
}
