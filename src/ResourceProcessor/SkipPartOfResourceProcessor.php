<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use ManuscriptGenerator\ResourceLoader\LoadedResource;

final class SkipPartOfResourceProcessor implements ResourceProcessor
{
    public function process(LoadedResource $resource): void
    {
        $replacement = $resource->attributes()
            ->getStringOrNull('skipReplacement');
        if (! is_string($replacement)) {
            $replacement = null;
        }

        $skipper = new TextSkipper($replacement);

        $resource->setContents($skipper->skipParts($resource->contents()));
    }
}
