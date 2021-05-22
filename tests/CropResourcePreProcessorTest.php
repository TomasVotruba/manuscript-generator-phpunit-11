<?php

declare(strict_types=1);

namespace BookTools\Test;

use BookTools\ResourcePreProcessor\CropResourcePreProcessor;
use PHPUnit\Framework\TestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

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
            $this->phpFileResource()
        );

        self::assertSame("\$code;\n", $result);
    }

    public function testItRemovesThePartEndingWithCropStart(): void
    {
        $result = $this->processor->process(
            "// this will be removed\n// crop-start\n\$code;\n",
            $this->phpFileResource()
        );

        self::assertSame("\$code;\n", $result);
    }

    public function testItLeavesTheCodeAsIsIfThereAreNoMarkers(): void
    {
        $result = $this->processor->process("\$code\n", $this->phpFileResource());

        self::assertSame("\$code\n", $result);
    }

    private function phpFileResource(): SmartFileInfo
    {
        return new SmartFileInfo(__FILE__);
    }
}
