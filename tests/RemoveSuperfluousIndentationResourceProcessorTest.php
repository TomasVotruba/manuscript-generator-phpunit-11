<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test;

use ManuscriptGenerator\Markua\Parser\Node\AttributeList;
use ManuscriptGenerator\ResourceLoader\LoadedResource;
use ManuscriptGenerator\ResourceProcessor\RemoveSuperfluousIndentationResourceProcessor;
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
