<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use ManuscriptGenerator\ResourceLoader\LoadedResource;

/**
 * @see ApplyCropAttributesProcessorTest
 */
final class ApplyCropAttributesProcessor implements ResourceProcessor
{
    public function process(LoadedResource $resource): void
    {
        $cropStart = $resource->attributes()
            ->getIntOrNull('crop-start');
        $cropEnd = $resource->attributes()
            ->getIntOrNull('crop-end');

        if ($cropStart === null && $cropEnd === null) {
            return;
        }

        $croppedContent = $this->selectLines(
            $resource->contents(),
            $cropStart === null ? null : $cropStart,
            $cropEnd === null ? null : $cropEnd,
        );

        $resource->attributes()
            ->remove('crop-start');
        $resource->attributes()
            ->remove('crop-end');

        $resource->setContents($croppedContent);
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
