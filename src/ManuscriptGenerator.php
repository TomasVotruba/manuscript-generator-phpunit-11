<?php

declare(strict_types=1);

namespace ManuscriptGenerator;

use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\FileOperations\FileOperations;
use ManuscriptGenerator\Markua\Processor\MarkuaProcessor;
use ManuscriptGenerator\Testing\TestRunner;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ManuscriptGenerator
{
    public function __construct(
        private RuntimeConfiguration $configuration,
        private DependenciesInstaller $dependenciesInstaller,
        private TestRunner $testRunner,
        private FileOperations $fileOperations,
        private MarkuaProcessor $markuaProcessor,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger
    ) {
    }

    public function generateManuscript(): void
    {
        if ($this->configuration->updateDependencies()) {
            $this->logger->info('Updating dependencies');
            $this->dependenciesInstaller->update();
        } else {
            $this->logger->info('Installing dependencies');
            $this->dependenciesInstaller->install();
        }

        $this->testRunner->run();

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

            $processedContents = $this->markuaProcessor->process($srcFilePath, $srcFilePath->contents());

            $targetFilePathname = $this->configuration->manuscriptTargetDir() . '/' . $srcFileName;
            $this->fileOperations->putContents($targetFilePathname, $processedContents);

            $targetTxtFilePathname = $this->configuration->manuscriptTargetDir() . '/' . $targetFileName;
            $txtFileContents = $srcFileName . "\n";
            $this->fileOperations->putContents($targetTxtFilePathname, $txtFileContents);
        }

        $this->logger->info('Generated the manuscript files in {manuscriptTargetDir}', [
            'manuscriptTargetDir' => $this->configuration->manuscriptTargetDir(),
        ]);
        $this->eventDispatcher->dispatch(new ManuscriptWasGenerated($this->configuration->manuscriptTargetDir()));
    }
}
