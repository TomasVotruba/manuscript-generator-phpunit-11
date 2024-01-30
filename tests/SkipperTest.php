<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test;

use ManuscriptGenerator\ResourceProcessor\TextSkipper;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SkipperTest extends TestCase
{
    public function testSkipEndAfterSkipStart(): void
    {
        $skipper = new TextSkipper();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('found before start marker');

        $skipper->skipParts(<<<EOF
        foo

        // skip-end

        bar

        // skip-start
        EOF);
    }
}
