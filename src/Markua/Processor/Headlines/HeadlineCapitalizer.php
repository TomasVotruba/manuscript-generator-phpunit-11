<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor\Headlines;

use Nette\Utils\Strings;

final class HeadlineCapitalizer
{
    /**
     * @var string[]
     */
    private const LOWERCASE_EXCEPTIONS = [
        'and', 'or', 'of', 'from', 'to', 'in', 'is', 'a', 'an', 'the', 'not', 'at', 'with', 'it', 'as', 'but', 'for', 'on', 'nor', 'until', 'if', 'unless', 'its',
    ];

    public function capitalizeHeadline(string $headline): string
    {
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

        return implode(' ', $headlineParts);
    }
}
