<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\Markua\Parser\Node\Attributes;
use BookTools\ResourceLoader\LoadedResource;

final class DelegatingResourcePreProcessor implements ResourcePreProcessor
{
    /**
     * @param array<ResourcePreProcessor> $preProcessors
     */
    public function __construct(
        private array $preProcessors
    ) {
    }

    public function process(LoadedResource $includedResource, Attributes $resourceAttributes): LoadedResource
    {
        $processedResource = $includedResource;

        foreach ($this->preProcessors as $preProcessor) {
            $processedResource = $preProcessor->process($processedResource, $resourceAttributes);
        }

        return $processedResource;
    }
}
