<?php

declare(strict_types=1);

namespace ManuscriptGenerator;

use ManuscriptGenerator\Cli\ResultPrinter;
use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\Dependencies\ComposerDependenciesInstaller;
use ManuscriptGenerator\FileOperations\Filesystem;
use ManuscriptGenerator\Markua\Parser\SimpleMarkuaParser;
use ManuscriptGenerator\Markua\Parser\Visitor\NodeVisitor;
use ManuscriptGenerator\Markua\Printer\MarkuaPrinter;
use ManuscriptGenerator\Markua\Processor\AstBasedMarkuaProcessor;
use ManuscriptGenerator\Markua\Processor\CopyIncludedResourceNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Headlines\CapitalizeHeadlinesNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Headlines\HeadlineCapitalizer;
use ManuscriptGenerator\Markua\Processor\InlineIncludedMarkdownFilesNodeVisitor;
use ManuscriptGenerator\Markua\Processor\InlineIncludedResourcesNodeVisitor;
use ManuscriptGenerator\Markua\Processor\LinkRegistry\CollectLinksForLinkRegistryNodeVisitor;
use ManuscriptGenerator\Markua\Processor\MarkuaLoader;
use ManuscriptGenerator\Markua\Processor\ProcessInlineResourcesNodeVisitor;
use ManuscriptGenerator\Markua\Processor\UseFilenameAsCaptionNodeVisitor;
use ManuscriptGenerator\ResourceLoader\DelegatingResourceLoader;
use ManuscriptGenerator\ResourceLoader\FileResourceLoader;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\CopyFromVendorResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\DetermineLastModifiedTimestamp;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\DrawioResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\GeneratedResourceLoader;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\PhpScriptOutputResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\PHPUnit\PhpUnitResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\RectorOutputResourceLoader;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\TableOfTokensResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\TitlePageResourceGenerator;
use ManuscriptGenerator\ResourceProcessor\ApplyCropAttributesProcessor;
use ManuscriptGenerator\ResourceProcessor\CropResourceProcessor;
use ManuscriptGenerator\ResourceProcessor\FragmentResourceProcessor;
use ManuscriptGenerator\ResourceProcessor\InsignificantWhitespaceStripper;
use ManuscriptGenerator\ResourceProcessor\LineLength\DelegatingLineFixer;
use ManuscriptGenerator\ResourceProcessor\LineLength\FixLongLinesResourceProcessor;
use ManuscriptGenerator\ResourceProcessor\LineLength\PhpUseStatementLineFixer;
use ManuscriptGenerator\ResourceProcessor\LineLength\RegularWordWrapLineFixer;
use ManuscriptGenerator\ResourceProcessor\RemoveSuperfluousIndentationResourceProcessor;
use ManuscriptGenerator\ResourceProcessor\SkipPartOfResourceProcessor;
use ManuscriptGenerator\ResourceProcessor\StripInsignificantWhitespaceResourceProcessor;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\ConsoleColorDiff\Console\Formatter\ColorConsoleDiffFormatter;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;

final class ServiceContainer
{
    public function __construct(
        private RuntimeConfiguration $configuration,
        private OutputInterface $output
    ) {
    }

    public function manuscriptGenerator(): ManuscriptGenerator
    {
        return new ManuscriptGenerator(
            $this->configuration,
            $this->dependenciesInstaller(),
            new AstBasedMarkuaProcessor($this->markuaNodeVisitors(), $this->markuaLoader(), new MarkuaPrinter()),
            $this->logger(),
            $this->resultPrinter()
        );
    }

    private function logger(): ConsoleLogger
    {
        return new ConsoleLogger($this->output);
    }

    private function resultPrinter(): ResultPrinter
    {
        return new ResultPrinter(new ConsoleDiffer(new Differ(), new ColorConsoleDiffFormatter()));
    }

    private function filesystem(): Filesystem
    {
        return new Filesystem($this->configuration->readOnlyFilesystem());
    }

    private function resourceLoader(): DelegatingResourceLoader
    {
        return new DelegatingResourceLoader(
            [
                new GeneratedResourceLoader(
                    array_merge(
                        $this->configuration->additionalResourceGenerators(),
                        [
                            new CopyFromVendorResourceGenerator(),
                            new PhpUnitResourceGenerator(),
                            new RectorOutputResourceLoader(),
                            new TableOfTokensResourceGenerator(),
                            new PhpScriptOutputResourceGenerator(),
                            new DrawioResourceGenerator($this->tmpDir()),
                            new TitlePageResourceGenerator($this->tmpDir()),
                        ]
                    ),
                    new FileResourceLoader(),
                    $this->filesystem(),
                    $this->dependenciesInstaller(),
                    new DetermineLastModifiedTimestamp(),
                    $this->logger()
                ),
                new FileResourceLoader(),
            ]
        );
    }

    private function markuaParser(): SimpleMarkuaParser
    {
        return new SimpleMarkuaParser();
    }

    /**
     * @return array<NodeVisitor>
     */
    private function markuaNodeVisitors(): array
    {
        $nodeVisitors = [
            new UseFilenameAsCaptionNodeVisitor(),
            new InlineIncludedMarkdownFilesNodeVisitor($this->resourceLoader(), $this->markuaLoader()),
            new InlineIncludedResourcesNodeVisitor($this->resourceLoader()),
            new ProcessInlineResourcesNodeVisitor(
                array_merge(
                    $this->configuration->additionalResourceProcessors(),
                    [
                        new FragmentResourceProcessor(),
                        new SkipPartOfResourceProcessor(),
                        new CropResourceProcessor(),
                        new ApplyCropAttributesProcessor(),
                        new RemoveSuperfluousIndentationResourceProcessor(),
                        new FixLongLinesResourceProcessor(
                            $this->configuration,
                            new DelegatingLineFixer(
                                [new PhpUseStatementLineFixer(), new RegularWordWrapLineFixer()]
                            )
                        ),
                        new StripInsignificantWhitespaceResourceProcessor(new InsignificantWhitespaceStripper()),
                    ]
                )
            ),
            new CopyIncludedResourceNodeVisitor($this->resourceLoader()),
            new CapitalizeHeadlinesNodeVisitor(
                new HeadlineCapitalizer(),
                $this->configuration->capitalizeHeadlines()
            ),
        ];

        if ($this->configuration->isLinkRegistryEnabled()) {
            $nodeVisitors[] = new CollectLinksForLinkRegistryNodeVisitor(
                $this->filesystem(),
                $this->configuration->linkRegistryConfiguration(),
                $this->configuration
            );
        }

        return $nodeVisitors;
    }

    private function tmpDir(): string
    {
        return $this->configuration->tmpDir();
    }

    private function dependenciesInstaller(): ComposerDependenciesInstaller
    {
        return new ComposerDependenciesInstaller($this->configuration, $this->logger());
    }

    private function markuaLoader(): MarkuaLoader
    {
        return new MarkuaLoader($this->markuaParser());
    }
}
