<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use ManuscriptGenerator\ResourceLoader\LoadedResource;

/**
 * @see ApplyCropAttributesProcessorTest
 */
final class ApplyCropAttributesProcessor implements ResourceProcessor
{
    public function process(LoadedResource $includedResource): void
    {
        $cropStart = $includedResource->getAttribute('crop-start');
        $cropEnd = $includedResource->getAttribute('crop-end');

        if ($cropStart === null && $cropEnd === null) {
            return;
        }

        $croppedContent = $this->selectLines(
            $includedResource->contents(),
            $cropStart === null ? null : (int) $cropStart,
            $cropEnd === null ? null : (int) $cropEnd,
        );

        $includedResource->removeAttribute('crop-start');
        $includedResource->removeAttribute('crop-end');

        $includedResource->setContents($croppedContent);
    }

    public static function selectLines(string $contents, ?int $firstLineIncluded, ?int $lastLineIncluded): string
    {
        $lines = explode("\n", $contents);

        if ($firstLineIncluded === null) {
            $firstLineIncluded = 1;
        }

        if ($lastLineIncluded === null) {
            $lastLineIncluded = count($lines);
        }

        $startIndex = $firstLineIncluded - 1;

        return implode("\n", array_slice($lines, $startIndex, $lastLineIncluded - $firstLineIncluded + 1));
    }
}
