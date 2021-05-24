<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\ResourceAttributes;
use BookTools\ResourceLoader\IncludedResource;

final class DelegatingResourcePreProcessor implements ResourcePreProcessor
{
    /**
     * @param array<ResourcePreProcessor> $preProcessors
     */
    public function __construct(
        private array $preProcessors
    ) {
    }

    public function process(
        string $fileContents,
        IncludedResource $includedResource,
        ResourceAttributes $resourceAttributes
    ): string {
        $processedContents = $fileContents;

        foreach ($this->preProcessors as $preProcessor) {
            $processedContents = $preProcessor->process($processedContents, $includedResource, $resourceAttributes);
        }

        return $processedContents;
    }
}
