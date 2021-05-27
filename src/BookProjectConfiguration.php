<?php

declare(strict_types=1);

namespace BookTools;

final class BookProjectConfiguration
{
    public function __construct(
        private string $manuscriptSrcDir = 'manuscript-src',
        private string $manuscriptTargetDir = 'manuscript',
        private bool $capitalizeHeadlines = false
    ) {
    }

    public static function usingDefaults(): self
    {
        return new self();
    }

    public function manuscriptSrcDir(): string
    {
        return $this->manuscriptSrcDir;
    }

    public function setManuscriptSrcDir(string $manuscriptSrcDir): void
    {
        $this->manuscriptSrcDir = $manuscriptSrcDir;
    }

    public function setManuscriptTargetDir(string $manuscriptTargetDir): void
    {
        $this->manuscriptTargetDir = $manuscriptTargetDir;
    }

    public function manuscriptTargetDir(): string
    {
        return $this->manuscriptTargetDir;
    }

    public function capitalizeHeadlines(): bool
    {
        return $this->capitalizeHeadlines;
    }

    public function setCapitalizeHeadings(bool $capitalizeHeadlines): void
    {
        $this->capitalizeHeadlines = $capitalizeHeadlines;
    }
}
