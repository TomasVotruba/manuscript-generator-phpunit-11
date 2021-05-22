<?php

declare(strict_types=1);

namespace BookTools;

use BookTools\ResourcePreProcessor\ResourcePreProcessor;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Application implements ApplicationInterface
{
    private Configuration $configuration;

    private HeadlineCapitalizer $headlineCapitalizer;

    private ResourceProcessor $resourceProcessor;

    /**
     * @var array<ResourcePreProcessor>
     */
    public function __construct(
        Configuration $configuration,
        HeadlineCapitalizer $headlineCapitalizer,
        ResourceProcessor $resourceProcessor
    )
    {
        $this->configuration = $configuration;
        $this->headlineCapitalizer = $headlineCapitalizer;

        $this->resourceProcessor = $resourceProcessor;
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
        $contents = $markdownFile->contentsWithResourcesInlined($this->resourceProcessor);

        if ($this->configuration->capitalizeHeadlines()) {
            $contents = $this->headlineCapitalizer->capitalizeHeadlines($contents);
        }

        return $contents;
    }
}
