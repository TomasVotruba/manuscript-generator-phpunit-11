<?php

declare(strict_types=1);

namespace BookTools\ResourceProcessor;

use BookTools\Markua\Parser\Node\AttributeList;
use BookTools\ResourceLoader\LoadedResource;

final class StripInsignificantWhitespaceResourceProcessor implements ResourceProcessor
{
    public function __construct(
        private InsignificantWhitespaceStripper $stripper
    ) {
    }

    public function process(LoadedResource $includedResource, AttributeList $resourceAttributes): LoadedResource
    {
        return $includedResource->withContents($this->stripper->strip($includedResource->contents()));
    }
}
