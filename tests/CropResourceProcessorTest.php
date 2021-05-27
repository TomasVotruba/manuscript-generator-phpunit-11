<?php

declare(strict_types=1);

namespace BookTools\Test;

use BookTools\Markua\Parser\Node\AttributeList;
use BookTools\ResourceLoader\LoadedResource;
use BookTools\ResourceProcessor\CropResourceProcessor;
use PHPUnit\Framework\TestCase;

final class CropResourceProcessorTest extends TestCase
{
    private CropResourceProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new CropResourceProcessor();
    }

    public function testItRemovesThePartStartingWithCropEnd(): void
    {
        $resource = $this->resourceWithContents("\$code;\n// crop-end\n// this will be removed\n");
        $this->processor->process($resource);

        self::assertSame("\$code;\n", $resource->contents());
    }

    public function testItRemovesThePartEndingWithCropStart(): void
    {
        $resource = $this->resourceWithContents("// this will be removed\n// crop-start\n\$code;\n");

        $this->processor->process($resource);

        self::assertSame("\$code;\n", $resource->contents());
    }

    public function testItLeavesTheCodeAsIsIfThereAreNoMarkers(): void
    {
        $resource = $this->resourceWithContents("\$code\n");

        $this->processor->process($resource);

        self::assertSame("\$code\n", $resource->contents());
    }

    private function resourceWithContents(string $contents): LoadedResource
    {
        return new LoadedResource('php', $contents, new AttributeList([]));
    }
}
