<?php

declare(strict_types=1);

namespace BookTools\Test;

use BookTools\Markua\Parser\Attributes;
use BookTools\ResourceLoader\LoadedResource;
use BookTools\ResourcePreProcessor\CropResourcePreProcessor;
use PHPUnit\Framework\TestCase;

final class CropResourcePreProcessorTest extends TestCase
{
    private CropResourcePreProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new CropResourcePreProcessor();
    }

    public function testItRemovesThePartStartingWithCropEnd(): void
    {
        $result = $this->processor->process(
            $this->resourceWithContents("\$code;\n// crop-end\n// this will be removed\n"),
            $this->attributes()
        );

        self::assertSame("\$code;\n", $result->contents());
    }

    public function testItRemovesThePartEndingWithCropStart(): void
    {
        $result = $this->processor->process(
            $this->resourceWithContents("// this will be removed\n// crop-start\n\$code;\n"),
            $this->attributes()
        );

        self::assertSame("\$code;\n", $result->contents());
    }

    public function testItLeavesTheCodeAsIsIfThereAreNoMarkers(): void
    {
        $result = $this->processor->process($this->resourceWithContents("\$code\n"), $this->attributes());

        self::assertSame("\$code\n", $result->contents());
    }

    private function resourceWithContents(string $contents): LoadedResource
    {
        return new LoadedResource('php', $contents);
    }

    private function attributes(): Attributes
    {
        return new Attributes([]);
    }
}
