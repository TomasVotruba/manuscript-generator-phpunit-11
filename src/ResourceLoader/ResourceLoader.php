<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use BookTools\Markua\Parser\Node\IncludedResource;

interface ResourceLoader
{
    public function load(IncludedResource $includedResource): LoadedResource;
}
