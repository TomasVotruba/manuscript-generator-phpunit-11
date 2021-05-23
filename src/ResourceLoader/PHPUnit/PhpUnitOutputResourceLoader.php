<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader\PHPUnit;

use BookTools\FileOperations\FileOperations;
use BookTools\ResourceLoader\CouldNotLoadFile;
use BookTools\ResourceLoader\ResourceLoader;
use function str_ends_with;
use Symfony\Component\Process\Process;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PhpUnitOutputResourceLoader implements ResourceLoader
{
    public function __construct(
        private FileOperations $fileOperations
    ) {
    }

    public function load(SmartFileInfo $includedFromFile, string $link): SmartFileInfo
    {
        if (! str_ends_with($link, 'phpunit-output.txt')) {
            throw CouldNotLoadFile::becauseResourceIsNotSupported();
        }

        $expectedPath = $includedFromFile->getPath() . '/resources/' . $link;

        $this->fileOperations->putContents($expectedPath, $this->getOutputOfPhpUnitRun(dirname($expectedPath)));

        return new SmartFileInfo($expectedPath);
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
