<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use Assert\Assertion;
use LogicException;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use Psr\Log\LoggerInterface;
use RuntimeException;

final readonly class IncludedResourceGenerator
{
    /**
     * @param array<ResourceGenerator> $resourceGenerators
     */
    public function __construct(
        private array $resourceGenerators,
        private DetermineLastModifiedTimestamp $determineLastModifiedTimestamp,
        private LoggerInterface $logger,
        private bool $regenerateAllResources,
        private bool $dryRun
    ) {
    }

    public function generateResource(IncludedResource $resource): void
    {
        $resourceGenerator = $this->getResourceGeneratorFor($resource);

        $expectedFile = $resource->expectedFile();

        $sourceAttribute = $resource->attributes->getStringOrNull('source');

        $source = new Source(
            $resource->includedFromFile()
                ->containingDirectory()
                ->appendPath($sourceAttribute ?? '')
                ->pathname()
        );

        if (! $this->regenerateAllResources
            && $expectedFile->exists()
            && $resourceGenerator->sourceLastModified($resource, $source, $this->determineLastModifiedTimestamp)
            <= $expectedFile->existing()
                ->lastModifiedTime()
        ) {
            $this->logger->debug('Generated resource {link} was still fresh', [
                'link' => $resource->link,
            ]);
            return;
        }

        if ($this->dryRun) {
            throw new RuntimeException(sprintf(
                'The following resource would have to be (re)generated: %s ',
                $resource->debugInfo()
            ));
        }

        $generatedResource = $resourceGenerator->generateResource($resource, $source);

        $expectedFile->putContents($generatedResource);

        $this->logger->info('Generated resource {link}', [
            'link' => $resource->link,
        ]);
    }

    private function getResourceGeneratorFor(IncludedResource $includedResource): ResourceGenerator
    {
        $generatorName = $includedResource->attributes->getStringOrNull('generator');
        Assertion::string($generatorName);

        foreach ($this->resourceGenerators as $resourceGenerator) {
            if ($resourceGenerator->name() === $generatorName) {
                return $resourceGenerator;
            }
        }

        throw new LogicException(
            sprintf(
                '"generator" key contains unknown resource generator "%s" (%s). Available options: %s',
                $generatorName,
                $includedResource->debugInfo(),
                implode(
                    ', ',
                    array_map(
                        fn (ResourceGenerator $resourceGenerator): string => $resourceGenerator->name(),
                        $this->resourceGenerators
                    )
                )
            )
        );
    }
}
