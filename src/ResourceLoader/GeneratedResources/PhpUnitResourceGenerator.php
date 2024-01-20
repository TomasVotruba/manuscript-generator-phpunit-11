<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;

final readonly class PhpUnitResourceGenerator implements ResourceGenerator
{
    public function __construct(
        private DependenciesInstaller $dependenciesInstaller
    ) {
    }

    public function name(): string
    {
        return 'phpunit_output';
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        $workingDir = $resource->directoryOfExpectedFile()
            ->existing();
        $this->dependenciesInstaller->install($workingDir);

        $process = new Process(['vendor/bin/phpunit', '--do-not-cache-result'], $workingDir);
        $result = $process->run();

        return $result->standardAndErrorOutputCombined();
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
