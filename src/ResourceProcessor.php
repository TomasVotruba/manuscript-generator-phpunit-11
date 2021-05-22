<?php

declare(strict_types=1);

namespace BookTools;

use BookTools\ResourcePreProcessor\ResourcePreProcessor;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ResourceProcessor
{
    private ResourceLoader $resourceLoader;

    /**
     * @var ResourcePreProcessor[]
     */
    private array $resourcePreProcessors;

    /**
     * @param ResourcePreProcessor[] $resourcePreProcessors
     */
    public function __construct(ResourceLoader $resourceLoader, array $resourcePreProcessors)
    {
        $this->resourceLoader = $resourceLoader;
        $this->resourcePreProcessors = $resourcePreProcessors;
    }

    public function loadAndProcess(SmartFileInfo $includedFromFile, string $link): string
    {
        $loadedFile = $this->resourceLoader->load($includedFromFile, $link);

        $contents = $loadedFile->getContents();
        foreach ($this->resourcePreProcessors as $resourcePreProcessor) {
            $contents = $resourcePreProcessor->process($contents, $loadedFile);
        }

        return $contents;
    }
}
