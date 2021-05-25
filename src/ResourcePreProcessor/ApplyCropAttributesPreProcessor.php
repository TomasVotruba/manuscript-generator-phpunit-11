<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\Markua\Parser\Node\Attributes;
use BookTools\ResourceLoader\LoadedResource;
use BookTools\Test\ApplyCropAttributesPreProcessorTest;

/**
 * @see ApplyCropAttributesPreProcessorTest
 */
final class ApplyCropAttributesPreProcessor implements ResourcePreProcessor
{
    public function process(LoadedResource $includedResource, Attributes $resourceAttributes): LoadedResource
    {
        $cropStart = $resourceAttributes->valueOf('crop-start');
        $cropEnd = $resourceAttributes->valueOf('crop-end');

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
