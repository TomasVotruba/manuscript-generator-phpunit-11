<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FileResourceLoader implements ResourceLoader
{
    public function load(SmartFileInfo $includedFromFile, string $link): SmartFileInfo
    {
        try {
            return new SmartFileInfo($includedFromFile->getPath() . '/resources/' . $link);
        } catch (FileNotFoundException $previous) {
            throw CouldNotLoadFile::createFromPrevious($previous, $previous);
        }
    }
}
