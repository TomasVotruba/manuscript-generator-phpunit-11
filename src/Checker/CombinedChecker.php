<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Checker;

use ManuscriptGenerator\Dependencies\DependenciesInstaller;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\Process\Result;
use Psr\Log\LoggerInterface;

final readonly class CombinedChecker
{
    /**
     * @param array<Checker> $checkers
     */
    public function __construct(
        private array $checkers,
        private DependenciesInstaller $dependenciesInstaller,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return array<Result>
     */
    public function check(ExistingDirectory $directory): array
    {
        $results = [];

        $this->dependenciesInstaller->install($directory);

        $configPath = $directory->appendPath('check.json');
        if (! $configPath->file()->exists()) {
            return $results;
        }
        $config = CheckerConfig::fromJson($configPath->file()->getContents());

        foreach ($config->checkerNames() as $name) {
            foreach ($this->checkers as $checker) {
                if ($checker->name() === $name) {
                    $logContext = [
                        'dir' => $directory->pathname(),
                        'checker' => $checker->name(),
                    ];
                    $this->logger->debug('Running checker {checker} in {dir}', $logContext);

                    $result = $checker->check($directory);
                    if ($result !== null) {
                        if (! $result->isSuccessful()) {
                            $this->logger->debug('Checker {checker} failed in {dir}', $logContext);
                        } else {
                            $this->logger->debug('Checker {checker} passed {dir}', $logContext);
                        }

                        $results[] = $result;
                    } else {
                        $this->logger->debug('Checker {checker} skipped in {dir}', $logContext);
                    }
                }
            }
        }

        return $results;
    }
}
