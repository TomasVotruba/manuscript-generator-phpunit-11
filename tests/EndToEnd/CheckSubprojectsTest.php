<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test\EndToEnd;

use ManuscriptGenerator\Process\Result;
use Symfony\Component\Process\Process;

final class CheckSubprojectsTest extends AbstractEndToEndTestCase
{
    public function testCheckSubprojects(): void
    {
        $this->filesystem->mirror(__DIR__ . '/SubprojectsCi/manuscript-src', $this->manuscriptSrcDir);

        $process = new Process([
            'php',
            'bin/generate-manuscript',
            'check',
            '--manuscript-dir',
            $this->manuscriptDir,
            '--manuscript-src-dir',
            $this->manuscriptSrcDir,
        ]);
        $process->run();
        self::assertFalse($process->isSuccessful());
        $display = $process->getOutput();

        self::assertStringContainsString('Failed checks: 2', $display, $process->getErrorOutput());
        self::assertStringContainsString('PHPUnit test failed', $display, $process->getErrorOutput());
    }

    public function testCheckSubprojectsJsonOutput(): void
    {
        $this->filesystem->mirror(__DIR__ . '/SubprojectsCi/manuscript-src', $this->manuscriptSrcDir);

        $process = new Process([
            'php',
            'bin/generate-manuscript',
            'check',
            '--manuscript-dir',
            $this->manuscriptDir,
            '--manuscript-src-dir',
            $this->manuscriptSrcDir,
            '--json',
        ]);
        $process->run();
        self::assertFalse($process->isSuccessful());
        $display = $process->getOutput();

        self::assertJson($display);

        $decodedData = json_decode($display, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decodedData);

        $results = array_map(fn (array $data): Result => Result::fromArray($data), $decodedData);
        self::assertCount(3, $results);
    }

    public function testCheckSubprojectsFailFast(): void
    {
        $this->filesystem->mirror(__DIR__ . '/SubprojectsCi/manuscript-src', $this->manuscriptSrcDir);

        $process = new Process([
            'php',
            'bin/generate-manuscript',
            'check',
            '--manuscript-dir',
            $this->manuscriptDir,
            '--manuscript-src-dir',
            $this->manuscriptSrcDir,
            '--fail-fast',
        ]);
        $process->run();
        self::assertFalse($process->isSuccessful());
        $display = $process->getOutput();

        // Two subprojects will fail, but we expect only the first one to be reported
        self::assertStringContainsString('Failed checks: 1', $display);
    }
}
