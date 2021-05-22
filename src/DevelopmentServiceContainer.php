<?php

declare(strict_types=1);

namespace BookTools;

use BookTools\ResourcePreProcessor\CropResourcePreProcessor;
use BookTools\ResourcePreProcessor\RemoveSuperfluousIndentationResourcePreProcessor;

final class DevelopmentServiceContainer
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function application(): ApplicationInterface
    {
        return new Application(
            $this->configuration,
            new HeadlineCapitalizer(),
            new ResourceProcessor(
                new FileResourceLoader(),
                [new CropResourcePreProcessor(), new RemoveSuperfluousIndentationResourcePreProcessor()]
            )
        );
    }
}
