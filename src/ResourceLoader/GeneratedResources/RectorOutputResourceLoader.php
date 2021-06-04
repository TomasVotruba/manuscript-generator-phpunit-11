<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use function str_ends_with;
use Symfony\Component\Process\Process;

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

    private function getOutputOfRectorRun(string $workingDir): string
    {
        $process = new Process([getcwd() . '/vendor/bin/rector', 'process', '--dry-run'], $workingDir);
        $process->run();

        return $process->getOutput();
    }
}
