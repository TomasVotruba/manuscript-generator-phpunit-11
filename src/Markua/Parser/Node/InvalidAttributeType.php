<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

use InvalidArgumentException;

final class InvalidAttributeType extends InvalidArgumentException
{
    public function __construct(string $attributeKey, string $expectedType, mixed $value)
    {
        parent::__construct(
            sprintf(
                'Attribute "%s" is expected to have a value of type %s, %s provided',
                $attributeKey,
                $expectedType,
                gettype($value)
            )
        );
    }
}
