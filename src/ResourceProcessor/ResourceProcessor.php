<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use ManuscriptGenerator\ResourceLoader\LoadedResource;

interface ResourceProcessor
{
    public function process(LoadedResource $resource): void;
}
