<?php

declare(strict_types=1);

namespace BookTools;

final class Configuration
{
    private string $manuscriptSrcDir;

    private string $manuscriptTargetDir;

    public function __construct(string $manuscriptSrcDir, string $manuscriptTargetDir)
    {
        $this->manuscriptSrcDir = $manuscriptSrcDir;
        $this->manuscriptTargetDir = $manuscriptTargetDir;
    }

    public function manuscriptSrcDir(): string
    {
        return $this->manuscriptSrcDir;
    }

    public function manuscriptTargetDir(): string
    {
        return $this->manuscriptTargetDir;
    }
}
