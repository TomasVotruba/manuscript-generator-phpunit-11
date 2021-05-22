<?php

declare(strict_types=1);

namespace BookTools\Test;

use BookTools\ResourceAttributes;
use BookTools\ResourcePreProcessor\RemoveSuperfluousIndentationResourcePreProcessor;
use PHPUnit\Framework\TestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveSuperfluousIndentationResourcePreProcessorTest extends TestCase
{
    private RemoveSuperfluousIndentationResourcePreProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new RemoveSuperfluousIndentationResourcePreProcessor();
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

        self::assertSame(
            $expected,
            $this->processor->process($code, $this->textBasedFileResource(), $this->attributes())
        );
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

        self::assertSame(
            $expected,
            $this->processor->process($code, $this->textBasedFileResource(), $this->attributes())
        );
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

        self::assertSame(
            $expected,
            $this->processor->process($code, $this->textBasedFileResource(), $this->attributes())
        );
    }

    public function testSkipIfNoSuperfluousIndentation(): void
    {
        $code = <<<'CODE_SAMPLE'
        public function test(): string
        {
            return 'test';
        }
        CODE_SAMPLE;

        self::assertSame($code, $this->processor->process($code, $this->textBasedFileResource(), $this->attributes()));
    }

    private function textBasedFileResource(): SmartFileInfo
    {
        return new SmartFileInfo(__FILE__);
    }

    private function attributes(): ResourceAttributes
    {
        return new ResourceAttributes([]);
    }
}
