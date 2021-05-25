<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser;

use function Parsica\Parsica\any;
use function Parsica\Parsica\atLeastOne;
use function Parsica\Parsica\between;
use function Parsica\Parsica\char;
use function Parsica\Parsica\choice;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\either;
use function Parsica\Parsica\eof;
use function Parsica\Parsica\keepFirst;
use function Parsica\Parsica\map;
use function Parsica\Parsica\newline;
use function Parsica\Parsica\noneOf;
use function Parsica\Parsica\optional;
use Parsica\Parsica\Parser;
use function Parsica\Parsica\satisfy;
use function Parsica\Parsica\sepBy;
use function Parsica\Parsica\skipSpace1;
use function Parsica\Parsica\takeWhile;
use function Parsica\Parsica\zeroOrMore;

final class SimpleMarkuaParser
{
    public function parseDocument(string $markua): Document
    {
        $parser = zeroOrMore(collect(any(self::heading(), self::includedResource(), self::paragraph())))
            ->thenEof()
            ->map(fn (array $nodes) => new Document($nodes));

        return $parser->tryString($markua)
            ->output();
    }

    public function parseAttributes(string $markua): Attributes
    {
        $parser = $this->attributes();

        $result = $parser->tryString($markua);

        return $result->output();
    }

    /**
     * @return Parser<Paragraph>
     */
    private static function paragraph(): Parser
    {
        return keepFirst(
            atLeastOne(choice(noneOf(["\n"]), newline() ->notFollowedBy(newline()))),
            self::newLineOrEof()
        )->map(fn (string $text) => new Paragraph($text));
    }

    /**
     * @return Parser<Attribute>
     */
    private static function attribute(): Parser
    {
        return map(
            collect(
                self::token(zeroOrMore(satisfy(fn (string $char): bool => ! in_array($char, [':'], true)),)),
                self::token(char(':')),
                choice(self::token(self::stringLiteral()), self::token(self::constant()))
            ),
            fn (array $o) => new Attribute($o[0], $o[2])
        );
    }

    /**
     * @return Parser<string>
     */
    private static function stringLiteral(): Parser
    {
        return self::token(
            between(
                char('"'),
                char('"'),
                zeroOrMore(
                    choice(
                        satisfy(fn (string $char): bool => ! in_array($char, ['"', '\\'], true)),
                        char('\\')
                            ->followedBy(choice(char('"')->map(fn ($_) => '"'),))
                    )
                )
            )->map(fn ($o): string => (string) $o) // because the empty json string returns null
        )->label('string literal');
    }

    /**
     * @return Parser<Heading>
     */
    private static function heading(): Parser
    {
        return collect(
            optional(self::attributes()),
            keepFirst(atLeastOne(char('#')), skipSpace1()),
            atLeastOne(satisfy(fn (string $char) => ! in_array($char, ["\n"], true))),
            self::newLineOrEof()
        )->map(fn (array $output) => new Heading(strlen($output[1]), $output[2], $output[0]));
    }

    /**
     * @return Parser<string>
     */
    private static function newLineOrEof(): Parser
    {
        return either(atLeastOne(newline()), eof());
    }

    /**
     * @return Parser<string>
     */
    private static function constant(): Parser
    {
        return self::token(
            zeroOrMore(satisfy(fn (string $char): bool => ! in_array($char, [' ', ',', '}'], true)))
        )->label('constant');
    }

    /**
     * @param Parser<string> $parser
     * @return Parser<string>
     */
    private static function token(Parser $parser): Parser
    {
        return keepFirst($parser, self::space());
    }

    /**
     * @return Parser<string>
     */
    private static function space(): Parser
    {
        return takeWhile(fn (string $char) => $char === ' ')
            ->voidLeft('')
            ->label('whitespace');
    }

    /**
     * @return Parser<IncludedResource>
     */
    private static function includedResource(): Parser
    {
        return collect(
            optional(self::attributes()),
            char('!')
                ->then(
                    between(
                        char('['),
                        char(']'),
                        zeroOrMore(
                            choice(
                                satisfy(fn (string $char) => ! in_array($char, [']', '\\'], true)),
                                char('\\')
                                    ->followedBy(char(']'))
                            )
                        )
                    )
                )->label('caption'),
            between(
                char('('),
                char(')'),
                zeroOrMore(satisfy(fn (string $char) => ! in_array($char, [')'], true)))
            )->label('link'),
            self::newLineOrEof()
        )->map(fn (array $collected) => new IncludedResource($collected[2], $collected[1], $collected[0]));
    }

    /**
     * @return Parser<Attributes>
     */
    private static function attributes(): Parser
    {
        return keepFirst(
            between(
                self::token(char('{')),
                self::token(char('}')),
                sepBy(self::token(char(',')), self::attribute())
            ),
            self::newLineOrEof()
        )->map(fn (array $members) => new Attributes($members));
    }
}
