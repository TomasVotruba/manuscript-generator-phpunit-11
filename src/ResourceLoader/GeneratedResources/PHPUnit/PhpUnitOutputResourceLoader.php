<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader\GeneratedResources\PHPUnit;

use BookTools\FileOperations\FileOperations;
use BookTools\Markua\Parser\Node\IncludedResource;
use BookTools\ResourceLoader\CouldNotLoadFile;
use BookTools\ResourceLoader\LoadedResource;
use BookTools\ResourceLoader\ResourceLoader;
use function str_ends_with;
use Symfony\Component\Process\Process;

final class PhpUnitOutputResourceLoader implements ResourceLoader
{
    public function __construct(
        private FileOperations $fileOperations
    ) {
    }

    public function load(IncludedResource $includedResource): LoadedResource
    {
        if (! str_ends_with($includedResource->link, 'phpunit-output.txt')) {
            throw CouldNotLoadFile::becauseResourceIsNotSupported();
        }

        $expectedPath = $includedResource->expectedFilePathname();

        $outputOfPhpUnitRun = $this->getOutputOfPhpUnitRun(dirname($expectedPath));

        $this->fileOperations->putContents($expectedPath, $outputOfPhpUnitRun);

        return LoadedResource::createFromIncludedResource($includedResource, $outputOfPhpUnitRun);
    }

    private function getOutputOfPhpUnitRun(string $workingDir): string
    {
        $process = new Process(
            [getcwd() . '/vendor/bin/phpunit', '--printer', CleanerResultPrinter::class, '--do-not-cache-result'],
            $workingDir
        );
        $process->run();

        return $process->getOutput();
    }
}
