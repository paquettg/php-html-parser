<?php

declare(strict_types=1);

use PHPHtmlParser\Dom\Cleaner;
use PHPHtmlParser\Options;
use PHPUnit\Framework\TestCase;

class CleanerTest extends TestCase
{
    public function testCleanEregiFailureFile()
    {
        $cleaner = new Cleaner();
        $string = $cleaner->clean(\file_get_contents('tests/data/files/mvEregiReplaceFailure.html'), new Options(), 'utf-8');
        $this->assertNotEquals(0, \strlen($string));
    }
}
