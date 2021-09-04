<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use Assert\Assertion;
use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;

final class PhpScriptOutputResourceGenerator implements CacheableResourceGenerator
{
    public function __construct(
        private DependenciesInstaller $dependenciesInstaller
    ) {
    }

    public function name(): string
    {
        return 'php_script_output';
    }

    public function sourcePathForResource(IncludedResource $resource): ExistingFile
    {
        // @TODO add getter for required attribute
        $script = $resource->attributes->get('script');
        Assertion::string($script);

        return ExistingFile::fromPathname($resource->includedFromFile()->directory() . '/' . $script);
    }

    public function generateResource(IncludedResource $resource): string
    {
        $scriptFile = $this->sourcePathForResource($resource);

        $this->dependenciesInstaller->install($scriptFile->directory());

        $process = new Process(['php', $scriptFile->basename()], $scriptFile->directory());

        $result = $process->run();

        $resource->attributes->remove('script');

        return $result->standardAndErrorOutputCombined();
    }

    public function sourceLastModified(
        IncludedResource $resource,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $determineLastModifiedTimestamp->ofDirectory($this->sourcePathForResource($resource)->directory());
    }
}
