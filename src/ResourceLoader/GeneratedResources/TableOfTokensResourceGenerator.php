<?php

declare(strict_types=1);

namespace BookTools\ResourceLoader\GeneratedResources;

use BookTools\Markua\Parser\Node\IncludedResource;
use PhpToken;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TableOfTokensResourceGenerator implements ResourceGenerator
{
    public const FILE_SUFFIX = '.table_of_tokens.md';

    public function supportsResource(IncludedResource $resource): bool
    {
        return str_ends_with($resource->link, self::FILE_SUFFIX);
    }

    public function generateResource(IncludedResource $resource): string
    {
        $phpFile = new SmartFileInfo(str_replace(self::FILE_SUFFIX, '.php', $resource->expectedFilePathname()));

        /** @var PhpToken[] $allTokens */
        $allTokens = PhpToken::tokenize($phpFile->getContents());

        return $this->printTokens($allTokens);
    }

    /**
     * @param PhpToken[] $allTokens
     */
    private function printTokens(array $allTokens): string
    {
        $table = '';

        $table .= "| Line | Token | Value |\n";
        $table .= "| --- | --- | --- |\n";

        foreach ($allTokens as $token) {
            $table .= sprintf(
                "| %d | `%s` | `%s` |\n",
                $token->line,
                $token->getTokenName(),
                str_replace("\n", '\n', (string) $token)
            );
        }

        return $table;
    }
}