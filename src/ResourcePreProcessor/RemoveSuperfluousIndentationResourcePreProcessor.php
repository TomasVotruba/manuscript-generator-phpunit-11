<?php

declare(strict_types=1);

namespace BookTools\ResourcePreProcessor;

use BookTools\Test\RemoveSuperfluousIndentationResourcePreProcessorTest;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see RemoveSuperfluousIndentationResourcePreProcessorTest
 */
final class RemoveSuperfluousIndentationResourcePreProcessor implements ResourcePreProcessor
{
    public function process(string $fileContents, SmartFileInfo $sourceFile): string {
        // @TODO determine if text-based and ignore if not

        $fileContents = $this->trim($fileContents);

        $result = preg_match_all('/(^|\n)([ ]*).+/', $fileContents, $matches);
        if ($result === 0) {
            // The file has no indentation at all
            return $fileContents;
        }

        $indentationLevels = array_map(fn (string $indentation) => strlen($indentation), $matches[2]);

        $minimumLevel = array_reduce(
            $indentationLevels,
            fn (
                $minimumLevel,
                $currentLevel
            ) => $currentLevel < $minimumLevel ? $currentLevel : $minimumLevel,
            PHP_INT_MAX
        );

        if ($minimumLevel === 0) {
            return $fileContents;
        }

        $fileContents = preg_replace('/(^|\n)([ ]{' . $minimumLevel . '})/', '$1', $fileContents);

        assert(is_string($fileContents));

        return $fileContents;
    }

    private function trim(string $fileContents): string
    {
        // Remove spaces, new lines, etc. from the end of the file contents
        $fileContents = rtrim($fileContents);

        /*
         * Remove empty line(s) from the beginning of the file
         * contents. We need to preserve the indentation of the first
         * line, so we don't remove spaces.
         */
        return ltrim($fileContents, "\n");
    }
}
