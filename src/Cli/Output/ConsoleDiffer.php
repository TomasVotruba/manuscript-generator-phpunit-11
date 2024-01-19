<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli\Output;

use ManuscriptGenerator\Cli\Output\Formatter\ColorConsoleDiffFormatter;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

final class ConsoleDiffer
{
    private Differ $differ;

    private ColorConsoleDiffFormatter $colorConsoleDiffFormatter;

    public function __construct()
    {
        $unifiedDiffOutputBuilder = $this->createUnifiedDiffOutputBuilder();
        $this->differ = new Differ($unifiedDiffOutputBuilder);

        $this->colorConsoleDiffFormatter = new ColorConsoleDiffFormatter();
    }

    public function diff(string $old, string $new): string
    {
        $diff = $this->differ->diff($old, $new);
        return $this->colorConsoleDiffFormatter->format($diff);
    }

    private function createUnifiedDiffOutputBuilder(): UnifiedDiffOutputBuilder
    {
        $unifiedDiffOutputBuilder = new UnifiedDiffOutputBuilder('');

        // set private property $contextLines value 10000 to see full diffs
        $contextLinesReflectionProperty = new \ReflectionProperty($unifiedDiffOutputBuilder, 'contextLines');
        $contextLinesReflectionProperty->setValue(10000);

        return $unifiedDiffOutputBuilder;
    }
}
