<?php

declare(strict_types=1);

namespace BookTools\Test;

use BookTools\ResourceAttributes;
use BookTools\ResourceLoader\IncludedResource;
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
            "\$code;\n// crop-end\n// this will be removed\n",
            $this->phpResource(),
            $this->attributes()
        );

        self::assertSame("\$code;\n", $result);
    }

    public function testItRemovesThePartEndingWithCropStart(): void
    {
        $result = $this->processor->process(
            "// this will be removed\n// crop-start\n\$code;\n",
            $this->phpResource(),
            $this->attributes()
        );

        self::assertSame("\$code;\n", $result);
    }

    public function testItLeavesTheCodeAsIsIfThereAreNoMarkers(): void
    {
        $result = $this->processor->process("\$code\n", $this->phpResource(), $this->attributes());

        self::assertSame("\$code\n", $result);
    }

    private function phpResource(): IncludedResource
    {
        return new IncludedResource('php', '');
    }

    private function attributes(): ResourceAttributes
    {
        return new ResourceAttributes([]);
    }
}
