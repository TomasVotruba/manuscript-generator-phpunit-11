<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Configuration;

use LogicException;

final readonly class TitlePageConfiguration
{
    private function __construct(
        private bool $includeInManuscript,
        private bool $generate,
        private ?string $generatorName,
        private ?string $sourceFile
    ) {
    }

    public static function uploadOnLeanpub(): self
    {
        return new self(false, false, null, null);
    }

    public static function includeInManuscript(): self
    {
        return new self(true, false, null, null);
    }

    public static function generate(string $generatorName = 'title_page', string $sourceFile = 'title_page.xcf'): self
    {
        return new self(true, true, $generatorName, $sourceFile);
    }

    public function shouldTitlePageBeIncludedInManuscript(): bool
    {
        return $this->includeInManuscript;
    }

    public function shouldTitlePageBeGenerated(): bool
    {
        return $this->generate;
    }

    public function generatorName(): string
    {
        if ($this->generatorName === null) {
            throw new LogicException();
        }

        return $this->generatorName;
    }

    public function sourceFile(): string
    {
        if ($this->sourceFile === null) {
            throw new LogicException();
        }

        return $this->sourceFile;
    }
}
