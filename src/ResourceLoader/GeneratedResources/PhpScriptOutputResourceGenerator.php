<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;

final class PhpScriptOutputResourceGenerator implements ResourceGenerator
{
    public function __construct(
        private DependenciesInstaller $dependenciesInstaller
    ) {
    }

    public function name(): string
    {
        return 'php_script_output';
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        $this->dependenciesInstaller->install($source->existingFile()->containingDirectory());

        $process = new Process([
            'php',
            $source->existingFile()->basename(),
        ], $source->existingFile()->containingDirectory());

        $result = $process->run();

        return $result->standardAndErrorOutputCombined();
    }

    public function sourceLastModified(
        IncludedResource $resource,
        Source $source,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $determineLastModifiedTimestamp->ofDirectory($source->existingFile()->containingDirectory()->pathname());
    }
}
