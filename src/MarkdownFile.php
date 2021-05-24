<?php

declare(strict_types=1);

namespace BookTools;

use BookTools\Markua\Attribute;
use BookTools\Markua\Attributes;
use BookTools\Markua\SimpleMarkuaParser;
use BookTools\ResourceLoader\ResourceLoader;
use BookTools\ResourcePreProcessor\ResourcePreProcessor;
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

    public function contentsWithResourcesInlined(
        ResourceLoader $resourceLoader,
        ResourcePreProcessor $preProcessor
    ): string {
        // A missing feature in Markua: the ability to include other .md files using standard resource notation ![]().
        $output = [];
        $lines = explode("\n", $this->contents());
        foreach ($lines as $index => $line) {
            if (! str_starts_with($line, '![')) {
                $output[] = $line;
                continue;
            }

            $result = preg_match(self::INCLUDED_RESOURCE_REGEX, $line, $matches);
            if ($result !== 1) {
                throw new RuntimeException('Could not extract included resource from line: ' . $line);
            }

            $parser = new SimpleMarkuaParser();

            $attributesLine = $lines[$index - 1];
            if (str_starts_with($attributesLine, '{')) {
                $attributes = $parser->parseAttributes($attributesLine);

                // Remove the previous line since it contained attributes (hacky solution!)
                unset($output[count($output) - 1]);
                // More hacks: reset the indices
                $output = array_values($output);
            // @TODO we need to look ahead instead of look back
            } else {
                $attributes = new Attributes([]);
            }

            $resource = $resourceLoader->load($this->fileInfo, $matches['link']);

            if (in_array($resource->fileExtension(), ['md', 'markdown'], true)) {
                $output[] = rtrim($resource->contents());
                continue;
            } elseif (in_array($resource->fileExtension(), ['gif', 'jpeg', 'jpg', 'png', 'svg'], true)) {
                // Don't try to inline images
                continue;
            }
            if ($matches[self::REGEX_CAPTION] !== '') {
                $attributes->setAttribute('caption', Attribute::quote($matches[self::REGEX_CAPTION]));
            }
            $attributes->setAttribute('format', $resource->fileExtension());

            $processedResource = $preProcessor->process($resource, $attributes);
            $output[] = $attributes->asMarkua();
            $output[] = '```';
            $output[] = rtrim($processedResource->contents());
            $output[] = '```';
        }

        return implode("\n", $output);
    }
}
