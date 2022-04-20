<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli\Check;

use ManuscriptGenerator\Process\Result;

interface ProjectCheckResultsPrinter
{
    /**
     * @param array<Result> $allResults
     */
    public function finish(array $allResults): void;

    public function setNumberOfDirectories(int $number): void;

    public function advance(int $numberOfDirs): void;
}
