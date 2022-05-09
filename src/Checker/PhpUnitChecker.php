<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Checker;

use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Process\Process;
use ManuscriptGenerator\Process\Result;

final class PhpUnitChecker implements Checker
{
    public function name(): string
    {
        return 'PHPUnit';
    }

    public function check(ExistingDirectory $directory): ?Result
    {
        $configFileName = 'phpunit.ci.xml';

        if (! $directory->appendPath($configFileName)->file()->exists()) {
            return null;
        }

        $process = new Process([
            'vendor/bin/phpunit',
            '--configuration',
            $configFileName,
            '--bootstrap',
            'vendor/autoload.php',
            '--do-not-cache-result',
        ], $directory);

        return $process->run();
    }
}
