<?php

declare(strict_types=1);

namespace BookTools;

use BookTools\FileOperations\FileOperations;
use BookTools\Markua\Processor\MarkuaProcessor;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ManuscriptGenerator
{
    public function __construct(
        private Configuration $configuration,
        private FileOperations $fileOperations,
        private MarkuaProcessor $markuaProcessor
    ) {
    }

    public function generateManuscript(): void
    {
        foreach ([
            'book.md' => 'Book.txt',
            'subset.md' => 'Subset.txt',
        ] as $srcFileName => $targetFileName) {
            $srcFilePath = new SmartFileInfo($this->configuration->manuscriptSrcDir() . '/' . $srcFileName);

            $processedContents = $this->markuaProcessor->process($srcFilePath, $srcFilePath->getContents());

            $targetFilePathname = $this->configuration->manuscriptTargetDir() . '/' . $srcFileName;
            $this->fileOperations->putContents($targetFilePathname, $processedContents);

            $targetTxtFilePathname = $this->configuration->manuscriptTargetDir() . '/' . $targetFileName;
            $txtFileContents = $srcFileName . "\n";
            $this->fileOperations->putContents($targetTxtFilePathname, $txtFileContents);
        }
    }
}
