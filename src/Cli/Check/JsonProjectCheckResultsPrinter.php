<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli\Check;

use Assert\Assertion;
use ManuscriptGenerator\Process\Result;
use Symfony\Component\Console\Output\OutputInterface;

final class JsonProjectCheckResultsPrinter implements ProjectCheckResultsPrinter
{
    public function __construct(
        private OutputInterface $output
    ) {
    }

    public function finish(array $allResults): void
    {
        $jsonEncodedResults = json_encode(
            array_map(fn (Result $result): array => $result->toArray(), $allResults),
            JSON_THROW_ON_ERROR
        );
        Assertion::string($jsonEncodedResults);

        $this->output->writeln($jsonEncodedResults);
    }

    public function setNumberOfDirectories(int $number): void
    {
    }

    public function advance(string $directory): void
    {
    }
}
