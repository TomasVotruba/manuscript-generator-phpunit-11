<?php

declare(strict_types=1);

use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\AbstractOutputBufferResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\DetermineLastModifiedTimestamp;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\ResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\Source;

$configuration = BookProjectConfiguration::usingDefaults();

$configuration->addResourceGenerator(
    new class() extends AbstractOutputBufferResourceGenerator {
        public function name(): string
        {
            return 'buffered_output';
        }

        public function sourceLastModified(
            IncludedResource $resource,
            Source $source,
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
        public function name(): string
        {
            return 'diagram';
        }

        public function generateResource(IncludedResource $resource, Source $source): string
        {
            return "binary contents\n";
        }

        public function sourceLastModified(
            IncludedResource $resource,
            Source $source,
            DetermineLastModifiedTimestamp $determineLastModifiedTimestamp
        ): int {
            return 0;
        }
    }
);

return $configuration;
