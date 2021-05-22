<?php

declare(strict_types=1);

namespace BookTools;

use RuntimeException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MarkdownFile
{
    private const INCLUDED_RESOURCE_REGEX = '/\!\[(?<text>.*?)\]\((?<link>.+)\)/';

    private SmartFileInfo $fileInfo;

    public function __construct(SmartFileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
    }

    /**
     * @return array<SmartFileInfo>
     */
    public function includedResources(): array
    {
        $markdownString = $this->fileInfo->getContents();

        $count = preg_match_all(self::INCLUDED_RESOURCE_REGEX, $markdownString, $matches);

        if ($count === 0) {
            return [];
        }

        return array_map(
            fn (string $relativePathname) => new SmartFileInfo(
                $this->fileInfo->getRealPathDirectory() . '/' . $relativePathname
            ),
            $matches['link']
        );
    }

    public function contents(): string
    {
        return $this->fileInfo->getContents();
    }

    public function contentsWithIncludedMarkdownResourcesInlined(): string
    {
        // A missing feature in Markua: the ability to include other .md files using standard resource notation ![]().
        $output = [];
        $lines = explode("\n", $this->contents());
        foreach ($lines as $line) {
            if (! str_starts_with($line, '![')) {
                $output[] = $line;
                continue;
            }

            $result = preg_match(self::INCLUDED_RESOURCE_REGEX, $line, $matches);
            if ($result !== 1) {
                throw new RuntimeException('Could not extract included resource from line: ' . $line);
            }

            if (! str_ends_with($matches['link'], '.md')) {
                continue;
            }

            $resource = new SmartFileInfo($this->fileInfo->getRealPathDirectory() . '/' . $matches['link']);
            $output[] = rtrim($resource->getContents());
        }

        return implode("\n", $output);
    }
}
