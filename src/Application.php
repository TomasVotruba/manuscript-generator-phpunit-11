<?php

declare(strict_types=1);

namespace BookTools;

use Symplify\SmartFileSystem\SmartFileInfo;

final class Application implements ApplicationInterface
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
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
                $combinedMarkdownContents[] = $includedResource->getContents();
            }
            $targetFilePathname = $this->configuration->manuscriptTargetDir() . '/' . $srcFileName;
            file_put_contents($targetFilePathname, implode("\n", $combinedMarkdownContents));

            $targetTxtFilePathname = $this->configuration->manuscriptTargetDir() . '/' . $targetFileName;
            $txtFileContents = $srcFileName . "\n";
            file_put_contents($targetTxtFilePathname, $txtFileContents);
        }
    }
}
