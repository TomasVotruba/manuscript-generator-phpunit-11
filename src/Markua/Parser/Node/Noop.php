<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

use ManuscriptGenerator\Markua\Printer\MarkuaPrinter;

/**
 * Instead of removing a node from the tree you can replace it with this Noop node. This node has no visual
 * representation in a Markua document
 *
 * @see MarkuaPrinter
 */
final class Noop extends AbstractNode
{
}
