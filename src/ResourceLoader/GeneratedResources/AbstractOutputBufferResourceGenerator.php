<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceLoader\GeneratedResources;

use ManuscriptGenerator\Markua\Parser\Node\IncludedResource;

abstract class AbstractOutputBufferResourceGenerator implements CacheableResourceGenerator
{
    public function generateResource(IncludedResource $resource): string
    {
        ob_start();

        $this->generateResourceByEchoingDirectly($resource);

        $output = ob_get_clean();
        assert(is_string($output));

        return $output;
    }

    abstract protected function generateResourceByEchoingDirectly(IncludedResource $resource): void;
}
