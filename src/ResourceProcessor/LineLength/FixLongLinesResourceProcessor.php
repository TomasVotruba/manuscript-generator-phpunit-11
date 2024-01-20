<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor\LineLength;

use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\ResourceLoader\LoadedResource;
use ManuscriptGenerator\ResourceProcessor\ResourceProcessor;

final readonly class FixLongLinesResourceProcessor implements ResourceProcessor
{
    public function __construct(
        private BookProjectConfiguration $bookProjectConfiguration,
        private LineFixer $lineFixer
    ) {
    }

    public function process(LoadedResource $resource): void
    {
        $maximumLineLength = $this->bookProjectConfiguration->maximumLineLengthForInlineResources();

        $fixedLines = $this->lineFixer->fix(explode("\n", $resource->contents()), $maximumLineLength);

        foreach ($fixedLines as $lineIndex => $fixedLine) {
            if (strlen($fixedLine) > $maximumLineLength) {
                throw new CouldNotFixLine(
                    sprintf(
                        'None of the line fixers was able to reduce line %d to the maximum length of %d. Full resource contents:' . "\n" . '%s',
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
