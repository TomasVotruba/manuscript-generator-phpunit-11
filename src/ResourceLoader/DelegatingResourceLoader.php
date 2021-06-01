<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;

final class DelegatingResourceLoader implements ResourceLoader
{
    /**
     * @param array<ResourceLoader> $loaders
     */
    public function __construct(
        private array $loaders
    ) {
    }

    public function load(IncludedResource $includedResource): LoadedResource
    {
        $lastException = null;

        foreach ($this->loaders as $loader) {
            try {
                return $loader->load($includedResource);
            } catch (CouldNotLoadFile $exception) {
                $lastException = CouldNotLoadFile::createFromPrevious($exception, $lastException);
            }
        }

        throw new CouldNotLoadFile(
            sprintf(
                'None of the loaders was able to load included resource "%s" included by file "%s"',
                $includedResource->link,
                $includedResource->includedFromFile()
                    ->getRelativePathname()
            ),
            0,
            $lastException
        );
    }
}
