<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;

final readonly class PhpStanOutputResourceGenerator implements ResourceGenerator
{
    public function __construct(
        private DependenciesInstaller $dependenciesInstaller
    ) {
    }

    public function name(): string
    {
        return 'phpstan_output';
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        $workingDir = $resource->directoryOfExpectedFile()
            ->existing();
        $this->dependenciesInstaller->install($workingDir);

        $command = ['vendor/bin/phpstan', 'analyze'];

        if ($resource->attributes->has('debug')) {
            $command[] = '--debug';
        }

        $process = new Process($command, $workingDir);
        $result = $process->run();

        return $result->standardOutput();
    }

    public function sourceLastModified(
        IncludedResource $resource,
        Source $source,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $determineLastModifiedTimestamp->ofDirectory(
            $resource->directoryOfExpectedFile()
                ->existing()
                ->pathname()
        );
    }
}
