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
        IncludedResource $includedResource,
        ResourceAttributes $resourceAttributes
    ): IncludedResource {
        $cropStart = $resourceAttributes->attribute('crop-start');
        $cropEnd = $resourceAttributes->attribute('crop-end');

        if ($cropStart === null && $cropEnd === null) {
            return $includedResource;
        }

        $croppedContent = $this->selectLines(
            $includedResource->contents(),
            $cropStart === null ? null : (int) $cropStart,
            $cropEnd === null ? null : (int) $cropEnd,
        );

        $resourceAttributes->removeAttribute('crop-start');
        $resourceAttributes->removeAttribute('crop-end');

        return $includedResource->withContents($croppedContent);
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
