<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;

final class PhpUnitResourceGenerator implements ResourceGenerator
{
    public function __construct(
        private DependenciesInstaller $dependenciesInstaller
    ) {
    }

    public function name(): string
    {
        return 'phpunit-output';
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        $this->dependenciesInstaller->install($source->directory());

        $process = new Process(
            [
                'vendor/bin/phpunit',
                '--printer',
                'LeanBookTools\\PHPUnit\\CleanerResultPrinter',
                '--do-not-cache-result',
            ],
            $source->directory()
        );
        $result = $process->run();

        return $result->standardAndErrorOutputCombined();
    }

    public function sourceLastModified(
        IncludedResource $resource,
        Source $source,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $determineLastModifiedTimestamp->ofDirectory($source->directory()->toString());
    }
}
