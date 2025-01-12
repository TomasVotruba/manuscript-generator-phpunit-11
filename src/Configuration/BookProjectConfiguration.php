<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Configuration;

use Assert\Assertion;
use ManuscriptGenerator\FileOperations\Directory;
use ManuscriptGenerator\FileOperations\ExistingDirectory;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\ResourceGenerator;
use ManuscriptGenerator\ResourceProcessor\ResourceProcessor;

final class BookProjectConfiguration
{
    /**
     * @var array<ResourceGenerator>
     */
    private array $resourceGenerators = [];

    /**
     * @var array<ResourceProcessor>
     */
    private array $resourceProcessors = [];

    private bool $autoImportMarkdownFiles = true;

    public function __construct(
        private string $manuscriptSrcDir = 'manuscript-src',
        private string $manuscriptTargetDir = 'manuscript',
        private string $tmpDir = 'var/tmp',
        private bool $capitalizeHeadlines = false,
        private ?LinkRegistryConfiguration $linkRegistryConfiguration = null,
        private ?TitlePageConfiguration $titlePageConfiguration = null
    ) {
        if ($this->titlePageConfiguration === null) {
            $this->titlePageConfiguration = TitlePageConfiguration::uploadOnLeanpub();
        }
    }

    public static function usingDefaults(): self
    {
        return new self();
    }

    public function manuscriptSrcDir(): ExistingDirectory
    {
        return ExistingDirectory::fromPathname($this->manuscriptSrcDir);
    }

    public function setManuscriptSrcDir(string $manuscriptSrcDir): void
    {
        $this->manuscriptSrcDir = $manuscriptSrcDir;
    }

    public function setManuscriptTargetDir(string $manuscriptTargetDir): void
    {
        $this->manuscriptTargetDir = $manuscriptTargetDir;
    }

    public function manuscriptTargetDir(): Directory
    {
        return Directory::fromPathname($this->manuscriptTargetDir);
    }

    public function capitalizeHeadlines(): bool
    {
        return $this->capitalizeHeadlines;
    }

    public function setCapitalizeHeadings(bool $capitalizeHeadlines): void
    {
        $this->capitalizeHeadlines = $capitalizeHeadlines;
    }

    public function addResourceGenerator(ResourceGenerator $resourceGenerator): void
    {
        $this->resourceGenerators[] = $resourceGenerator;
    }

    /**
     * @return array<ResourceGenerator>
     */
    public function resourceGenerators(): array
    {
        return $this->resourceGenerators;
    }

    public function addResourceProcessor(ResourceProcessor $resourceProcessor): void
    {
        $this->resourceProcessors[] = $resourceProcessor;
    }

    /**
     * @return array<ResourceProcessor>
     */
    public function additionalResourceProcessors(): array
    {
        return $this->resourceProcessors;
    }

    public function setLinkRegistryConfiguration(LinkRegistryConfiguration $linkRegistryConfiguration): void
    {
        $this->linkRegistryConfiguration = $linkRegistryConfiguration;
    }

    public function isLinkRegistryEnabled(): bool
    {
        return $this->linkRegistryConfiguration !== null;
    }

    public function linkRegistryConfiguration(): LinkRegistryConfiguration
    {
        assert($this->linkRegistryConfiguration !== null);

        return $this->linkRegistryConfiguration;
    }

    public function tmpDir(): Directory
    {
        return Directory::fromPathname($this->tmpDir);
    }

    public function setTmpDir(string $tmpDir): void
    {
        $this->tmpDir = $tmpDir;
    }

    public function setTitlePageConfiguration(TitlePageConfiguration $titlePageConfiguration): void
    {
        $this->titlePageConfiguration = $titlePageConfiguration;
    }

    public function titlePageConfiguration(): TitlePageConfiguration
    {
        Assertion::notNull($this->titlePageConfiguration);

        return $this->titlePageConfiguration;
    }

    public function autoImportMarkdownFiles(): bool
    {
        return $this->autoImportMarkdownFiles;
    }

    public function setAutoImportMarkdownFiles(bool $autoImportMarkdownFiles): void
    {
        $this->autoImportMarkdownFiles = $autoImportMarkdownFiles;
    }

    public function maximumLineLengthForInlineResources(): int
    {
        return 72;
    }
}
