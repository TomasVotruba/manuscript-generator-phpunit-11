<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Configuration;

use Assert\Assert;

final class LinkRegistryConfiguration
{
    public function __construct(
        private string $linksFile,
        private string $linkRegistryBaseUrl
    ) {
        Assert::that($this->linksFile)->notEmpty();
        Assert::that($this->linkRegistryBaseUrl)->url();
    }

    public function linksFile(): string
    {
        return $this->linksFile;
    }

    public function linkRegistryBaseUrl(): string
    {
        return $this->linkRegistryBaseUrl;
    }
}
