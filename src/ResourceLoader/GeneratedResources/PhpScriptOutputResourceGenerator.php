<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Process\Process;
use SplFileInfo;

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
        $scriptFile = new SplFileInfo($this->sourcePathForResource($resource));

        $process = new Process(['php', $scriptFile->getPathname()]);
        $result = $process->run();

        // Maybe generalize this: strip working dir from file paths in output
        $realPath = $scriptFile->getRealPath();
        assert(is_string($realPath));

        return str_replace(dirname($realPath) . '/', '', $result->standardAndErrorOutputCombined());
    }
}
