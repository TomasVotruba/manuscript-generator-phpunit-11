<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor\LineLength;

use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\ResourceLoader\LoadedResource;
use ManuscriptGenerator\ResourceProcessor\ResourceProcessor;

final class FixLongLinesResourceProcessor implements ResourceProcessor
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

        foreach ($fixedLines as $lineIndex => $fixedLine) {
            if (strlen($fixedLine) > $maximumLineLength) {
                throw new CouldNotFixLine(
                    sprintf(
                        'None of the line fixers was reduce the line %d to the maximum length of %d. Full resource contents: %s',
                        $lineIndex + 1,
                        $maximumLineLength,
                        implode("\n", $fixedLines)
                    )
                );
            }
        }

        $resource->setContents(implode("\n", $fixedLines));
    }
}
