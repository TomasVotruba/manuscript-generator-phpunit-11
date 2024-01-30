<?php

namespace ManuscriptGenerator\Checker;

use LogicException;

class CheckerConfig
{
    /**
     * @param array<string,mixed> $config
     */
    private function __construct(
        private readonly array $config
    ) {
    }

    public static function fromJson(string $json): self
    {
        $decodedConfig = json_decode($json, true, 2, JSON_THROW_ON_ERROR);
        if (! is_array($decodedConfig)) {
            throw new LogicException(sprintf('Expected decoded JSON data to be an array, got "%s"', $json));
        }
        if (count(array_filter(array_keys($decodedConfig), 'is_int')) > 0) {
            throw new LogicException(sprintf('Expected decoded JSON data to have only string keys, got "%s"', $json));
        }

        return new self($decodedConfig);
    }

    /**
     * @return list<string>
     */
    public function checkerNames(): array
    {
        return array_keys($this->config);
    }
}
