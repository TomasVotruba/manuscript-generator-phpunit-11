<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\LinkRegistry;

use RuntimeException;

final class ExternalLinkCollector
{
    /**
     * @param array<ExternalLink> $externalLinks
     */
    public function __construct(
        private array $externalLinks = []
    ) {
    }

    public static function loadFromString(string $string): self
    {
        $lines = array_filter(explode("\n", $string));

        // @TODO use Parsica :)

        return new self(
            array_map(
                function (string $line): ExternalLink {
                    $parts = explode(' ', $line);
                    if (count($parts) !== 2) {
                        throw new RuntimeException('Each line should contain a slug and a URL separated by a space');
                    }
                    list($slug, $url) = $parts;

                    return new ExternalLink($url, $slug);
                },
                $lines
            )
        );
    }

    public function add(string $slug, string $url): void
    {
        foreach ($this->externalLinks as $link) {
            if ($link->slug() === $slug) {
                if ($link->url() !== $url) {
                    throw CouldNotAddExternalLink::becauseTheSlugIsAlreadyInUse($slug, $url, $link->url());
                }

                // We already have this link
                return;
            }
        }

        $this->externalLinks[] = new ExternalLink($url, $slug);
    }

    public function asString(): string
    {
        $lines = [];

        foreach ($this->externalLinks as $externalLink) {
            $lines[] = $externalLink->slug() . ' ' . $externalLink->url();
        }

        return implode("\n", $lines) . "\n";
    }
}
