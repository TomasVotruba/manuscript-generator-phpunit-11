<?php

declare(strict_types=1);

namespace BookTools\ResourceProcessor;

use BookTools\Markua\Parser\Node\AttributeList;
use BookTools\ResourceLoader\LoadedResource;

/**
 * @see ApplyCropAttributesProcessorTest
 */
final class ApplyCropAttributesProcessor implements ResourceProcessor
{
    public function process(LoadedResource $includedResource, AttributeList $resourceAttributes): LoadedResource
    {
        $cropStart = $resourceAttributes->get('crop-start');
        $cropEnd = $resourceAttributes->get('crop-end');

        if ($cropStart === null && $cropEnd === null) {
            return $includedResource;
        }

        $croppedContent = $this->selectLines(
            $includedResource->contents(),
            $cropStart === null ? null : (int) $cropStart,
            $cropEnd === null ? null : (int) $cropEnd,
        );

        $resourceAttributes->remove('crop-start');
        $resourceAttributes->remove('crop-end');

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
