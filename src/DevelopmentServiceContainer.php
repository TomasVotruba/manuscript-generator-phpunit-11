<?php

declare(strict_types=1);

namespace BookTools;

use BookTools\ResourceLoader\DelegatingResourceLoader;
use BookTools\ResourceLoader\FileResourceLoader;
use BookTools\ResourceLoader\PHPUnit\PhpUnitOutputResourceLoader;
use BookTools\ResourceLoader\VendorResourceLoader;
use BookTools\ResourcePreProcessor\ApplyCropAttributesPreProcessor;
use BookTools\ResourcePreProcessor\CropResourcePreProcessor;
use BookTools\ResourcePreProcessor\DelegatingResourcePreProcessor;
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
            new DelegatingResourceLoader(
                [new VendorResourceLoader(), new PhpUnitOutputResourceLoader(), new FileResourceLoader()]
            ),
            new DelegatingResourcePreProcessor(
                [
                    new CropResourcePreProcessor(),
                    new ApplyCropAttributesPreProcessor(),
                    new RemoveSuperfluousIndentationResourcePreProcessor(),
                ]
            )
        );
    }
}
