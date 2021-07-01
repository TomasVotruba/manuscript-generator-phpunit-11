<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;
use function str_ends_with;

final class RectorOutputResourceLoader implements ResourceGenerator
{
    public function supportsResource(IncludedResource $resource): bool
    {
        return str_ends_with($resource->link, 'rector-output.diff');
    }

    public function sourcePathForResource(IncludedResource $resource): string
    {
        return dirname($resource->expectedFilePathname());
    }

    public function generateResource(IncludedResource $resource): string
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
        $process = new Process(['vendor/bin/rector', 'process', '--dry-run'], $workingDir);
        $result = $process->run();

        return $result->standardOutput();
    }
}
