<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader\GeneratedResources\PHPUnit;

use BookTools\Markua\Parser\Node\IncludedResource;
use BookTools\ResourceLoader\GeneratedResources\ResourceGenerator;
use function str_ends_with;
use Symfony\Component\Process\Process;

final class PhpUnitResourceGenerator implements ResourceGenerator
{
    public function supportsResource(IncludedResource $resource): bool
    {
        return str_ends_with($resource->link, 'phpunit-output.txt');
    }

    public function generateResource(IncludedResource $resource): string
    {
        return $this->getOutputOfPhpUnitRun(dirname($resource->expectedFilePathname()));
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
