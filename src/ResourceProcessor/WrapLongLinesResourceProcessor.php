<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\ResourceLoader\LoadedResource;
use ManuscriptGenerator\ResourceProcessor\LineLength\LineFixer;

final class WrapLongLinesResourceProcessor implements ResourceProcessor
{
    public function __construct(
        private RuntimeConfiguration $configuration,
        private LineFixer $lineFixer
    ) {
    }

    public function process(LoadedResource $resource): void
    {
        $maximumLineLength = $this->configuration->maximumLineLengthForInlineResources();

        $fixedLines = $this->lineFixer->fix(explode("\n", $resource->contents()), $maximumLineLength);

        $resource->setContents(implode("\n", $fixedLines));
    }
}
