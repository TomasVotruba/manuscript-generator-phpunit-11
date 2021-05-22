<?php

declare(strict_types=1);

namespace BookTools;

use Symplify\SmartFileSystem\SmartFileInfo;

final class Application implements ApplicationInterface
{
    private Configuration $configuration;

    private HeadlineCapitalizer $headlineCapitalizer;

    public function __construct(Configuration $configuration, HeadlineCapitalizer $headlineCapitalizer)
    {
        $this->configuration = $configuration;
        $this->headlineCapitalizer = $headlineCapitalizer;
    }

    public function generateManuscript(): void
    {
        foreach ([
            'book.md' => 'Book.txt',
            'subset.md' => 'Subset.txt',
        ] as $srcFileName => $targetFileName) {
            $srcFilePath = new SmartFileInfo($this->configuration->manuscriptSrcDir() . '/' . $srcFileName);

            $markdownFile = new MarkdownFile($srcFilePath);

            $includedResources = $markdownFile->includedResources();
            $combinedMarkdownContents = [];
            foreach ($includedResources as $includedResource) {
                $combinedMarkdownContents[] = $this->processMarkdownContents(new MarkdownFile($includedResource));
            }
            $targetFilePathname = $this->configuration->manuscriptTargetDir() . '/' . $srcFileName;
            file_put_contents($targetFilePathname, implode("\n", $combinedMarkdownContents));

            $targetTxtFilePathname = $this->configuration->manuscriptTargetDir() . '/' . $targetFileName;
            $txtFileContents = $srcFileName . "\n";
            file_put_contents($targetTxtFilePathname, $txtFileContents);
        }
    }

    private function processMarkdownContents(MarkdownFile $markdownFile): string
    {
        $contents = $this->inlineIncludedMarkdownResources($markdownFile);

        if ($this->configuration->capitalizeHeadlines()) {
            $contents = $this->headlineCapitalizer->capitalizeHeadlines($contents);
        }

        return $contents;
    }

    private function inlineIncludedMarkdownResources(MarkdownFile $markdownFile): string
    {
        // A missing feature in Markua: the ability to include other .md files using standard resource notation ![]().

        $output = [];
        $lines = explode("\n", $markdownFile->contents());
        foreach ($lines as $line) {
            if (! str_starts_with($line, '![')) {
                $output[] = $line;
                continue;
            }

            $output[] = rtrim($markdownFile->extractIncludedResource($line)->getContents());
        }

        return implode("\n", $output);
    }
}
