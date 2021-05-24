<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor;

use BookTools\HeadlineCapitalizer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CapitalizeHeadlinesProcessor implements MarkuaProcessor
{
    public function __construct(
        private HeadlineCapitalizer $headlineCapitalizer,
        private bool $capitalizeHeadlines
    ) {
    }

    public function process(SmartFileInfo $markuaFileInfo, string $markua): string
    {
        if (! $this->capitalizeHeadlines) {
            return $markua;
        }

        return $this->headlineCapitalizer->capitalizeHeadlines($markua);
    }
}
