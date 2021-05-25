<?php

declare(strict_types=1);

namespace BookTools;

use BookTools\Cli\ResultPrinter;
use BookTools\FileOperations\FileOperations;
use BookTools\FileOperations\Filesystem;
use BookTools\Markua\Parser\SimpleMarkuaParser;
use BookTools\Markua\Printer\MarkuaPrinter;
use BookTools\Markua\Processor\AstBasedMarkuaProcessor;
use BookTools\Markua\Processor\DelegatingMarkuaProcessor;
use BookTools\Markua\Processor\Headlines\CapitalizeHeadlinesProcessor;
use BookTools\Markua\Processor\Headlines\HeadlineCapitalizer;
use BookTools\Markua\Processor\InlineIncludedResourcesMarkuaProcessor;
use BookTools\ResourceLoader\DelegatingResourceLoader;
use BookTools\ResourceLoader\FileResourceLoader;
use BookTools\ResourceLoader\PHPUnit\PhpUnitOutputResourceLoader;
use BookTools\ResourceLoader\VendorResourceLoader;
use BookTools\ResourcePreProcessor\ApplyCropAttributesPreProcessor;
use BookTools\ResourcePreProcessor\CropResourcePreProcessor;
use BookTools\ResourcePreProcessor\DelegatingResourcePreProcessor;
use BookTools\ResourcePreProcessor\RemoveSuperfluousIndentationResourcePreProcessor;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symplify\ConsoleColorDiff\Console\Formatter\ColorConsoleDiffFormatter;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;

final class DevelopmentServiceContainer
{
    private Configuration $configuration;

    private ?EventDispatcher $eventDispatcher = null;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function manuscriptGenerator(): ManuscriptGenerator
    {
        return new ManuscriptGenerator(
            $this->configuration,
            $this->fileOperations(),
            new DelegatingMarkuaProcessor(
                [
                    new InlineIncludedResourcesMarkuaProcessor(
                        new DelegatingResourceLoader(
                            [
                                new VendorResourceLoader($this->fileOperations()),
                                new PhpUnitOutputResourceLoader($this->fileOperations()),
                                new FileResourceLoader(),
                            ]
                        ),
                        new DelegatingResourcePreProcessor(
                            [
                                new CropResourcePreProcessor(),
                                new ApplyCropAttributesPreProcessor(),
                                new RemoveSuperfluousIndentationResourcePreProcessor(),
                            ]
                        ),
                    ),
                    new AstBasedMarkuaProcessor(
                        [
                            new CapitalizeHeadlinesProcessor(
                                new HeadlineCapitalizer(),
                                $this->configuration->capitalizeHeadlines()
                            ),
                        ],
                        new SimpleMarkuaParser(),
                        new MarkuaPrinter()
                    ),
                ]
            )
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

    private function eventDispatcher(): EventDispatcherInterface
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
}
