<?php

declare(strict_types=1);

namespace ManuscriptGenerator;

use ManuscriptGenerator\Cli\ResultPrinter;
use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\ManuscriptFiles\ManuscriptDiff;
use ManuscriptGenerator\ManuscriptFiles\ManuscriptFiles;
use ManuscriptGenerator\Markua\Processor\MarkuaProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ManuscriptGenerator
{
    public function __construct(
        private RuntimeConfiguration $configuration,
        private DependenciesInstaller $dependenciesInstaller,
        private MarkuaProcessor $markuaProcessor,
        private LoggerInterface $logger,
        private ResultPrinter $resultPrinter
    ) {
    }

    public function generateManuscript(): ManuscriptFiles
    {
        if ($this->configuration->updateDependencies()) {
            // Only if the user wants to force-update dependencies should we do it at once for all subprojects
            $this->logger->info('Updating all manuscript source dependencies');
            $this->dependenciesInstaller->updateAll();
        }

        $manuscriptFiles = ManuscriptFiles::createEmpty();

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

            $processedContents = $this->markuaProcessor->process($srcFilePath, $manuscriptFiles);

            $manuscriptFiles->addFile($srcFileName, $processedContents);

            $txtFileContents = $srcFileName . "\n";
            $manuscriptFiles->addFile($targetFileName, $txtFileContents);
        }

        return $manuscriptFiles;
    }

    public function diffWithExistingManuscriptDir(ManuscriptFiles $manuscriptFiles): ManuscriptDiff
    {
        return $manuscriptFiles->diff(ManuscriptFiles::fromDir($this->configuration->manuscriptTargetDir()));
    }

    public function printDiff(ManuscriptDiff $diff, OutputInterface $output): void
    {
        $this->resultPrinter->printManuscriptDiff($diff, $output);
    }

    public function dumpManuscriptFiles(ManuscriptFiles $manuscriptFiles): void
    {
        $manuscriptFiles->dumpTo($this->configuration->manuscriptTargetDir());

        $this->logger->info('Generated the manuscript files in {manuscriptTargetDir}', [
            'manuscriptTargetDir' => $this->configuration->manuscriptTargetDir(),
        ]);
    }
}
