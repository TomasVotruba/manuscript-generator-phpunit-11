<?php

declare(strict_types=1);

namespace BookTools;

use BookTools\FileOperations\FileOperations;
use BookTools\ResourceLoader\ResourceLoader;
use BookTools\ResourcePreProcessor\ResourcePreProcessor;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Application implements ApplicationInterface
{
    public function __construct(
        private Configuration $configuration,
        private HeadlineCapitalizer $headlineCapitalizer,
        private ResourceLoader $resourceLoader,
        private ResourcePreProcessor $preProcessor,
        private FileOperations $fileOperations
    ) {
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
            $this->fileOperations->putContents($targetFilePathname, implode("\n", $combinedMarkdownContents));

            $targetTxtFilePathname = $this->configuration->manuscriptTargetDir() . '/' . $targetFileName;
            $txtFileContents = $srcFileName . "\n";
            $this->fileOperations->putContents($targetTxtFilePathname, $txtFileContents);
        }
    }

    private function processMarkdownContents(MarkdownFile $markdownFile): string
    {
        $contents = $markdownFile->contentsWithResourcesInlined($this->resourceLoader, $this->preProcessor);

        if ($this->configuration->capitalizeHeadlines()) {
            $contents = $this->headlineCapitalizer->capitalizeHeadlines($contents);
        }

        return $contents;
    }
}
