<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Testing;

interface TestRunner
{
    /**
     * @throws TestFailed
     */
    public function run(): void;
}
