<?php

declare(strict_types=1);

namespace BookTools;

use BookTools\FileOperations\FileOperations;
use BookTools\Markua\Processor\MarkuaProcessor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ManuscriptGenerator
{
    public function __construct(
        private Configuration $configuration,
        private FileOperations $fileOperations,
        private MarkuaProcessor $markuaProcessor,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function generateManuscript(): void
    {
        foreach ([
            'book.md' => 'Book.txt',
            'subset.md' => 'Subset.txt',
        ] as $srcFileName => $targetFileName) {
            $srcFilePath = $this->configuration->manuscriptSrcDir() . '/' . $srcFileName;
            if (! is_file($srcFilePath)) {
                continue;
            }

            $srcFilePath = new SmartFileInfo($srcFilePath);

            $processedContents = $this->markuaProcessor->process($srcFilePath, $srcFilePath->getContents());

            $targetFilePathname = $this->configuration->manuscriptTargetDir() . '/' . $srcFileName;
            $this->fileOperations->putContents($targetFilePathname, $processedContents);

            $targetTxtFilePathname = $this->configuration->manuscriptTargetDir() . '/' . $targetFileName;
            $txtFileContents = $srcFileName . "\n";
            $this->fileOperations->putContents($targetTxtFilePathname, $txtFileContents);
        }

        $this->eventDispatcher->dispatch(new ManuscriptWasGenerated($this->configuration->manuscriptTargetDir()));
    }
}
