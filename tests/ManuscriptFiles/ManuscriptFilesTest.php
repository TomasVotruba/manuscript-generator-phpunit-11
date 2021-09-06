<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test\ManuscriptFiles;

use ManuscriptGenerator\FileOperations\Directory;
use ManuscriptGenerator\ManuscriptFiles\ManuscriptFiles;
use ManuscriptGenerator\ManuscriptFiles\ModifiedFile;
use ManuscriptGenerator\ManuscriptFiles\NewFile;
use ManuscriptGenerator\ManuscriptFiles\UnusedFile;
use PHPUnit\Framework\TestCase;

final class ManuscriptFilesTest extends TestCase
{
    public function testItComparesGeneratedManuscriptFilesToExistingFiles(): void
    {
        $manuscriptFiles = ManuscriptFiles::createEmpty();

        // contents changed from "original" to "modified"
        $manuscriptFiles->addFile('modified.md', "modified\n");

        // this file doesn't exist in ./manuscript/
        $manuscriptFiles->addFile('new.md', "new\n");

        // the contents didn't change
        $manuscriptFiles->addFile('unchanged.md', "unchanged\n");

        $diff = $manuscriptFiles->diff(ManuscriptFiles::fromDir(Directory::fromPathname(__DIR__ . '/manuscript')));

        self::assertTrue($diff->hasDifferences());

        self::assertEquals([new NewFile('new.md', "new\n")], $diff->newFiles());

        self::assertEquals([new ModifiedFile('modified.md', "original\n", "modified\n")], $diff->modifiedFiles());

        self::assertEquals([new UnusedFile('unused.md', "unused\n")], $diff->unusedFiles());
    }
}
