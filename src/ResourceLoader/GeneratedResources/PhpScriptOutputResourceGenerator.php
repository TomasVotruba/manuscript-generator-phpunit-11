<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use Symfony\Component\Process\Process;

final class PhpScriptOutputResourceGenerator implements ResourceGenerator
{
    public const PHP_SCRIPT_OUTPUT_TXT = '.php_script_output.txt';

    public function supportsResource(IncludedResource $resource): bool
    {
        return str_ends_with($resource->link, self::PHP_SCRIPT_OUTPUT_TXT);
    }

    public function sourcePathForResource(IncludedResource $resource): string
    {
        return str_replace(self::PHP_SCRIPT_OUTPUT_TXT, '.php', $resource->expectedFilePathname());
    }

    public function generateResource(IncludedResource $resource): string
    {
        $workingDir = dirname($resource->expectedFilePathname());

        $process = new Process(['php', $this->sourcePathForResource($resource)], $workingDir);
        $process->run();

        $output = $process->getOutput();

        // Maybe generalize this: strip working dir from file paths in output
        return str_replace($workingDir . '/', '', $output);
    }
}
