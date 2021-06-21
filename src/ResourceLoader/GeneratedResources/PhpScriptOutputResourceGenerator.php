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

        $scriptFileName = $scriptFile->getBasename();
        $workingDir = realpath($scriptFile->getPath());
        assert(is_string($workingDir));

        $process = new Process(['php', $scriptFileName], $workingDir);
        $result = $process->run();

        return $result->standardAndErrorOutputCombined();
    }
}
