<?php

namespace ManuscriptGenerator\Test;

use JsonException;
use LogicException;
use ManuscriptGenerator\Checker\CheckerConfig;
use PHPUnit\Framework\TestCase;

class CheckerConfigTest extends TestCase
{
    public function testFailsWhenInvalidJson(): void
    {
        $this->expectException(JsonException::class);
        CheckerConfig::fromJson('{"invalid');
    }

    public function testFailsWhenJsonIsNotArray(): void
    {
        $this->expectException(LogicException::class);
        CheckerConfig::fromJson('false');
    }

    public function testFailsWhenJsonIsNotAssociativeArray(): void
    {
        $this->expectException(LogicException::class);
        CheckerConfig::fromJson('["foo"]');
    }

    public function testCheckerNames(): void
    {
        $this->assertSame(['PHPUnit'], CheckerConfig::fromJson('{"PHPUnit": true}')->checkerNames());
    }
}
