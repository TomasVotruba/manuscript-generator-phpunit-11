<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;

interface ResourceLoader
{
    public function load(IncludedResource $includedResource): LoadedResource;
}
