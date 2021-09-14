<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Checker;

use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Process\Result;

interface Checker
{
    public function name(): string;

    public function check(ExistingDirectory $directory): ?Result;
}
