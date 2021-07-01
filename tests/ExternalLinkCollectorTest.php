<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test;

use Generator;
use Iterator;
use ManuscriptGenerator\Markua\Processor\LinkRegistry\CouldNotAddExternalLink;
use ManuscriptGenerator\Markua\Processor\LinkRegistry\ExternalLink;
use ManuscriptGenerator\Markua\Processor\LinkRegistry\ExternalLinkCollector;
use PHPUnit\Framework\TestCase;

final class ExternalLinkCollectorTest extends TestCase
{
    /**
     * @dataProvider fileContentsProvider
     */
    public function testLoadFromExistingContents(ExternalLinkCollector $expectedCollector, string $fileContents): void
    {
        self::assertEquals($expectedCollector, ExternalLinkCollector::loadFromString($fileContents));
    }

    /**
     * @return Generator<array{ExternalLinkCollector,string}>
     */
    public function fileContentsProvider(): Iterator
    {
        yield [
            new ExternalLinkCollector([]),
            "\n", // only a newline
        ];
        yield [
            new ExternalLinkCollector([new ExternalLink('https://matthiasnoback.nl', '/blog')]),
            "/blog https://matthiasnoback.nl\n", // one trailing newline
        ];
        yield [
            new ExternalLinkCollector([new ExternalLink('https://matthiasnoback.nl', '/blog')]),
            '/blog https://matthiasnoback.nl', // no trialing new line
        ];
        yield [
            new ExternalLinkCollector([new ExternalLink('https://matthiasnoback.nl', '/blog')]),
            "/blog https://matthiasnoback.nl\n\n", // two trailing newlines
        ];
    }

    public function testAdd(): void
    {
        $collector = new ExternalLinkCollector([]);
        $collector->add('/blog', 'https://matthiasnoback.nl');

        self::assertEquals(
            new ExternalLinkCollector([new ExternalLink('https://matthiasnoback.nl', '/blog')]),
            $collector
        );
    }

    public function testAddIsIdempotent(): void
    {
        $collector = new ExternalLinkCollector([]);
        $collector->add('/blog', 'https://matthiasnoback.nl');
        $collector->add('/blog', 'https://matthiasnoback.nl');

        self::assertSame(1, preg_match_all('/blog/', $collector->asString(), $matches));
    }

    public function testAddFailsIfSlugExistsForADifferentUrl(): void
    {
        $collector = new ExternalLinkCollector([]);
        $collector->add('/blog', 'https://matthiasnoback.nl');

        $this->expectException(CouldNotAddExternalLink::class);

        $collector->add('/blog', 'https://blog.com');
    }
}
