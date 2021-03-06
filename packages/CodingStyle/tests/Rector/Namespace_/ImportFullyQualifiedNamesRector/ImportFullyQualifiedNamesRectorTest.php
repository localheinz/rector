<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector;

use Iterator;
use Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ImportFullyQualifiedNamesRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     * @dataProvider provideDataForTestFunction()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideDataForTestFunction(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureFunction');
    }

    public function skippedProviderPartials(): Iterator
    {
        // @todo fix later
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixturePartial');
    }

    protected function getRectorClass(): string
    {
        return ImportFullyQualifiedNamesRector::class;
    }
}
