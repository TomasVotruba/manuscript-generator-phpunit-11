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
        self::assertStringContainsString('3/3', $display, 'Expected two subprojects to be checked');
        self::assertStringContainsString('Failed checks: 2', $display);
        self::assertStringContainsString('PHPUnit test failed', $display);
    }

    public function testCheckSubprojectsFailFast(): void
    {
        $this->filesystem->mirror(__DIR__ . '/SubprojectsCi/manuscript-src', $this->manuscriptSrcDir);

        $this->tester->execute(
            [
                '--manuscript-dir' => $this->manuscriptDir,
                '--manuscript-src-dir' => $this->manuscriptSrcDir,
                '--fail-fast' => true,
            ]
        );

        $display = $this->tester->getDisplay();

        // Two subprojects will fail, but we expect only the first one to be reported
        self::assertStringContainsString('Failed checks: 1', $display);
    }
}
