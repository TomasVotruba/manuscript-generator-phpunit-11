<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Checker;

use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Process\Process;
use ManuscriptGenerator\Process\Result;

final class PhpStanChecker implements Checker
{
    public function name(): string
    {
        return 'PHPStan';
    }

    public function check(ExistingDirectory $directory): ?Result
    {
        $configFile = $directory->appendPath('phpstan.neon')
            ->file();
        if (! $configFile->exists()) {
            return null;
        }

        $process = new Process([
            'vendor/bin/phpstan',
            'analyze',
            '-c',
            $configFile->pathname(),
            '-a',
            $directory->appendPath('vendor/autoload.php')
                ->pathname(),
        ], ExistingDirectory::currentWorkingDirectory());

        return $process->run();
    }
}
