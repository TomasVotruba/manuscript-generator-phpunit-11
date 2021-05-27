<?php

declare(strict_types=1);

namespace BookTools\ResourceProcessor;

use BookTools\ResourceLoader\LoadedResource;

interface ResourceProcessor
{
    public function process(LoadedResource $includedResource): void;
}
