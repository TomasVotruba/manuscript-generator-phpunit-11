<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources\PHPUnit;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\DetermineLastModifiedTimestamp;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\ResourceGenerator;
use function str_ends_with;

final class PhpUnitResourceGenerator implements ResourceGenerator
{
    public function supportsResource(IncludedResource $resource): bool
    {
        return str_ends_with($resource->link, 'phpunit-output.txt');
    }

    public function sourcePathForResource(IncludedResource $resource): string
    {
        return dirname($resource->expectedFilePathname());
    }

    public function generateResource(IncludedResource $resource): string
    {
        return $this->getOutputOfPhpUnitRun($this->sourcePathForResource($resource));
    }

    public function sourceLastModified(
        IncludedResource $resource,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $determineLastModifiedTimestamp->ofDirectory($this->sourcePathForResource($resource));
    }

    private function getOutputOfPhpUnitRun(string $workingDir): string
    {
        $process = new Process(
            [
                'vendor/bin/phpunit',
                '--printer',
                'LeanBookTools\\PHPUnit\\CleanerResultPrinter',
                '--do-not-cache-result',
            ],
            $workingDir
        );
        $result = $process->run();

        return $result->standardAndErrorOutputCombined();
    }
}
