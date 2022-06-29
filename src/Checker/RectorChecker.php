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
        $rectorConfigFile = 'rector.ci.php';
        $configFile = $directory->appendPath($rectorConfigFile)
            ->file();
        if (! $configFile->exists()) {
            return null;
        }

        $process = new Process([
            'vendor/bin/rector',
            'process',
            '--config',
            $rectorConfigFile,
            '--dry-run',
        ], $directory);

        return $process->run();
    }
}
