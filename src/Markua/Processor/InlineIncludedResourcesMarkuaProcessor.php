<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor;

use BookTools\Markua\Parser\Attribute;
use BookTools\Markua\Parser\Attributes;
use BookTools\Markua\Parser\SimpleMarkuaParser;
use BookTools\ResourceLoader\ResourceLoader;
use BookTools\ResourcePreProcessor\ResourcePreProcessor;
use RuntimeException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InlineIncludedResourcesMarkuaProcessor implements MarkuaProcessor
{
    public const REGEX_CAPTION = 'caption';

    private const INCLUDED_RESOURCE_REGEX = '/\!\[(?<caption>.*?)\]\((?<link>.+)\)/';

    public function __construct(
        private ResourceLoader $resourceLoader,
        private ResourcePreProcessor $resourcePreProcessor
    ) {
    }

    public function process(SmartFileInfo $markuaFileInfo, string $markua): string
    {
        // A missing feature in Markua: the ability to include other .md files using standard resource notation ![]().
        $output = [];
        $lines = explode("\n", $markua);
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

            $resource = $this->resourceLoader->load($markuaFileInfo, $matches['link']);

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

            $processedResource = $this->resourcePreProcessor->process($resource, $attributes);
            $output[] = $attributes->asMarkua();
            $output[] = '```';
            $output[] = rtrim($processedResource->contents());
            $output[] = '```';
        }

        return implode("\n", $output);
    }
}
