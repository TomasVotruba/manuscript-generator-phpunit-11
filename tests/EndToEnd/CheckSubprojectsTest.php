<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test\EndToEnd;

use ManuscriptGenerator\Cli\CheckSubprojectsCommand;
use Symfony\Component\Console\Tester\CommandTester;

final class CheckSubprojectsTest extends AbstractEndToEndTest
{
    private CommandTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester = new CommandTester(new CheckSubprojectsCommand());
    }

    public function testCheckSubprojects(): void
    {
        $this->filesystem->mirror(__DIR__ . '/SubprojectsCi/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
            ]
        );

        $display = $this->tester->getDisplay();
        self::assertStringContainsString('Failed checks: 1', $display);
        self::assertStringContainsString('PHPUnit test failed', $display);
    }
}
