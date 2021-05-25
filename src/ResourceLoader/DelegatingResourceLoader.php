<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use Symplify\SmartFileSystem\SmartFileInfo;

final class DelegatingResourceLoader implements ResourceLoader
{
    /**
     * @param array<ResourceLoader> $loaders
     */
    public function __construct(
        private array $loaders
    ) {
    }

    public function load(SmartFileInfo $includedFromFile, string $link): LoadedResource
    {
        $lastException = null;

        foreach ($this->loaders as $loader) {
            try {
                return $loader->load($includedFromFile, $link);
            } catch (CouldNotLoadFile $exception) {
                $lastException = CouldNotLoadFile::createFromPrevious($exception, $lastException);
            }
        }

        throw new CouldNotLoadFile(
            sprintf(
                'None of the loaders was able to load this resource "%s" included by file "%s"',
                $link,
                $includedFromFile->getRelativePathname()
            ),
            0,
            $lastException
        );
    }
}
