<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\ResourceLoader\LoadedResource;
use Psr\Log\LoggerInterface;

final class WrapLongLinesResourceProcessor implements ResourceProcessor
{
    public function __construct(
        private RuntimeConfiguration $configuration,
        private LoggerInterface $logger
    ) {
    }

    public function process(LoadedResource $resource): void
    {
        $maximumLineWidth = $this->configuration->maximumLineWidthForInlineResources();

        $lines = explode("\n", $resource->contents());
        $processed = [];
        foreach ($lines as $line) {
            if (strlen($line) <= $maximumLineWidth) {
                $processed[] = $line;
                continue;
            }

            if (str_contains($line, 'use ')) {
                // Don't touch use statements
                // @TODO how to generalize this?
                $processed[] = $line;
                $this->logger->warning(
                    'Line of inline resource is too long: {line} ({length})',
                    [
                        'line' => $line,
                        'length' => strlen($line),
                    ]
                );
                continue;
            }

            $processed[] = wordwrap($line, $maximumLineWidth, "\n", true);
        }

        $resource->setContents(implode("\n", $processed));
    }
}
