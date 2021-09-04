<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;

final class PhpUnitResourceGenerator implements CacheableResourceGenerator
{
    public function __construct(
        private DependenciesInstaller $dependenciesInstaller
    ) {
    }

    public function name(): string
    {
        return 'phpunit-output';
    }

    public function sourcePathForResource(IncludedResource $resource): string
    {
        return dirname($resource->expectedFilePathname());
    }

    public function generateResource(IncludedResource $resource, Source $source): string
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
        $this->dependenciesInstaller->install($workingDir);

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
