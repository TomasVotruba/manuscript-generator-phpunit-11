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
        IncludedResource $includedResource,
        ResourceAttributes $resourceAttributes
    ): IncludedResource {
        $processedResource = $includedResource;

        foreach ($this->preProcessors as $preProcessor) {
            $processedResource = $preProcessor->process($processedResource, $resourceAttributes);
        }

        return $processedResource;
    }
}
