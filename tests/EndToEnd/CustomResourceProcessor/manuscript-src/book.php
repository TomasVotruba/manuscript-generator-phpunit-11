<?php

declare(strict_types=1);

use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\ResourceLoader\LoadedResource;
use ManuscriptGenerator\ResourceProcessor\ResourceProcessor;

$configuration = BookProjectConfiguration::usingDefaults();

$configuration->addResourceProcessor(
    new class() implements ResourceProcessor {
        public function process(LoadedResource $resource): void
        {
            // Fix smileys
            $resource->setContents(str_replace('-)', ')', $resource->contents()));
        }
    }
);

return $configuration;
