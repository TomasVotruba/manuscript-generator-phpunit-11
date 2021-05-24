<?php

declare(strict_types=1);

namespace BookTools\Markua\Printer;

final class Result
{
    /**
     * @var array<string>
     */
    private array $blocks = [];

    public function asString(): string
    {
        return implode("\n\n", $this->blocks)
            //  we end the document with a newline
            . "\n";
    }

    public function addBlock(string $block): void
    {
        $this->blocks[] = $block;
    }

    public function appendToBlock(string $line): void
    {
        $this->blocks[count($this->blocks) - 1] .= $line;
    }

    public function appendLineToBlock(string $line): void
    {
        $this->appendToBlock($line . "\n");
    }

    public function startBlock(): void
    {
        $this->blocks[] = '';
    }
}
