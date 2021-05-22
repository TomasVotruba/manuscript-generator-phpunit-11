<?php

declare(strict_types=1);

namespace BookTools;

use Symplify\SmartFileSystem\SmartFileInfo;

interface ResourceLoader
{
    public function load(SmartFileInfo $includedFromFile, string $link): SmartFileInfo;
}
