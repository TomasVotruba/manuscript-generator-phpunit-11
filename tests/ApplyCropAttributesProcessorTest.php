<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test;

use ManuscriptGenerator\ResourceProcessor\ApplyCropAttributesProcessor;
use PHPUnit\Framework\TestCase;

final class ApplyCropAttributesProcessorTest extends TestCase
{
    public function testItAppliesCropStartAndEnd(): void
    {
        $text = <<<CODE_SAMPLE
Line 1
Line 2
Line 3
Line 4
CODE_SAMPLE;

        self::assertSame($text, ApplyCropAttributesProcessor::selectLines($text, null, null));

        self::assertSame(
            <<<CODE_SAMPLE
Line 2
Line 3
Line 4
CODE_SAMPLE
            ,
            ApplyCropAttributesProcessor::selectLines($text, 2, null),
        );

        self::assertSame(<<<CODE_SAMPLE
Line 2
Line 3
CODE_SAMPLE
            , ApplyCropAttributesProcessor::selectLines($text, 2, 3));

        self::assertSame(
            <<<CODE_SAMPLE
Line 1
Line 2
Line 3
CODE_SAMPLE
            ,
            ApplyCropAttributesProcessor::selectLines($text, null, 3)
        );
        self::assertSame(
            <<<CODE_SAMPLE
Line 1
Line 2
Line 3
Line 4
CODE_SAMPLE
            ,
            ApplyCropAttributesProcessor::selectLines($text, null, 5)
        );
    }
}
