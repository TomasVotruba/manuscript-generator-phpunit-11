<?php

declare(strict_types=1);

namespace ManuscriptGenerator;

use ManuscriptGenerator\Cli\ResultPrinter;
use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\ManuscriptFiles\ManuscriptDiff;
use ManuscriptGenerator\ManuscriptFiles\ManuscriptFiles;
use ManuscriptGenerator\Markua\Processor\MarkuaProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ManuscriptGenerator
{
    public function __construct(
        private RuntimeConfiguration $configuration,
        private BookProjectConfiguration $bookProjectConfiguration,
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
            $this->dependenciesInstaller->updateAll($this->bookProjectConfiguration->manuscriptSrcDir());
        }

        $manuscriptFiles = ManuscriptFiles::createEmpty();

        $srcFileName = 'book.md';
        $srcFile = $this->bookProjectConfiguration->manuscriptSrcDir()
            ->appendPath($srcFileName)
            ->file();

        $processedContents = $this->markuaProcessor->process($srcFile->existing(), $manuscriptFiles);

        $manuscriptFiles->addFile($srcFileName, $processedContents);

        $txtFileContents = $srcFileName . "\n";
        $manuscriptFiles->addFile('Book.txt', $txtFileContents);

        return $manuscriptFiles;
    }

    public function diffWithExistingManuscriptDir(ManuscriptFiles $manuscriptFiles): ManuscriptDiff
    {
        return $manuscriptFiles->diff(ManuscriptFiles::fromDir($this->bookProjectConfiguration->manuscriptTargetDir()));
    }

    public function printDiff(ManuscriptDiff $diff, OutputInterface $output): void
    {
        $this->resultPrinter->printManuscriptDiff($diff, $output);
    }

    public function dumpManuscriptFiles(ManuscriptFiles $manuscriptFiles): void
    {
        $manuscriptFiles->dumpTo($this->bookProjectConfiguration->manuscriptTargetDir());

        $this->logger->info('Generated the manuscript files in {manuscriptTargetDir}', [
            'manuscriptTargetDir' => $this->bookProjectConfiguration->manuscriptTargetDir()
                ->pathname(),
        ]);
    }
}
