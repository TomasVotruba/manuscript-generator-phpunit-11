<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class VendorResourceLoader implements ResourceLoader
{
    public function load(SmartFileInfo $includedFromFile, string $link): SmartFileInfo
    {
        if (! str_starts_with($link, 'vendor/')) {
            throw CouldNotLoadFile::becauseResourceIsNotSupported();
        }

        // @TODO remove duplication
        $targetPathname = $includedFromFile->getPath() . '/resources/' . $link;

        try {
            $vendorResource = new SmartFileInfo(getcwd() . '/' . $link);
            // @TODO use filesystem service
            @mkdir(dirname($targetPathname), 0777, true);
            // @TODO use filesystem service
            file_put_contents($targetPathname, $vendorResource->getContents());

            return new SmartFileInfo($targetPathname);
        } catch (FileNotFoundException $exception) {
            throw CouldNotLoadFile::createFromPrevious($exception);
        }
    }
}
