<?php

declare(strict_types=1);

namespace BookTools\Test;

use BookTools\Attribute;
use BookTools\ResourceAttributes;
use PHPUnit\Framework\TestCase;

final class ResourceAttributesTest extends TestCase
{
    public function testItDealsWithALineThatContainsNoAttributes(): void
    {
        self::assertEquals(new ResourceAttributes([]), ResourceAttributes::fromString('Contains no attributes'));
    }

    public function testItExtractsExistingAttributes(): void
    {
        self::assertEquals(
            new ResourceAttributes([new Attribute('crop-start', '6'), new Attribute('caption', '"Caption"')]),
            ResourceAttributes::fromString('{crop-start: 6, caption: "Caption"}')
        );
    }
}
