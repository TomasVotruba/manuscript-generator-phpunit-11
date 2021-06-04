<?php

declare(strict_types=1);

namespace ManuscriptGenerator;

use ManuscriptGenerator\Cli\ResultPrinter;
use ManuscriptGenerator\Configuration\RuntimeConfiguration;
use ManuscriptGenerator\FileOperations\FileOperations;
use ManuscriptGenerator\FileOperations\Filesystem;
use ManuscriptGenerator\Markua\Parser\SimpleMarkuaParser;
use ManuscriptGenerator\Markua\Parser\Visitor\NodeVisitor;
use ManuscriptGenerator\Markua\Printer\MarkuaPrinter;
use ManuscriptGenerator\Markua\Processor\AstBasedMarkuaProcessor;
use ManuscriptGenerator\Markua\Processor\Headlines\CapitalizeHeadlinesNodeVisitor;
use ManuscriptGenerator\Markua\Processor\Headlines\HeadlineCapitalizer;
use ManuscriptGenerator\Markua\Processor\InlineIncludedMarkdownFilesNodeVisitor;
use ManuscriptGenerator\Markua\Processor\InlineIncludedResourcesNodeVisitor;
use ManuscriptGenerator\Markua\Processor\LinkRegistry\CollectLinksForLinkRegistryNodeVisitor;
use ManuscriptGenerator\Markua\Processor\ProcessInlineResourcesNodeVisitor;
use ManuscriptGenerator\ResourceLoader\DelegatingResourceLoader;
use ManuscriptGenerator\ResourceLoader\FileResourceLoader;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\CachedResourceLoader;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\DrawioResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\GeneratedResourceLoader;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\PhpScriptOutputResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\PHPUnit\PhpUnitResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\RectorOutputResourceLoader;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\TableOfTokensResourceGenerator;
use ManuscriptGenerator\ResourceLoader\GeneratedResources\VendorResourceLoader;
use ManuscriptGenerator\ResourceProcessor\ApplyCropAttributesProcessor;
use ManuscriptGenerator\ResourceProcessor\CropResourceProcessor;
use ManuscriptGenerator\ResourceProcessor\InsignificantWhitespaceStripper;
use ManuscriptGenerator\ResourceProcessor\RemoveSuperfluousIndentationResourceProcessor;
use ManuscriptGenerator\ResourceProcessor\StripInsignificantWhitespaceResourceProcessor;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symplify\ConsoleColorDiff\Console\Formatter\ColorConsoleDiffFormatter;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;

final class DevelopmentServiceContainer
{
    private ?EventDispatcher $eventDispatcher = null;

    public function __construct(
        private RuntimeConfiguration $configuration
    ) {
    }

    public function manuscriptGenerator(): ManuscriptGenerator
    {
        return new ManuscriptGenerator(
            $this->configuration,
            $this->fileOperations(),
            new AstBasedMarkuaProcessor($this->markuaNodeVisitors(), $this->markuaParser(), new MarkuaPrinter()),
            $this->eventDispatcher()
        );
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->printResultsSubscriber()
            ->setOutput($output);
    }

    public function addEventSubscriber(EventSubscriberInterface $eventSubscriber): void
    {
        $this->eventDispatcher()
            ->addSubscriber($eventSubscriber);
    }

    private function eventDispatcher(): EventDispatcher
    {
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = new EventDispatcher();
            $this->eventDispatcher->addSubscriber($this->printResultsSubscriber());
        }

        return $this->eventDispatcher;
    }

    private function fileOperations(): FileOperations
    {
        return new FileOperations(
            new Filesystem($this->configuration->readOnlyFilesystem()),
            $this->eventDispatcher()
        );
    }

    private function printResultsSubscriber(): ResultPrinter
    {
        return $this->printResults ??= new ResultPrinter(
            new NullOutput(),
            new ConsoleDiffer(new Differ(), new ColorConsoleDiffFormatter())
        );
    }

    private function resourceLoader(): DelegatingResourceLoader
    {
        return new DelegatingResourceLoader(
            [
                new VendorResourceLoader($this->fileOperations()),
                new CachedResourceLoader(
                    new GeneratedResourceLoader(
                        array_merge(
                            [
                                new PhpUnitResourceGenerator(),
                                new RectorOutputResourceLoader(),
                                new TableOfTokensResourceGenerator(),
                                new PhpScriptOutputResourceGenerator(),
                                new DrawioResourceGenerator(),
                            ],
                            $this->configuration->additionalResourceGenerators()
                        ),
                        $this->fileOperations()
                    ),
                    new FileResourceLoader(),
                    $this->eventDispatcher()
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
            new InlineIncludedMarkdownFilesNodeVisitor($this->resourceLoader(), $this->markuaParser()),
            new InlineIncludedResourcesNodeVisitor($this->resourceLoader(),),
            new ProcessInlineResourcesNodeVisitor(
                [
                    new CropResourceProcessor(),
                    new ApplyCropAttributesProcessor(),
                    new RemoveSuperfluousIndentationResourceProcessor(),
                    new StripInsignificantWhitespaceResourceProcessor(new InsignificantWhitespaceStripper()),
                ]
            ),
            new CapitalizeHeadlinesNodeVisitor(
                new HeadlineCapitalizer(),
                $this->configuration->capitalizeHeadlines()
            ),
        ];

        if ($this->configuration->isLinkRegistryEnabled()) {
            $nodeVisitors[] = new CollectLinksForLinkRegistryNodeVisitor(
                $this->fileOperations(),
                $this->configuration->linkRegistryConfiguration(),
                $this->configuration
            );
        }

        return $nodeVisitors;
    }
}
