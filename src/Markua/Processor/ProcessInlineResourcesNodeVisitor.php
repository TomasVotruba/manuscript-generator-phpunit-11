<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\InlineResource;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;
use ManuscriptGenerator\ResourceLoader\LoadedResource;
use ManuscriptGenerator\ResourceProcessor\ResourceProcessor;

final class ProcessInlineResourcesNodeVisitor extends AbstractNodeVisitor
{
    /**
     * @param array<ResourceProcessor> $resourceProcessors
     */
    public function __construct(
        private readonly array $resourceProcessors
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof InlineResource) {
            return null;
        }

        $processedResource = LoadedResource::createFromInlineResource($node);

        foreach ($this->resourceProcessors as $processor) {
            $processor->process($processedResource);
        }

        $node->contents = $processedResource->contents();

        return $node;
    }
}
