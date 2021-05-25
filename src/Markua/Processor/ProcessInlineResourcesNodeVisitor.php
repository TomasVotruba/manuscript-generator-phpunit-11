<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor;

use BookTools\Markua\Parser\Node;
use BookTools\Markua\Parser\Node\InlineResource;
use BookTools\Markua\Parser\Visitor\NodeVisitor;
use BookTools\ResourceLoader\LoadedResource;
use BookTools\ResourcePreProcessor\ResourcePreProcessor;

final class ProcessInlineResourcesNodeVisitor implements NodeVisitor
{
    /**
     * @param array<ResourcePreProcessor> $resourceProcessors
     */
    public function __construct(
        private array $resourceProcessors
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof InlineResource) {
            return null;
        }

        $processedResource = LoadedResource::createFromInlineResource($node);

        foreach ($this->resourceProcessors as $processor) {
            $processedResource = $processor->process($processedResource, $node->attributes);
        }

        $node->contents = $processedResource->contents();

        return $node;
    }
}
