<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

final class InitCommand extends Command
{
    /**
     * @var string
     */
    public const COMMAND_NAME = 'init';

    /**
     * @var string
     */
    private const NEW_BOOK_TEMPLATES_DIRECTORY = __DIR__ . '/../../templates/new-book';

    public function __construct(
        private Filesystem $filesystem
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Creates a start structure for a new book');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $manuscriptSrcDirectory = getcwd() . '/manuscript-src';

        // the directory already exists - ask for override
        if ($this->filesystem->exists($manuscriptSrcDirectory)) {
            $shouldOverride = $symfonyStyle->ask('The "manuscripts-src" already exists. Do you want to override it?');
            if ($shouldOverride === false) {
                $symfonyStyle->note('Nothing has changed');
                return self::SUCCESS;
            }
        }

        assert(is_dir(self::NEW_BOOK_TEMPLATES_DIRECTORY));

        $workingDirectory = getcwd();
        assert(is_string($workingDirectory));
        assert(is_dir($workingDirectory));

        $this->filesystem->mirror(self::NEW_BOOK_TEMPLATES_DIRECTORY, $workingDirectory, null, [
            'override' => true,
        ]);
        $symfonyStyle->success(
            'New book structure was successfully generated. Try to generate book to see what happens:'
        );
        $symfonyStyle->writeln('vendor/bin/generate-manuscript');

        return self::SUCCESS;
    }
}
