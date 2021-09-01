<?php

declare(strict_types=1);

namespace ManuscriptGenerator;

use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\FileOperations\FileOperations;
use ManuscriptGenerator\Markua\Processor\MarkuaProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ManuscriptGenerator
{
    public function __construct(
        private RuntimeConfiguration $configuration,
        private DependenciesInstaller $dependenciesInstaller,
        private FileOperations $fileOperations,
        private MarkuaProcessor $markuaProcessor,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger
    ) {
    }

    public function generateManuscript(): void
    {
        if ($this->configuration->updateDependencies()) {
            // Only if the user wants to force-update dependencies should we do it at once for all subprojects
            $this->logger->info('Updating all manuscript source dependencies');
            $this->dependenciesInstaller->updateAll();
        }

        $manuscriptFiles = new ManuscriptFiles();

        foreach ([
            'book.md' => 'Book.txt',
            'subset.md' => 'Subset.txt',
        ] as $srcFileName => $targetFileName) {
            $srcFilePath = $this->configuration->manuscriptSrcDir() . '/' . $srcFileName;
            if (! is_file($srcFilePath)) {
                $this->logger->warning('Skipping generation of {targetFileName} because {srcFilePath} does not exist', [
                    'targetFileName' => $targetFileName,
                    'srcFilePath' => $srcFilePath,
                ]);

                continue;
            }

            $srcFilePath = ExistingFile::fromPathname($srcFilePath);

            $processedContents = $this->markuaProcessor->process(
                $srcFilePath,
                $srcFilePath->contents(),
                $manuscriptFiles
            );

            $manuscriptFiles->addFile($srcFileName, $processedContents);

            $txtFileContents = $srcFileName . "\n";
            $manuscriptFiles->addFile($targetFileName, $txtFileContents);
        }

        $manuscriptFiles->dumpTo($this->configuration->manuscriptTargetDir());

        $this->logger->info('Generated the manuscript files in {manuscriptTargetDir}', [
            'manuscriptTargetDir' => $this->configuration->manuscriptTargetDir(),
        ]);
        $this->eventDispatcher->dispatch(new ManuscriptWasGenerated($this->configuration->manuscriptTargetDir()));
    }
}
