<?php

declare(strict_types=1);

namespace BookTools\Test;

use BookTools\Markua\Processor\Headlines\HeadlineCapitalizer;
use Iterator;
use PHPUnit\Framework\TestCase;

final class HeadlineCapitalizerTest extends TestCase
{
    private HeadlineCapitalizer $headlineCapitalizer;

    protected function setUp(): void
    {
        $this->headlineCapitalizer = new HeadlineCapitalizer();
    }

    /**
     * @dataProvider provideData()
     */
    public function test(string $inputContent, string $expectedCapitalizedContent): void
    {
        $capitilizedContent = $this->headlineCapitalizer->capitalizeHeadlines($inputContent);
        $this->assertSame($expectedCapitalizedContent, $capitilizedContent);
    }

    /**
     * @return Iterator<string[]>
     */
    public function provideData(): Iterator
    {
        yield ['# hi', '# Hi'];
        yield ['# put bug in the me', '# Put Bug in the Me'];
        yield [
            <<<'CODE_SAMPLE'
# hello

```
## skip me, I am code
```
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
# Hello

```
## skip me, I am code
```
CODE_SAMPLE
        ];
    }
}
