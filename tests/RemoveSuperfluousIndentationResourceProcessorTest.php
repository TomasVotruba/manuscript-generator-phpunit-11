<?php

declare(strict_types=1);

namespace BookTools\Test;

use BookTools\Markua\Parser\Node\AttributeList;
use BookTools\ResourceLoader\LoadedResource;
use BookTools\ResourceProcessor\RemoveSuperfluousIndentationResourceProcessor;
use PHPUnit\Framework\TestCase;

final class RemoveSuperfluousIndentationResourceProcessorTest extends TestCase
{
    private RemoveSuperfluousIndentationResourceProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new RemoveSuperfluousIndentationResourceProcessor();
    }

    public function testItRemovesSuperfluousIndentationOfPhpResources(): void
    {
        $code = <<<'CODE_SAMPLE'
            public function test(): string
            {
                return 'test';
            }
        CODE_SAMPLE;

        $expected = <<<'CODE_SAMPLE'
        public function test(): string
        {
            return 'test';
        }
        CODE_SAMPLE;

        $resource = $this->resourceWithContents($code);
        $this->processor->process($resource);

        self::assertSame($expected, $resource->contents());
    }

    public function testItTrimsTheContentsBeforeRemovingSuperfluousIndentation(): void
    {
        $code = <<<'CODE_SAMPLE'

            public function test(): string
            {
                return 'test';
            }

        CODE_SAMPLE;

        $expected = <<<'CODE_SAMPLE'
        public function test(): string
        {
            return 'test';
        }
        CODE_SAMPLE;

        $resource = $this->resourceWithContents($code);
        $this->processor->process($resource);

        self::assertSame($expected, $resource->contents());
    }

    public function testItIgnoresLinesWithNoIndentation(): void
    {
        $code = <<<'CODE_SAMPLE'
            public function test(): string
            {
                // empty line:

                return 'test';
            }
        CODE_SAMPLE;

        $expected = <<<'CODE_SAMPLE'
        public function test(): string
        {
            // empty line:

            return 'test';
        }
        CODE_SAMPLE;

        $resource = $this->resourceWithContents($code);
        $this->processor->process($resource);

        self::assertSame($expected, $resource->contents());
    }

    public function testSkipIfNoSuperfluousIndentation(): void
    {
        $code = <<<'CODE_SAMPLE'
        public function test(): string
        {
            return 'test';
        }
        CODE_SAMPLE;

        $resource = $this->resourceWithContents($code);
        $this->processor->process($resource);

        self::assertSame($code, $resource->contents());
    }

    private function resourceWithContents(string $contents): LoadedResource
    {
        return new LoadedResource('php', $contents, new AttributeList([]));
    }
}
