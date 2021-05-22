<?php

declare(strict_types=1);

namespace BookTools\Test;

use BookTools\ResourcePreProcessor\ApplyCropAttributesPreProcessor;
use PHPUnit\Framework\TestCase;

final class ApplyCropAttributesPreProcessorTest extends TestCase
{
    public function testItAppliesCropStartAndEnd(): void
    {
        $text = <<<CODE_SAMPLE
Line 1
Line 2
Line 3
Line 4
CODE_SAMPLE;

        self::assertSame($text, ApplyCropAttributesPreProcessor::selectLines($text, null, null));

        self::assertSame(
            <<<CODE_SAMPLE
Line 2
Line 3
Line 4
CODE_SAMPLE
,
            ApplyCropAttributesPreProcessor::selectLines($text, 2, null),
        );

        self::assertSame(<<<CODE_SAMPLE
Line 2
Line 3
CODE_SAMPLE
, ApplyCropAttributesPreProcessor::selectLines($text, 2, 3));

        self::assertSame(
            <<<CODE_SAMPLE
Line 1
Line 2
Line 3
CODE_SAMPLE
,
            ApplyCropAttributesPreProcessor::selectLines($text, null, 3)
        );
        self::assertSame(
            <<<CODE_SAMPLE
Line 1
Line 2
Line 3
Line 4
CODE_SAMPLE
,
            ApplyCropAttributesPreProcessor::selectLines($text, null, 5)
        );
    }
}
