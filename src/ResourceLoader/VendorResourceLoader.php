<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use BookTools\FileOperations\FileOperations;
use Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class VendorResourceLoader implements ResourceLoader
{
    public function __construct(
        private FileOperations $fileOperations
    ) {
    }

    public function load(SmartFileInfo $includedFromFile, string $link): IncludedResource
    {
        if (! str_starts_with($link, 'vendor/')) {
            throw CouldNotLoadFile::becauseResourceIsNotSupported();
        }

        // @TODO remove duplication
        $targetPathname = $includedFromFile->getPath() . '/resources/' . $link;

        try {
            $expectedPathname = getcwd() . '/' . $link;
            $vendorResource = new SmartFileInfo($expectedPathname);
            $contents = $vendorResource->getContents();
            $this->fileOperations->putContents($targetPathname, $contents);

            return IncludedResource::createFromPathAndContents($expectedPathname, $contents);
        } catch (FileNotFoundException $exception) {
            throw CouldNotLoadFile::createFromPrevious($exception);
        }
    }
}
