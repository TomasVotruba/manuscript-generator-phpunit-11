<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test;

use Iterator;
use ManuscriptGenerator\Markua\Processor\Headlines\HeadlineCapitalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HeadlineCapitalizerTest extends TestCase
{
    private HeadlineCapitalizer $headlineCapitalizer;

    protected function setUp(): void
    {
        $this->headlineCapitalizer = new HeadlineCapitalizer();
    }

    #[DataProvider('provideData')]
    public function test(string $inputContent, string $expectedCapitalizedContent): void
    {
        $this->assertSame(
            $expectedCapitalizedContent,
            $this->headlineCapitalizer->capitalizeHeadline($inputContent)
        );
    }

    public static function provideData(): Iterator
    {
        yield ['hi', 'Hi'];
        yield ['put bug in the me', 'Put Bug in the Me'];
        yield ['hello', 'Hello'];
        yield ['About some method()', 'About Some method()'];
    }
}
