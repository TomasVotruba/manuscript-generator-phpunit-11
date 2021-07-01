<?php

declare(strict_types=1);

use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\AbstractOutputBufferResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\DetermineLastModifiedTimestamp;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\ResourceGenerator;

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

        public function sourceLastModified(
            IncludedResource $resource,
            DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
        ): int {
            return 0;
        }

        protected function generateResourceByEchoingDirectly(IncludedResource $resource): void
        {
            echo 'Echo whatever you want';
        }
    }
);

$configuration->addResourceGenerator(
    new class() implements ResourceGenerator {
        private const EXPECTED_SUFFIX = '.diagram.png';

        public function supportsResource(IncludedResource $resource): bool
        {
            return str_ends_with($resource->link, self::EXPECTED_SUFFIX);
        }

        public function sourcePathForResource(IncludedResource $resource): string
        {
            return __FILE__;
        }

        public function generateResource(IncludedResource $resource): string
        {
            return "binary contents\n";
        }

        public function sourceLastModified(
            IncludedResource $resource,
            DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
        ): int {
            return 0;
        }
    }
);

return $configuration;
