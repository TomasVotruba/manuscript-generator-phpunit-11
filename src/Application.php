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
        ] as $srcFile => $targetFile) {
            $srcFilePath = new SmartFileInfo($this->configuration->manuscriptSrcDir() . '/' . $srcFile);

            $markdownFile = new MarkdownFile($srcFilePath);

            $includedResources = $markdownFile->includedResources();
            foreach ($includedResources as $includedResource) {
                $targetFilePathname = $this->configuration->manuscriptTargetDir() . '/'
                    . $includedResource->getRelativeFilePathFromDirectory($this->configuration->manuscriptSrcDir());
                copy($includedResource->getPathname(), $targetFilePathname);
            }

            $targetTxtFilePathname = $this->configuration->manuscriptTargetDir() . '/' . $targetFile;
            $txtLines = [];
            foreach ($includedResources as $includedResource) {
                $txtLines[] = $includedResource->getRelativeFilePathFromDirectory(
                    $this->configuration->manuscriptSrcDir()
                );
            }
            file_put_contents($targetTxtFilePathname, implode("\n", $txtLines) . "\n");
        }
    }
}
