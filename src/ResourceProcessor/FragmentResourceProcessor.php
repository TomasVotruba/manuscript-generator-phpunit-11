<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use ManuscriptGenerator\ResourceLoader\LoadedResource;

final class FragmentResourceProcessor implements ResourceProcessor
{
    public function process(LoadedResource $resource): void
    {
        $selectFragment = $resource->getAttribute('fragment');
        if ($selectFragment === null) {
            return;
        }

        $cropper = new TextCropper('// fragment-start ' . $selectFragment, '// fragment-end ' . $selectFragment);

        $resource->setContents($cropper->crop($resource->contents()));
        $resource->removeAttribute('fragment');
    }
}
