<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use Assert\Assertion;
use LogicException;
use ManuscriptGenerator\FileOperations\Filesystem;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use Psr\Log\LoggerInterface;

final class DelegatingResourceGenerator implements IncludedResourceGenerator
{
    /**
     * @param array<ResourceGenerator> $resourceGenerators
     */
    public function __construct(
        private array $resourceGenerators,
        private Filesystem $filesystem,
        private DetermineLastModifiedTimestamp $determineLastModifiedTimestamp,
        private LoggerInterface $logger
    ) {
    }

    public function generateResource(IncludedResource $resource): void
    {
        $resourceGenerator = $this->getResourceGeneratorFor($resource);

        $expectedPath = $resource->expectedFilePathname();

        $source = new Source(
            $resource->includedFromFile()
                ->containingDirectory()
                ->toString() . '/' . $resource->attributes->get('source')
        );

        if (is_file($expectedPath)
            && $resourceGenerator->sourceLastModified($resource, $source, $this->determineLastModifiedTimestamp)
            <= ((int) filemtime($expectedPath))
        ) {
            $this->logger->debug('Generated resource {link} was still fresh', [
                'link' => $resource->link,
            ]);
            return;
        }

        $generatedResource = $resourceGenerator->generateResource($resource, $source);
        $this->filesystem->putContents($expectedPath, $generatedResource);

        $this->logger->info('Generated resource {link}', [
            'link' => $resource->link,
        ]);
    }

    private function getResourceGeneratorFor(IncludedResource $includedResource): ResourceGenerator
    {
        $generatorName = $includedResource->attributes->get('generator');
        Assertion::string($generatorName);

        foreach ($this->resourceGenerators as $resourceGenerator) {
            if ($resourceGenerator->name() === $generatorName) {
                return $resourceGenerator;
            }
        }

        throw new LogicException(
            sprintf(
                '"generator" key contains unknown resource generator "%s" (%s)',
                $generatorName,
                $includedResource->debugInfo()
            )
        );
    }
}
