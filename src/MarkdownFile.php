<?php

declare(strict_types=1);

namespace BookTools;

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

            $attributes = ResourceAttributes::fromString($lines[$index - 1]);
            if (! $attributes->isEmpty()) {
                // Remove the previous line since it contained attributes (hacky solution!)
                unset($output[count($output) - 1]);
            }

            $resource = $resourceLoader->load($this->fileInfo, $matches['link']);

            if (in_array($resource->getSuffix(), ['md', 'markdown'], true)) {
                $output[] = rtrim($resource->getContents());
                continue;
            } elseif (in_array($resource->getSuffix(), ['gif', 'jpeg', 'jpg', 'png', 'svg'], true)) {
                // Don't try to inline images
                continue;
            }
            if ($matches[self::REGEX_CAPTION] !== '') {
                $attributes->setAttribute(Attribute::quoted('caption', $matches[self::REGEX_CAPTION]));
            }
            $attributes->setAttribute(new Attribute('format', $resource->getSuffix()));

            $preProcessedContents = $preProcessor->process($resource->getContents(), $resource, $attributes);
            $output[] = $attributes->asString();
            $output[] = '```';
            $output[] = rtrim($preProcessedContents);
            $output[] = '```';
        }

        return implode("\n", $output);
    }
}
