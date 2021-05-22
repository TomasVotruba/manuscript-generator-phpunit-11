<?php

declare(strict_types=1);

namespace BookTools;

final class DevelopmentServiceContainer
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function application(): ApplicationInterface
    {
        return new Application($this->configuration, new HeadlineCapitalizer());
    }
}
