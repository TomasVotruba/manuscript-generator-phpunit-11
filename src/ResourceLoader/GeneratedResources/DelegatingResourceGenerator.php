<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use Assert\Assertion;
use LogicException;
use ManuscriptGenerator\FileOperations\Filesystem;
use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;
use Psr\Log\LoggerInterface;

final class DelegatingResourceGenerator implements ResourceGenerator
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

    public function name(): string
    {
        // @TODO reconsider type hierarchy: this method makes no sense here
        return 'delegating';
    }

    public function generateResource(IncludedResource $resource): string
    {
        $resourceGenerator = $this->getResourceGeneratorFor($resource);
        $expectedPath = $resource->expectedFilePathname();

        if ($resourceGenerator instanceof CacheableResourceGenerator) {
            if (is_file($expectedPath)
                && $resourceGenerator->sourceLastModified($resource, $this->determineLastModifiedTimestamp)
                <= ((int) filemtime($expectedPath))
            ) {
                $this->logger->debug('Generated resource {link} was still fresh', [
                    'link' => $resource->link,
                ]);

                return $this->filesystem->getContents($expectedPath);
            }
        }

        $generatedResource = $resourceGenerator->generateResource($resource);
        $this->filesystem->putContents($expectedPath, $generatedResource);

        $this->logger->info('Generated resource {link}', [
            'link' => $resource->link,
        ]);

        return $generatedResource;
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
