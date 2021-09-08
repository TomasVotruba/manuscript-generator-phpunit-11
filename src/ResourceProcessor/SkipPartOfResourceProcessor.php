<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use ManuscriptGenerator\ResourceLoader\LoadedResource;

final class SkipPartOfResourceProcessor implements ResourceProcessor
{
    private const SKIP_START_MARKER = '// skip-start';

    private const SKIP_END_MARKER = '// skip-end';

    public function process(LoadedResource $resource): void
    {
        $replacement = $resource->attributes()
            ->getStringOrNull('skipReplacement');
        if (! is_string($replacement)) {
            $replacement = '// ...';
        }

        $skipper = new TextSkipper(self::SKIP_START_MARKER, self::SKIP_END_MARKER, $replacement);

        $resource->setContents($skipper->skipParts($resource->contents()));
    }
}
