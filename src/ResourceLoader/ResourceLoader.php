<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use Symplify\SmartFileSystem\SmartFileInfo;

interface ResourceLoader
{
    public function load(SmartFileInfo $includedFromFile, string $link): IncludedResource;
}
