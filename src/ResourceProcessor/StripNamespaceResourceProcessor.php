<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use ManuscriptGenerator\ResourceLoader\LoadedResource;

final class StripNamespaceResourceProcessor implements ResourceProcessor
{
    /**
     * @var array<string>
     */
    private array $searchFor;

    public function __construct(Psr4SrcNamespaceCollector $collector)
    {
        $this->searchFor = $collector->collect();
    }

    public function process(LoadedResource $resource): void
    {
        $resource->setContents(str_replace($this->searchFor, '', $resource->contents()));
    }
}
