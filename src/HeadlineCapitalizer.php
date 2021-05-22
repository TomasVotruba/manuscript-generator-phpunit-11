<?php

declare(strict_types=1);

namespace BookTools;

use Nette\Utils\Strings;

/**
 * @see \BookTools\Test\HeadlineCapitalizerTest
 */
final class HeadlineCapitalizer
{
    /**
     * @see https://regex101.com/r/0I1FPP/1
     * @var string
     */
    private const MARKDOWN_HEADLINE_REGEX = '#^(?<prefix>\#{1,}\s+)(?<headline>.*?)$#ms';

    /**
     * @var string[]
     */
    private const LOWERCASE_EXCEPTIONS = [
        'and', 'or', 'of', 'from', 'to', 'in', 'is', 'a', 'an', 'the', 'not', 'at', 'with', 'it', 'as', 'but', 'for', 'on', 'nor', 'until', 'if', 'unless', 'its',
    ];

    public function capitalizeHeadlines(string $fileContent): string
    {
        // skip comments in code snippets
        $codeSnippetMatches = Strings::matchAll($fileContent, '#^```(?<code>.*?)```$#ms');

        return Strings::replace($fileContent, self::MARKDOWN_HEADLINE_REGEX, function (
            array $match
        ) use ($codeSnippetMatches) {
            $headline = $match['headline'];
            foreach ($codeSnippetMatches as $codeSnippetMatch) {
                if (Strings::contains($codeSnippetMatch['code'], $headline)) {
                    // return original content
                    return $match[0];
                }
            }

            $headlineParts = Strings::split($headline, '#\s+#');

            foreach ($headlineParts as $key => $headlinePart) {
                if (in_array($headlinePart, self::LOWERCASE_EXCEPTIONS, true)) {
                    continue;
                }

                // code, skip it
                if (Strings::endsWith($headlinePart, '()')) {
                    continue;
                }

                $headlineParts[$key] = ucfirst($headlinePart);
            }

            $newHeadline = implode(' ', $headlineParts);

            return $match['prefix'] . $newHeadline;
        });
    }
}
