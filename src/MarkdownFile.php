<?php

declare(strict_types=1);

namespace BookTools;

use RuntimeException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MarkdownFile
{
    public const REGEX_CAPTION = 'caption';

    private const INCLUDED_RESOURCE_REGEX = '/\!\[(?<caption>.*?)\]\((?<link>.+)\)/';

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

    public function contentsWithResourcesInlined(): string
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

            $resource = new SmartFileInfo($this->fileInfo->getRealPathDirectory() . '/resources/' . $matches['link']);

            if (in_array($resource->getSuffix(), ['md', 'markdown'], true)) {
                $output[] = rtrim($resource->getContents());
                continue;
            } elseif (in_array($resource->getSuffix(), ['gif', 'jpeg', 'jpg', 'png', 'svg'], true)) {
                // Don't try to inline images
                continue;
            }
            $attributes = [];
            if ($matches[self::REGEX_CAPTION] !== '') {
                $attributes[] = [
                    'key' => 'caption',
                    'value' => '"' . addslashes($matches[self::REGEX_CAPTION]) . '"',
                ];
            }
            $attributes[] = [
                'key' => 'format',
                'value' => $resource->getSuffix(),
            ];

            $attributes = array_map(
                fn (array $attribute) => $attribute['key'] . ': ' . $attribute['value'],
                $attributes
            );

            $attributes = '{' . implode(', ', $attributes) . '}';
            $output[] = $attributes;
            $output[] = '```';
            $output[] = rtrim($resource->getContents());
            $output[] = '```';
        }

        return implode("\n", $output);
    }
}
