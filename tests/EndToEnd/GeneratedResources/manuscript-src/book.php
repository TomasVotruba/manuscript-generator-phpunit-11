<?php

declare(strict_types=1);

use BookTools\Configuration\BookProjectConfiguration;
use BookTools\Markua\Parser\Node\IncludedResource;
use BookTools\ResourceLoader\GeneratedResources\AbstractOutputBufferResourceGenerator;

$configuration = BookProjectConfiguration::usingDefaults();

$configuration->addResourceGenerator(
    new class() extends AbstractOutputBufferResourceGenerator {
        private const EXPECTED_SUFFIX = '.buffered-output.txt';

        public function supportsResource(IncludedResource $resource): bool
        {
            return str_ends_with($resource->link, self::EXPECTED_SUFFIX);
        }

        protected function generateResourceByEchoingDirectly(IncludedResource $resource): void
        {
            echo 'Echo whatever you want based on ' . $resource->expectedFilePathname();
        }
    }
);

return $configuration;
