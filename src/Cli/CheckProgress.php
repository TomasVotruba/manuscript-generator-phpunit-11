<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use ManuscriptGenerator\FileOperations\ExistingDirectory;

interface CheckProgress
{
    public function startChecking(ExistingDirectory $directory): void;

    public function finish(): void;
}
