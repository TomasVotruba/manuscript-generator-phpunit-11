<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use Symplify\SmartFileSystem\SmartFileInfo;

final class DelegatingResourcePreProcessor implements ResourcePreProcessor
{
    /**
     * @param array<ResourcePreProcessor> $preProcessors
     */
    public function __construct(
        private array $preProcessors
    ) {
    }

    public function process(string $fileContents, SmartFileInfo $resourceFile): string
    {
        $processedContents = $fileContents;

        foreach ($this->preProcessors as $preProcessor) {
            $processedContents = $preProcessor->process($processedContents, $resourceFile);
        }

        return $processedContents;
    }
}
