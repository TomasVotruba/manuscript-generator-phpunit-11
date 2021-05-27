<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader\GeneratedResources;

use BookTools\Markua\Parser\Node\IncludedResource;
use function str_ends_with;
use Symfony\Component\Process\Process;

final class RectorOutputResourceLoader implements ResourceGenerator
{
    public function supportsResource(IncludedResource $resource): bool
    {
        return str_ends_with($resource->link, 'rector-output.diff');
    }

    public function generateResource(IncludedResource $resource): string
    {
        return $this->getOutputOfRectorRun(dirname($resource->expectedFilePathname()));
    }

    private function getOutputOfRectorRun(string $workingDir): string
    {
        $process = new Process([getcwd() . '/vendor/bin/rector', 'process', '--dry-run'], $workingDir);
        $process->run();

        return $process->getOutput();
    }
}
