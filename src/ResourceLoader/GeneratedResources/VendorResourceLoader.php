<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\FileOperations\FileOperations;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\ResourceLoader\CouldNotLoadFile;
use ManuscriptGenerator\ResourceLoader\LoadedResource;
use ManuscriptGenerator\ResourceLoader\ResourceLoader;
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
