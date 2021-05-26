<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\Markua\Parser\Node\AttributeList;
use BookTools\ResourceLoader\LoadedResource;

final class StripWhitespacesAtEndOfLineProcessor implements ResourcePreProcessor
{
    private InsignificantWhitespaceStripper $stripper;

    public function __construct(InsignificantWhitespaceStripper $stripper)
    {
        $this->stripper = $stripper;
    }

    public function process(LoadedResource $includedResource, AttributeList $resourceAttributes): LoadedResource
    {
        return $includedResource->withContents($this->stripper->strip($includedResource->contents()));
    }
}
