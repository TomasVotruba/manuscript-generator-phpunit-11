<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader\GeneratedResources;

use BookTools\FileOperations\FileOperations;
use BookTools\Markua\Parser\Node\IncludedResource;
use BookTools\ResourceLoader\CouldNotLoadFile;
use BookTools\ResourceLoader\LoadedResource;
use BookTools\ResourceLoader\ResourceLoader;
use function str_ends_with;
use Symfony\Component\Process\Process;

final class RectorOutputResourceLoader implements ResourceLoader
{
    public function __construct(
        private FileOperations $fileOperations
    ) {
    }

    public function load(IncludedResource $includedResource): LoadedResource
    {
        if (! str_ends_with($includedResource->link, 'rector-output.diff')) {
            throw CouldNotLoadFile::becauseResourceIsNotSupported();
        }

        $expectedPath = $includedResource->expectedFilePathname();

        $outputOfPhpUnitRun = $this->getOutputOfRectorRun(dirname($expectedPath));

        $this->fileOperations->putContents($expectedPath, $outputOfPhpUnitRun);

        return LoadedResource::createFromIncludedResource($includedResource, $outputOfPhpUnitRun);
    }

    private function getOutputOfRectorRun(string $workingDir): string
    {
        $process = new Process([getcwd() . '/vendor/bin/rector', 'process', '--dry-run'], $workingDir);
        $process->run();

        return $process->getOutput();
    }
}
