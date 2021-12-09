<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Checker;

use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Process\Process;
use ManuscriptGenerator\Process\Result;

final class RectorChecker implements Checker
{
    public function name(): string
    {
        return 'Rector (dry-run)';
    }

    public function check(ExistingDirectory $directory): ?Result
    {
        $configFile = $directory->appendPath('rector.ci.php')
            ->file();
        if (! $configFile->exists()) {
            return null;
        }

        $process = new Process([
            'vendor/bin/rector',
            'process',
            '--config',
            $configFile->pathname(),
            '--autoload-file',
            'vendor/autoload.php',
            '--working-dir',
            $directory->pathname(),
            '--dry-run',
        ], ExistingDirectory::currentWorkingDirectory());

        return $process->run();
    }
}
