<?php
declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use ManuscriptGenerator\Process\Result;

interface ProjectCheckResultsPrinter
{
    /**
     * @param array<Result> $allResults
     */
    public function finish(array $allResults): void;
}
