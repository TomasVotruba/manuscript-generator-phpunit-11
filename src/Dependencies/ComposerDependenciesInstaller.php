<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Dependencies;

use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\Process\Process;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

final class ComposerDependenciesInstaller implements DependenciesInstaller
{
    public const INSTALL_COMMAND = 'install';

    public const UPDATE_COMMAND = 'update';

    public function __construct(
        private RuntimeConfiguration $configuration,
        private LoggerInterface $logger
    ) {
    }

    public function install(): void
    {
        $command = self::INSTALL_COMMAND;

        $this->runComposerForAllSubProjects($command);
    }

    public function update(): void
    {
        $this->runComposerForAllSubProjects(self::UPDATE_COMMAND);
    }

    private function runComposerForAllSubProjects(string $preferredCommand): void
    {
        $composerJsonFileFinder = Finder::create()
            ->files()
            ->in($this->configuration->manuscriptSrcDir())
            ->notPath('vendor') // don't try to install dependencies for vendor packages!
            ->name('composer.json');

        foreach ($composerJsonFileFinder as $composerJsonFile) {
            $composerLockFile = new SplFileInfo($composerJsonFile->getPath() . '/composer.lock');
            $vendorDir = new SplFileInfo($composerJsonFile->getPath() . '/vendor');

            if ($preferredCommand === self::INSTALL_COMMAND
                && $composerLockFile->isFile()
                && $composerLockFile->getMTime() < $composerJsonFile->getMTime()) {
                $this->logger->debug($composerJsonFile->getPathname() . ' has been modified');
                $this->runComposer($composerJsonFile, self::UPDATE_COMMAND);
            } elseif ($preferredCommand === self::INSTALL_COMMAND
                && $composerLockFile->isFile()
                && $vendorDir->isDir()
                && $vendorDir->getMTime() >= $composerLockFile->getMTime()) {
                $this->logger->info($composerLockFile->getPathname() . ' has not been modified, skipping install');
            } else {
                $this->runComposer($composerJsonFile, $preferredCommand);
            }
        }
    }

    private function runComposer(SplFileInfo $composerJsonFile, string $command): void
    {
        $workingDir = $composerJsonFile->getPath();
        $this->logger->info('Running composer ' . $command . ' in ' . $workingDir);

        $composer = new Process(
            [
                'composer', // @TODO make configurable
                $command,
            ],
            $workingDir
        );
        $result = $composer->run();

        if (! $result->isSuccessful()) {
            throw new RuntimeException('Composer failed: ' . $result->standardAndErrorOutputCombined());
        }
    }
}
