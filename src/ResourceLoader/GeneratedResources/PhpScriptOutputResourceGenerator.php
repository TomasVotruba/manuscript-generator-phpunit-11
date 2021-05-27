<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader\GeneratedResources;

use BookTools\Markua\Parser\Node\IncludedResource;
use Symfony\Component\Process\Process;

final class PhpScriptOutputResourceGenerator implements ResourceGenerator
{
    public const PHP_SCRIPT_OUTPUT_TXT = '.php_script_output.txt';

    public function supportsResource(IncludedResource $resource): bool
    {
        return str_ends_with($resource->link, self::PHP_SCRIPT_OUTPUT_TXT);
    }

    public function generateResource(IncludedResource $resource): string
    {
        $scriptPath = str_replace(self::PHP_SCRIPT_OUTPUT_TXT, '.php', $resource->expectedFilePathname());
        $process = new Process(['php', '-dauto_prepend_file=vendor/autoload.php', $scriptPath]);
        $process->run();

        return $process->getOutput();
    }
}
