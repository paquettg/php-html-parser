<?php

declare(strict_types=1);

use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;
use PHPUnit\Framework\TestCase;

class CleanupTest extends TestCase
{
    public function testCleanupInputTrue()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setCleanupInput(true));
        $dom->loadFromFile('tests/data/files/big.html');
        $this->assertEquals(0, \count($dom->find('style')));
        $this->assertEquals(0, \count($dom->find('script')));
    }

    public function testCleanupInputFalse()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setCleanupInput(false));
        $dom->loadFromFile('tests/data/files/big.html');
        $this->assertEquals(1, \count($dom->find('style')));
        $this->assertEquals(22, \count($dom->find('script')));
    }

    public function testRemoveStylesTrue()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setRemoveStyles(true));
        $dom->loadFromFile('tests/data/files/big.html');
        $this->assertEquals(0, \count($dom->find('style')));
    }

    public function testRemoveStylesFalse()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setRemoveStyles(false));
        $dom->loadFromFile('tests/data/files/big.html');
        $this->assertEquals(1, \count($dom->find('style')));
        $this->assertEquals(
            'text/css',
            $dom->find('style')->getAttribute('type')
        );
    }

    public function testRemoveScriptsTrue()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setRemoveScripts(true));
        $dom->loadFromFile('tests/data/files/big.html');
        $this->assertEquals(0, \count($dom->find('script')));
    }

    public function testRemoveScriptsFalse()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setRemoveScripts(false));
        $dom->loadFromFile('tests/data/files/big.html');
        $this->assertEquals(22, \count($dom->find('script')));
        $this->assertEquals(
            'text/javascript',
            $dom->find('script')->getAttribute('type')
        );
    }

    public function testSmartyScripts()
    {
        $dom = new Dom();
        $dom->loadStr('
        aa={123}
        ');
        $this->assertEquals(' aa= ', $dom->innerHtml);
    }

    public function testSmartyScriptsDisabled()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setRemoveSmartyScripts(false));
        $dom->loadStr('
        aa={123}
        ');
        $this->assertEquals(' aa={123} ', $dom->innerHtml);
    }
}
