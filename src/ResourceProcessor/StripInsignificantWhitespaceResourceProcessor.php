<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use ManuscriptGenerator\ResourceLoader\LoadedResource;

final readonly class StripInsignificantWhitespaceResourceProcessor implements ResourceProcessor
{
    public function __construct(
        private InsignificantWhitespaceStripper $stripper
    ) {
    }

    public function process(LoadedResource $resource): void
    {
        $resource->setContents($this->stripper->strip($resource->contents()));
    }
}
