<?php

declare(strict_types=1);

namespace BookTools;

use BookTools\Cli\ResultPrinter;
use BookTools\FileOperations\FileOperations;
use BookTools\FileOperations\Filesystem;
use BookTools\Markua\Parser\SimpleMarkuaParser;
use BookTools\Markua\Printer\MarkuaPrinter;
use BookTools\Markua\Processor\AstBasedMarkuaProcessor;
use BookTools\Markua\Processor\Headlines\CapitalizeHeadlinesNodeVisitor;
use BookTools\Markua\Processor\Headlines\HeadlineCapitalizer;
use BookTools\Markua\Processor\InlineIncludedMarkdownFilesNodeVisitor;
use BookTools\Markua\Processor\InlineIncludedResourcesNodeVisitor;
use BookTools\Markua\Processor\ProcessInlineResourcesNodeVisitor;
use BookTools\ResourceLoader\DelegatingResourceLoader;
use BookTools\ResourceLoader\FileResourceLoader;
use BookTools\ResourceLoader\GeneratedResources\CachedResourceLoader;
use BookTools\ResourceLoader\GeneratedResources\GeneratedResourceLoader;
use BookTools\ResourceLoader\GeneratedResources\PHPUnit\PhpUnitResourceGenerator;
use BookTools\ResourceLoader\GeneratedResources\RectorOutputResourceLoader;
use BookTools\ResourceLoader\GeneratedResources\VendorResourceLoader;
use BookTools\ResourceProcessor\ApplyCropAttributesProcessor;
use BookTools\ResourceProcessor\CropResourceProcessor;
use BookTools\ResourceProcessor\InsignificantWhitespaceStripper;
use BookTools\ResourceProcessor\RemoveSuperfluousIndentationResourceProcessor;
use BookTools\ResourceProcessor\StripInsignificantWhitespaceResourceProcessor;
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
            new AstBasedMarkuaProcessor(
                [
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
                ],
                $this->markuaParser(),
                new MarkuaPrinter()
            ),
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
                        [new PhpUnitResourceGenerator(), new RectorOutputResourceLoader()],
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
}
