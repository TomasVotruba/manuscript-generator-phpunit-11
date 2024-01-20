<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli\Output;

use ManuscriptGenerator\Cli\Output\Formatter\ColorConsoleDiffFormatter;
use SebastianBergmann\Diff\Differ;

final readonly class ConsoleDiffer
{
    public function __construct(
        private Differ $differ,
        private ColorConsoleDiffFormatter $colorConsoleDiffFormatter,
    ) {
    }

    public function diff(string $old, string $new): string
    {
        $diff = $this->differ->diff($old, $new);
        return $this->colorConsoleDiffFormatter->format($diff);
    }
}
