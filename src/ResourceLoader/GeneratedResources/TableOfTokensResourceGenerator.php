<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use PhpToken;

final class TableOfTokensResourceGenerator implements ResourceGenerator
{
    public function name(): string
    {
        return 'table_of_tokens';
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        /** @var PhpToken[] $allTokens */
        $allTokens = PhpToken::tokenize($source->file()->contents());

        return $this->printTokens($allTokens);
    }

    public function sourceLastModified(
        IncludedResource $resource,
        Source $source,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $determineLastModifiedTimestamp->ofFile($source->file()->pathname());
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
