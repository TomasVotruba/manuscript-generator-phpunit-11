<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader;

use BookTools\FileOperations\FileOperations;
use Symfony\Component\Process\Process;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorOutputResourceLoader implements ResourceLoader
{
    public function __construct(
        private FileOperations $fileOperations
    ) {
    }

    public function load(SmartFileInfo $includedFromFile, string $link): LoadedResource
    {
        if (! str_ends_with($link, 'rector-output.diff')) {
            throw CouldNotLoadFile::becauseResourceIsNotSupported();
        }

        // @TODO remove duplication: introduce a process output loader
        $expectedPath = $includedFromFile->getPath() . '/resources/' . $link;

        $outputOfPhpUnitRun = $this->getOutputOfRectorRun(dirname($expectedPath));

        $this->fileOperations->putContents($expectedPath, $outputOfPhpUnitRun);

        return LoadedResource::createFromPathAndContents($link, $outputOfPhpUnitRun);
    }

    private function getOutputOfRectorRun(string $workingDir): string
    {
        $process = new Process(
            [getcwd() . '/vendor/bin/rector', 'process', '--dry-run', '--config', $workingDir . '/rector.php']
        );
        $process->run();

        return $process->getOutput();
    }
}
