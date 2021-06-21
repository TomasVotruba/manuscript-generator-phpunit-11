<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Dependencies;

use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\Process\Process;
use RuntimeException;
use Symfony\Component\Finder\Finder;

final class ComposerDependenciesInstaller implements DependenciesInstaller
{
    public function __construct(
        private RuntimeConfiguration $configuration
    ) {
    }

    public function install(): void
    {
        $command = 'install';

        $this->runComposer($command);
    }

    public function update(): void
    {
        $this->runComposer('update');
    }

    private function runComposer(string $command): void
    {
        $composerJsonFileFinder = Finder::create()
            ->files()
            ->in($this->configuration->manuscriptSrcDir())
            ->notPath('vendor') // don't try to install dependencies for vendor packages!
            ->name('composer.json');

        foreach ($composerJsonFileFinder as $composerJsonFile) {
            $workingDir = $composerJsonFile->getPath();
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
}
