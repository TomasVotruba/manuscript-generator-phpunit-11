<?php

declare(strict_types=1);

namespace BookTools;

final class Configuration
{
    private string $manuscriptSrcDir;

    private string $manuscriptTargetDir;

    private bool $capitalizeHeadlines;

    public function __construct(
        string $manuscriptSrcDir,
        string $manuscriptTargetDir,
        bool $capitalizeHeadlines = false
    ) {
        $this->manuscriptSrcDir = $manuscriptSrcDir;
        $this->manuscriptTargetDir = $manuscriptTargetDir;
        $this->capitalizeHeadlines = $capitalizeHeadlines;
    }

    public function manuscriptSrcDir(): string
    {
        return $this->manuscriptSrcDir;
    }

    public function manuscriptTargetDir(): string
    {
        return $this->manuscriptTargetDir;
    }

    public function capitalizeHeadlines(): bool
    {
        return $this->capitalizeHeadlines;
    }
}
