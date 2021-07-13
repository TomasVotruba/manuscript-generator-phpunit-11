<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test\EndToEnd;

use ManuscriptGenerator\Cli\ListSubprojectsCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class ListSubprojectsTest extends TestCase
{
    private CommandTester $tester;

    protected function setUp(): void
    {
        $this->tester = new CommandTester(new ListSubprojectsCommand());
    }

    public function testSubprojectsRequireAPhpUnitOrRectorRun(): void
    {
        $this->tester->execute([
            'dir' => __DIR__ . '/SubprojectsCi',
        ]);

        self::assertEquals(
            [
                [
                    'directory' => 'manuscript-src/resources/subproject2',
                    'runPhpUnit' => false,
                    'runRector' => true,
                ],
                [
                    'directory' => 'manuscript-src/resources/subproject1',
                    'runPhpUnit' => true,
                    'runRector' => false,
                ],
                [
                    'directory' => 'manuscript-src/resources/subproject3',
                    'runRector' => false,
                    'runPhpUnit' => false,
                ],
            ],
            json_decode($this->tester->getDisplay(), true)
        );
    }
}
