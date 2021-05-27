<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor;

use BookTools\Markua\Parser\Node;
use BookTools\Markua\Parser\Node\InlineResource;
use BookTools\Markua\Parser\Visitor\NodeVisitor;
use BookTools\ResourceLoader\LoadedResource;
use BookTools\ResourceProcessor\ResourceProcessor;

final class ProcessInlineResourcesNodeVisitor implements NodeVisitor
{
    /**
     * @param array<ResourceProcessor> $resourceProcessors
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
            $processor->process($processedResource, $node->attributes);
        }

        $node->contents = $processedResource->contents();

        return $node;
    }
}
