<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use Assert\Assertion;
use ManuscriptGenerator\FileOperations\ExistingFile;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use PhpToken;

final class TableOfTokensResourceGenerator implements CacheableResourceGenerator
{
    public const FILE_SUFFIX = '.table_of_tokens.md';

    public function name(): string
    {
        return 'table_of_tokens';
    }

    public function sourcePathForResource(IncludedResource $resource): ExistingFile
    {
        $script = $resource->attributes->get('source');
        Assertion::string($script);

        return ExistingFile::fromPathname($resource->includedFromFile()->containingDirectory() . '/' . $script);
    }

    public function generateResource(IncludedResource $resource, Source $source): string
    {
        $phpFile = $this->sourcePathForResource($resource);

        /** @var PhpToken[] $allTokens */
        $allTokens = PhpToken::tokenize($phpFile->contents());

        return $this->printTokens($allTokens);
    }

    public function sourceLastModified(
        IncludedResource $resource,
        DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
    ): int {
        return $determineLastModifiedTimestamp->ofFile($this->sourcePathForResource($resource)->pathname());
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
