<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\ResourceAttributes;
use BookTools\ResourceLoader\IncludedResource;
use BookTools\Test\ApplyCropAttributesPreProcessorTest;

/**
 * @see ApplyCropAttributesPreProcessorTest
 */
final class ApplyCropAttributesPreProcessor implements ResourcePreProcessor
{
    public function process(
        string $fileContents,
        IncludedResource $includedResource,
        ResourceAttributes $resourceAttributes
    ): string {
        $cropStart = $resourceAttributes->attribute('crop-start');
        $cropEnd = $resourceAttributes->attribute('crop-end');

        if ($cropStart === null && $cropEnd === null) {
            return $fileContents;
        }

        $croppedContent = $this->selectLines(
            $fileContents,
            $cropStart === null ? null : (int) $cropStart,
            $cropEnd === null ? null : (int) $cropEnd,
        );

        $resourceAttributes->removeAttribute('crop-start');
        $resourceAttributes->removeAttribute('crop-end');

        return $croppedContent;
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
