<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;

final class RectorOutputResourceLoader implements CacheableResourceGenerator
{
    public function __construct(
        private DependenciesInstaller $dependenciesInstaller
    ) {
    }

    public function name(): string
    {
        return 'rector_output';
    }

    public function sourcePathForResource(IncludedResource $resource): string
    {
        return dirname($resource->expectedFilePathname());
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        return $this->getOutputOfRectorRun($this->sourcePathForResource($resource));
    }

    public function sourceLastModified(
        IncludedResource $resource,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $determineLastModifiedTimestamp->ofDirectory($this->sourcePathForResource($resource));
    }

    private function getOutputOfRectorRun(string $workingDir): string
    {
        $this->dependenciesInstaller->install($workingDir);

        $process = new Process(['vendor/bin/rector', 'process', '--dry-run'], $workingDir);
        $result = $process->run();

        return $result->standardOutput();
    }
}
