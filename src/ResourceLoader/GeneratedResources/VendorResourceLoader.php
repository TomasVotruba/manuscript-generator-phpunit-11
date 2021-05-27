<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader\GeneratedResources;

use BookTools\FileOperations\FileOperations;
use BookTools\Markua\Parser\Node\IncludedResource;
use BookTools\ResourceLoader\CouldNotLoadFile;
use BookTools\ResourceLoader\LoadedResource;
use BookTools\ResourceLoader\ResourceLoader;
use function str_starts_with;
use Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class VendorResourceLoader implements ResourceLoader
{
    public function __construct(
        private FileOperations $fileOperations
    ) {
    }

    public function load(IncludedResource $includedResource): LoadedResource
    {
        if (! str_starts_with($includedResource->link, 'vendor/')) {
            throw CouldNotLoadFile::becauseResourceIsNotSupported();
        }

        $targetPathname = $includedResource->expectedFilePathname();

        try {
            $expectedPathname = getcwd() . '/' . $includedResource->link;
            $vendorResource = new SmartFileInfo($expectedPathname);
            $contents = $vendorResource->getContents();
            $this->fileOperations->putContents($targetPathname, $contents);

            return LoadedResource::createFromIncludedResource($includedResource, $contents);
        } catch (FileNotFoundException $exception) {
            throw CouldNotLoadFile::createFromPrevious($exception);
        }
    }
}
