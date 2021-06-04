<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use PhpToken;

final class TableOfTokensResourceGenerator implements ResourceGenerator
{
    public const FILE_SUFFIX = '.table_of_tokens.md';

    public function supportsResource(IncludedResource $resource): bool
    {
        return str_ends_with($resource->link, self::FILE_SUFFIX);
    }

    public function sourcePathForResource(IncludedResource $resource): string
    {
        return str_replace(self::FILE_SUFFIX, '.php', $resource->expectedFilePathname());
    }

    public function generateResource(IncludedResource $resource): string
    {
        $phpFile = ExistingFile::fromPathname($this->sourcePathForResource($resource));

        /** @var PhpToken[] $allTokens */
        $allTokens = PhpToken::tokenize($phpFile->contents());

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
