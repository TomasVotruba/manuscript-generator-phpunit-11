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

    public function install(string $directory): void
    {
        $command = self::INSTALL_COMMAND;

        $this->runComposerInDirectory($directory, $command);
    }

    public function updateAll(): void
    {
        $composerJsonFileFinder = Finder::create()
            ->files()
            ->in($this->configuration->manuscriptSrcDir())
            ->notPath('vendor') // don't try to install dependencies for vendor packages!
            ->name('composer.json');

        foreach ($composerJsonFileFinder as $composerJsonFile) {
            $this->runComposerInDirectory($composerJsonFile->getPath(), self::UPDATE_COMMAND);
        }
    }

    public function dependenciesHaveChangedSince(int $timestamp, string $directory): bool
    {
        $composerJsonFile = new SplFileInfo($directory . '/composer.json');
        $composerLockFile = new SplFileInfo($directory . '/composer.lock');
        $vendorDir = new SplFileInfo($directory . '/vendor');

        if (! $composerJsonFile->isFile()) {
            // This directory doesn't have a composer.json file so the installer doesn't need to do anything
            return false;
        }

        if (! $composerLockFile->isFile()) {
            // composer.lock doesn't exist, so we definitely have to install dependencies
            return true;
        }

        if ($composerLockFile->getMTime() > $timestamp) {
            // composer.lock has changed since the provided timestamp so we have to re-install dependencies
            return true;
        }

        if (! $vendorDir->isDir()) {
            // dependencies have not even been installed
            return true;
        }

        if ($composerJsonFile->getMTime() > $composerLockFile->getMTime()) {
            // composer.json has been modified so we need to re-install dependencies
            return true;
        }

        if ($composerLockFile->getMTime() > $vendorDir->getMTime()) {
            // composer.lock has changed since the latest install, so we have to re-install dependencies
            return true;
        }

        return false;
    }

    private function runComposerInDirectory(string $directory, string $preferredCommand): void
    {
        $composerJsonFile = new SplFileInfo($directory . '/composer.json');
        if (! $composerJsonFile->isFile()) {
            // Nothing to install
            return;
        }

        $composerLockFile = new SplFileInfo($directory . '/composer.lock');
        $vendorDir = new SplFileInfo($directory . '/vendor');

        if ($preferredCommand === self::INSTALL_COMMAND
            && $composerLockFile->isFile()
            && $composerLockFile->getMTime() < $composerJsonFile->getMTime()) {
            $this->logger->debug($composerJsonFile->getPathname() . ' has been modified');
            $this->runComposer($composerJsonFile, self::UPDATE_COMMAND);
        } elseif ($preferredCommand === self::INSTALL_COMMAND
            && $composerLockFile->isFile()
            && $vendorDir->isDir()
            && $vendorDir->getMTime() >= $composerLockFile->getMTime()) {
            $this->logger->debug($composerLockFile->getPathname() . ' has not been modified, skipping install');
        } else {
            $this->runComposer($composerJsonFile, $preferredCommand);
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

        /*
         * composer.lock is touched seconds later than the latest change to composer.lock so if we want to use the mtime
         * of the lock file and compare it to the vendor/ dir to determine if we need to re-install the dependencies, we
         * have to touch vendor/ once more.
         */
        touch($composerJsonFile->getPath() . '/vendor');

        if (! $result->isSuccessful()) {
            throw new RuntimeException('Composer failed: ' . $result->standardAndErrorOutputCombined());
        }
    }
}
