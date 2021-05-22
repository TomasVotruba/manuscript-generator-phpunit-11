<?php

declare(strict_types=1);

namespace BookTools;

use Symplify\SmartFileSystem\SmartFileInfo;

final class FileResourceLoader implements ResourceLoader
{
    public function load(SmartFileInfo $includedFromFile, string $link): SmartFileInfo
    {
        return new SmartFileInfo($includedFromFile->getPath() . '/resources/' . $link);
    }
}
