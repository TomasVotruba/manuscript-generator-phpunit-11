<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser;

use ManuscriptGenerator\Markua\Parser\Node\Aside;
use ManuscriptGenerator\Markua\Parser\Node\Attribute;
use ManuscriptGenerator\Markua\Parser\Node\AttributeList;
use ManuscriptGenerator\Markua\Parser\Node\Blurb;
use ManuscriptGenerator\Markua\Parser\Node\Directive;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\Node\Heading;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\Markua\Parser\Node\InlineResource;
use ManuscriptGenerator\Markua\Parser\Node\Link;
use ManuscriptGenerator\Markua\Parser\Node\Paragraph;
use ManuscriptGenerator\Markua\Parser\Node\Span;
use function Parsica\Parsica\alphaChar;
use function Parsica\Parsica\alphaNumChar;
use function Parsica\Parsica\any;
use function Parsica\Parsica\atLeastOne;
use function Parsica\Parsica\between;
use function Parsica\Parsica\char;
use function Parsica\Parsica\choice;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\either;
use function Parsica\Parsica\eof;
use function Parsica\Parsica\eol;
use function Parsica\Parsica\keepFirst;
use function Parsica\Parsica\keepSecond;
use function Parsica\Parsica\map;
use function Parsica\Parsica\newline;
use function Parsica\Parsica\noneOf;
use function Parsica\Parsica\optional;
use Parsica\Parsica\Parser;
use function Parsica\Parsica\repeat;
use function Parsica\Parsica\satisfy;
use function Parsica\Parsica\sepBy;
use function Parsica\Parsica\skipSpace1;
use function Parsica\Parsica\string;
use function Parsica\Parsica\takeWhile;
use function Parsica\Parsica\zeroOrMore;

final class SimpleMarkuaParser
{
    public function parseDocument(string $markua): Document
    {
        $parser = zeroOrMore(
            collect(
                any(
                    self::aside(),
                    self::blurb(),
                    self::directive(),
                    self::heading(),
                    self::includedResource(),
                    self::inlineResource(),
                    self::paragraph()
                )
            )
        )
            ->thenEof()
            ->map(fn (array $nodes) => new Document($nodes));

        return $parser->tryString($markua)
            ->output();
    }

    /**
     * @return Parser<Aside>
     */
    public static function aside(): Parser
    {
        return keepFirst(
            between(
                keepFirst(string('{aside}'), eol()),
                string('{/aside}'),
                zeroOrMore(
                    choice(noneOf(['{']), char('{') ->notFollowedBy(string('/aside')))
                )->map(fn (string $contents) => self::parseBlock($contents))
            ),
            self::newLineOrEof()
        )
            ->label('aside')
            ->map(fn (array $subnodes) => new Aside($subnodes));
    }

    /**
     * @return Parser<Blurb>
     */
    public static function blurb(): Parser
    {
        return keepFirst(
            collect(
                string('{blurb'), // 0
                optional(keepSecond(string(', '), self::attributes())), // 1
                keepFirst(string('}'), eol()), // 2
                zeroOrMore( // 3
                    choice(noneOf(['{']), char('{') ->notFollowedBy(string('/blurb}')))
                )
                    ->map(fn (string $chars) => self::parseBlock($chars)),
                string('{/blurb}')
            ),
            self::newLineOrEof()
        )->label('blurb')
            ->map(fn (array $parts) => new Blurb($parts[3], $parts[1]));
    }

    /**
     * @return array<Node>
     */
    private static function parseBlock(string $blockContents): array
    {
        $parser = zeroOrMore(
            collect(any(self::heading(), self::includedResource(), self::inlineResource(), self::paragraph()))
        )
            ->thenEof();

        return $parser->tryString($blockContents)
            ->output();
    }

    /**
     * @return Parser<string>
     */
    private static function uriBetweenBrackets(): Parser
    {
        return between(
            char('('),
            char(')'),
            zeroOrMore(noneOf([' ', "\n", ')'])) // quite a simplification
        );
    }

    /**
     * @return Parser<string>
     */
    private static function textBetweenSquareBrackets(): Parser
    {
        return between(
            char('['),
            char(']'),
            zeroOrMore(
                choice(
                    satisfy(fn (string $char) => ! in_array($char, [']', '\\'], true)),
                    char('\\')
                        ->followedBy(char(']'))
                )
            )
        );
    }

    /**
     * @return Parser<Directive>
     */
    private static function directive(): Parser
    {
        return keepFirst(
            between(char('{'), char('}'), choice(
                string('frontmatter'),
                string('mainmatter'),
                string('backmatter'),
            ))->label('directive'),
            self::newLineOrEof()
        )->map(fn (string $name) => new Directive($name));
    }

    /**
     * @return Parser<Paragraph>
     */
    private static function paragraph(): Parser
    {
        return keepFirst(
            atLeastOne(
                collect(
                    choice(
                        collect(
                            self::textBetweenSquareBrackets()->label('linkText'), // 0
                                self::uriBetweenBrackets()->label('target'), // 1
                                optional(self::attributeList()) // 2
                        )->map(fn (array $parts) => new Link($parts[1], $parts[0], $parts[2])),
                        choice(
                            noneOf(['`', '!', '{', '[', "\n"])
                                ->and(
                                    zeroOrMore(
                                        choice(
                                            noneOf(["\n", '[']),
                                            newline()
                                                ->notFollowedBy(either(newline(), eof()))
                                        )
                                    )
                                ),
                            char('[')
                                ->notFollowedBy(zeroOrMore(noneOf([']']))->followedBy(char(']')->then(char('('))))
                        )
                            ->map(fn (?string $text) => new Span((string) $text))
                            ->label('span'),
                    )
                )
            ),
            self::newLineOrEof()
        )
            ->map(fn ($parts) => new Paragraph(self::simplifyNodes($parts)))
            ->label('paragraph');
    }

    /**
     * @param array<Node> $originalNodes
     * @return array<Node>
     */
    private static function simplifyNodes(array $originalNodes): array
    {
        $simplified = [];
        foreach ($originalNodes as $currentNode) {
            $previousNode = $simplified[count($simplified) - 1] ?? null;
            if ($currentNode instanceof Span && $previousNode instanceof Span) {
                $previousNode->text .= $currentNode->text;
            } else {
                $simplified[] = $currentNode;
            }
        }

        return $simplified;
    }

    /**
     * @return Parser<Attribute>
     */
    private static function attribute(): Parser
    {
        return map(
            collect(
                self::token(zeroOrMore(satisfy(fn (string $char): bool => ! in_array($char, [':'], true)))),
                self::token(char(':')),
                choice(self::token(self::stringLiteral()), self::token(self::constant()))
            )->label('attribute'),
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
            optional(keepFirst(either(self::attributeList(), self::idAttributeList()), self::newLineOrEof())),
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
            optional(keepFirst(self::attributeList(), self::newLineOrEof())), // 0
            char('!')
                ->then(self::textBetweenSquareBrackets())->label('caption'), // 1
            self::uriBetweenBrackets()->label('link'), // 2
            self::newLineOrEof() // 3
        )->map(fn (array $collected) => new IncludedResource($collected[2], $collected[1], $collected[0]));
    }

    /**
     * @return Parser<InlineResource>
     */
    private static function inlineResource(): Parser
    {
        return collect(
            optional(keepFirst(self::attributeList(), self::newLineOrEof())), // 0
            repeat(3, char('`')), // 1
            keepFirst(optional(atLeastOne(alphaChar())), newline())
                ->label('format'), // 2
            zeroOrMore(
                choice(
                    satisfy(fn (string $char) => ! in_array($char, ['`'], true)),
                    newline()
                        ->notFollowedBy(char('`'))
                )
            )->label('source'), // 3
            repeat(3, char('`')), // 4
            self::newLineOrEof() // 5
        )->map(fn (array $collected) => new InlineResource($collected[3], $collected[2], $collected[0]));
    }

    /**
     * @return Parser<AttributeList>
     */
    private static function attributeList(): Parser
    {
        return between(self::token(char('{')), self::token(char('}')), self::attributes())->label('attribute list');
    }

    /**
     * @return Parser<AttributeList>
     */
    private static function attributes(): Parser
    {
        return sepBy(self::token(char(',')), self::attribute())
            ->map(fn (array $attributes) => new AttributeList($attributes));
    }

    /**
     * @return Parser<AttributeList>
     */
    private static function idAttributeList(): Parser
    {
        return between(
            self::token(char('{')),
            self::token(char('}')),
            char('#')
                ->then(atLeastOne(choice(alphaNumChar(), char('_'), char('-'))))
        )->label('idAttribute')
            ->map(fn (string $id) => new AttributeList([new Attribute('id', $id)]));
    }
}
