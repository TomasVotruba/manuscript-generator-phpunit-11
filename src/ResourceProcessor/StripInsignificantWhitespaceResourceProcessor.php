<?php

declare(strict_types=1);

namespace BookTools\ResourceProcessor;

use BookTools\ResourceLoader\LoadedResource;

final class StripInsignificantWhitespaceResourceProcessor implements ResourceProcessor
{
    public function __construct(
        private InsignificantWhitespaceStripper $stripper
    ) {
    }

    public function process(LoadedResource $includedResource): void
    {
        $includedResource->setContents($this->stripper->strip($includedResource->contents()));
    }
}
