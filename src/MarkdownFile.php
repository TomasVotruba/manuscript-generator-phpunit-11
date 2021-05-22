<?php

declare(strict_types=1);

namespace BookTools;

use RuntimeException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MarkdownFile
{
    private const MARKDOWN_INCLUDED_RESOURCE_REGEX = '/\!\[(?<text>.*?)\]\((?<link>.+)\)/';

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

        $count = preg_match_all(self::MARKDOWN_INCLUDED_RESOURCE_REGEX, $markdownString, $matches);

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

    public function extractIncludedResource(string $markdownLine): SmartFileInfo
    {
        $result = preg_match(self::MARKDOWN_INCLUDED_RESOURCE_REGEX, $markdownLine, $matches);
        if ($result !== 1) {
            throw new RuntimeException('Could not extract included resource from line: ' . $markdownLine);
        }

        return new SmartFileInfo($this->fileInfo->getRealPathDirectory() . '/' . $matches['link']);
    }

    public function contents(): string
    {
        return $this->fileInfo->getContents();
    }
}
