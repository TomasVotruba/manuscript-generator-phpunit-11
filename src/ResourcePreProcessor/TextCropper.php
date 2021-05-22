<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

final class TextCropper
{
    public function __construct(
        private string $cropStartMarker,
        private string $cropEndMarker
    ) {
        assert($cropStartMarker !== '');
        assert($cropEndMarker !== '');
    }

    public function crop(string $text): string
    {
        $cropStartPosition = strpos($text, $this->cropStartMarker);
        $cropEndPosition = strpos($text, $this->cropEndMarker);

        if ($cropStartPosition === false && $cropEndPosition === false) {
            // No need to crop
            return $text;
        }

        if ($cropStartPosition === false) {
            // Don't crop the start
            $cropStartPosition = 0;
        } else {
            // Also crop the marker itself
            $cropStartPosition += strlen($this->cropStartMarker);
        }

        if ($cropEndPosition === false) {
            // Don't crop the end
            $cropEndPosition = strlen($text);
        }

        $length = $cropEndPosition - $cropStartPosition;

        return substr($text, $cropStartPosition, $length);
    }
}
