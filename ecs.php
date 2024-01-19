<?php

declare(strict_types=1);

use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withRootFiles()
    ->withPreparedSets(psr12: true, common: true)
    ->withConfiguredRule(LineLengthFixer::class, [
        // to keep the code snippets visible on the book page without line-breaking
        LineLengthFixer::LINE_LENGTH => 120,
    ])
    ->withSkip([
        // fixture files
        '*/tests/EndToEnd/*/*',
    ]);
