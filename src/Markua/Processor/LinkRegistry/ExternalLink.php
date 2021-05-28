<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor\LinkRegistry;

use Assert\Assertion;

final class ExternalLink
{
    public function __construct(
        private string $url,
        private string $slug
    ) {
        Assertion::url($url);
        Assertion::notEmpty($slug);
        Assertion::startsWith($slug, '/', 'slug should start with a /');
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function url(): string
    {
        return $this->url;
    }
}
