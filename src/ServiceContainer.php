<?php

declare(strict_types=1);

namespace ManuscriptGenerator;

use ManuscriptGenerator\Cli\Output\ConsoleDiffer;
use ManuscriptGenerator\Cli\Output\Formatter\ColorConsoleDiffFormatter;
use ManuscriptGenerator\Cli\ResultPrinter;
use ManuscriptGenerator\Configuration\BookProjectConfiguration;
use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\Dependencies\ComposerDependenciesInstaller;
use ManuscriptGenerator\FileOperations\Directory;
use ManuscriptGenerator\Markua\Parser\SimpleMarkuaParser;
use ManuscriptGenerator\Markua\Parser\Visitor\NodeVisitor;
use ManuscriptGenerator\Markua\Printer\MarkuaPrinter;
use ManuscriptGenerator\Markua\Processor\AstBasedMarkuaProcessor;
use ManuscriptGenerator\Markua\Processor\CopyIncludedResourceNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Headlines\CapitalizeHeadlinesNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Headlines\HeadlineCapitalizer;
use ManuscriptGenerator\Markua\Processor\ImportIncludedMarkdownFilesNodeVisitor;
use ManuscriptGenerator\Markua\Processor\InlineIncludedResourcesNodeVisitor;
use ManuscriptGenerator\Markua\Processor\LinkRegistry\CollectLinksForLinkRegistryNodeVisitor;
use ManuscriptGenerator\Markua\Processor\MarkuaLoader;
use ManuscriptGenerator\Markua\Processor\ProcessInlineResourcesNodeVisitor;
use ManuscriptGenerator\Markua\Processor\RemoveCommentsNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Subset\CreateSubsetNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Subset\MarkNodesForInclusionInSubsetNodeVisitor;
use ManuscriptGenerator\Markua\Processor\UseFilenameAsCaptionNodeVisitor;
use ManuscriptGenerator\ResourceLoader\FileResourceLoader;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\CopyFromVendorResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\DetermineLastModifiedTimestamp;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\DrawioResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\GenerateIncludedResourceNodeVisitor;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\IncludedResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\PhpScriptOutputResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\PhpUnitResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\RectorOutputResourceGenerator;
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
use ReflectionProperty;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

//use Symplify\PackageBuilder\Console\Formatter\ColorConsoleDiffFormatter;
//use Symplify\PackageBuilder\Console\Output\ConsoleDiffer;

final readonly class ServiceContainer
{
    public function __construct(
        private RuntimeConfiguration $configuration,
        private BookProjectConfiguration $bookProjectConfiguration,
        private OutputInterface $output
    ) {
    }

    public function manuscriptGenerator(): ManuscriptGenerator
    {
        return new ManuscriptGenerator(
            $this->configuration,
            $this->bookProjectConfiguration,
            $this->dependenciesInstaller(),
            new AstBasedMarkuaProcessor(
                $this->markuaNodeVisitors(),
                $this->markuaLoader(),
                $this->markuaPrinter(),
                $this->bookProjectConfiguration->titlePageConfiguration()
            ),
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
        $unifiedDiffOutputBuilder = $this->createUnifiedDiffOutputBuilder();
        $differ = new Differ($unifiedDiffOutputBuilder);

        return new ResultPrinter(new ConsoleDiffer($differ, new ColorConsoleDiffFormatter()));
    }

    private function resourceLoader(): FileResourceLoader
    {
        return new FileResourceLoader();
    }

    private function includedResourceGenerator(): IncludedResourceGenerator
    {
        return new IncludedResourceGenerator(
            array_merge(
                $this->bookProjectConfiguration->resourceGenerators(),
                [
                    new CopyFromVendorResourceGenerator($this->dependenciesInstaller()),
                    new PhpUnitResourceGenerator($this->dependenciesInstaller()),
                    new RectorOutputResourceGenerator($this->dependenciesInstaller()),
                    new TableOfTokensResourceGenerator(),
                    new PhpScriptOutputResourceGenerator($this->dependenciesInstaller()),
                    new DrawioResourceGenerator($this->tmpDir()),
                    new TitlePageResourceGenerator($this->tmpDir()),
                ]
            ),
            new DetermineLastModifiedTimestamp(),
            $this->logger(),
            $this->configuration->regenerateAllGeneratedResources(),
            $this->configuration->readOnlyFilesystem(),
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
            new MarkNodesForInclusionInSubsetNodeVisitor(),
            new GenerateIncludedResourceNodeVisitor($this->includedResourceGenerator()),
            new UseFilenameAsCaptionNodeVisitor(),
            new ImportIncludedMarkdownFilesNodeVisitor(
                $this->resourceLoader(),
                $this->markuaLoader(),
                $this->bookProjectConfiguration->autoImportMarkdownFiles()
            ),
            new InlineIncludedResourcesNodeVisitor($this->resourceLoader()),
            new ProcessInlineResourcesNodeVisitor(
                array_merge(
                    $this->bookProjectConfiguration->additionalResourceProcessors(),
                    [
                        new FragmentResourceProcessor(),
                        new SkipPartOfResourceProcessor(),
                        new CropResourceProcessor(),
                        new ApplyCropAttributesProcessor(),
                        new RemoveSuperfluousIndentationResourceProcessor(),
                        new FixLongLinesResourceProcessor(
                            $this->bookProjectConfiguration,
                            new DelegatingLineFixer(
                                [new PhpUseStatementLineFixer(), new RegularWordWrapLineFixer()]
                            )
                        ),
                        new StripInsignificantWhitespaceResourceProcessor(new InsignificantWhitespaceStripper()),
                    ]
                )
            ),
            new CopyIncludedResourceNodeVisitor($this->resourceLoader()),
            new CreateSubsetNodeVisitor($this->markuaPrinter()),
            new CapitalizeHeadlinesNodeVisitor(
                new HeadlineCapitalizer(),
                $this->bookProjectConfiguration->capitalizeHeadlines()
            ),
            new RemoveCommentsNodeVisitor(),
        ];

        if ($this->bookProjectConfiguration->isLinkRegistryEnabled()) {
            $nodeVisitors[] = new CollectLinksForLinkRegistryNodeVisitor($this->bookProjectConfiguration);
        }

        return $nodeVisitors;
    }

    private function tmpDir(): Directory
    {
        return $this->bookProjectConfiguration->tmpDir();
    }

    private function dependenciesInstaller(): ComposerDependenciesInstaller
    {
        return new ComposerDependenciesInstaller($this->logger());
    }

    private function markuaLoader(): MarkuaLoader
    {
        return new MarkuaLoader($this->markuaParser());
    }

    private function markuaPrinter(): MarkuaPrinter
    {
        return new MarkuaPrinter();
    }

    private function createUnifiedDiffOutputBuilder(): UnifiedDiffOutputBuilder
    {
        $unifiedDiffOutputBuilder = new UnifiedDiffOutputBuilder('');

        // set private property $contextLines value 10000 to see full diffs
        $contextLinesReflectionProperty = new ReflectionProperty($unifiedDiffOutputBuilder, 'contextLines');
        $contextLinesReflectionProperty->setValue($unifiedDiffOutputBuilder, 10);

        return $unifiedDiffOutputBuilder;
    }
}
