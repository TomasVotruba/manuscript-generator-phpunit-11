<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test\EndToEnd;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractEndToEndTest extends TestCase
{
    protected Filesystem $filesystem;

    protected string $manuscriptSrcDir;

    protected string $manuscriptDir;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();

        // Create temporary directories
        $this->manuscriptDir = sys_get_temp_dir() . '/' . uniqid('manuscript');
        $this->filesystem->mkdir($this->manuscriptDir);

        $this->manuscriptSrcDir = sys_get_temp_dir() . '/' . uniqid('manuscript-src');
        $this->filesystem->mkdir($this->manuscriptSrcDir);
    }

    protected function tearDown(): void
    {
        // Remove temporary directories
        $this->filesystem->remove($this->manuscriptDir);
        $this->filesystem->remove($this->manuscriptSrcDir);
    }
}
