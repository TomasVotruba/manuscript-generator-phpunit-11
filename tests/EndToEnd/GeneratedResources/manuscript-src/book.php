<?php

declare(strict_types=1);

use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\AbstractOutputBufferResourceGenerator;

$configuration = BookProjectConfiguration::usingDefaults();

$configuration->addResourceGenerator(
    new class() extends AbstractOutputBufferResourceGenerator {
        private const EXPECTED_SUFFIX = '.buffered-output.txt';

        public function supportsResource(IncludedResource $resource): bool
        {
            return str_ends_with($resource->link, self::EXPECTED_SUFFIX);
        }

        public function sourcePathForResource(IncludedResource $resource): string
        {
            return __FILE__;
        }

        protected function generateResourceByEchoingDirectly(IncludedResource $resource): void
        {
            echo 'Echo whatever you want';
        }
    }
);

return $configuration;
