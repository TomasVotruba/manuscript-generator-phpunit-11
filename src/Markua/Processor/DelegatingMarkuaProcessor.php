<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor;

use Symplify\SmartFileSystem\SmartFileInfo;

final class DelegatingMarkuaProcessor implements MarkuaProcessor
{
    /**
     * @param array<MarkuaProcessor> $markuaProcessors
     */
    public function __construct(
        private array $markuaProcessors
    ) {
    }

    public function process(MarkuaProcessor $markuaProcessor, SmartFileInfo $markuaFileInfo, string $markua): string
    {
        $processed = $markua;

        foreach ($this->markuaProcessors as $markuaProcessor) {
            $processed = $markuaProcessor->process($markuaProcessor, $markuaFileInfo, $processed);
        }

        return $processed;
    }
}
