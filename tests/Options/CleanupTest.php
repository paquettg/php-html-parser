<?php

use PHPHtmlParser\Dom;

class CleanupTest extends PHPUnit_Framework_TestCase {

    public function testCleanupInputTrue()
    {
        $dom = new Dom;
        $dom->setOptions([
            'cleanupInput' => true,
        ]);
        $dom->loadFromFile('tests/files/horrible.html');
        $this->assertEquals(0, count($dom->find('style')));
        $this->assertEquals(0, count($dom->find('script')));
    }

    public function testCleanupInputFalse()
    {
        $dom = new Dom;
        $dom->setOptions([
            'cleanupInput' => false,
        ]);
        $dom->loadFromFile('tests/files/horrible.html');
        $this->assertEquals(1, count($dom->find('style')));
        $this->assertEquals(1, count($dom->find('script')));
    }

    public function testRemoveStylesTrue()
    {
        $dom = new Dom;
        $dom->setOptions([
            'removeStyles' => true,
        ]);
        $dom->loadFromFile('tests/files/horrible.html');
        $this->assertEquals(0, count($dom->find('style')));
    }

    public function testRemoveStylesFalse()
    {
        $dom = new Dom;
        $dom->setOptions([
            'removeStyles' => false,
        ]);
        $dom->loadFromFile('tests/files/horrible.html');
        $this->assertEquals(1, count($dom->find('style')));
        $this->assertEquals('text/css',
            $dom->find('style')->getAttribute('type'));
    }

    public function testRemoveScriptsTrue()
    {
        $dom = new Dom;
        $dom->setOptions([
            'removeScripts' => true,
        ]);
        $dom->loadFromFile('tests/files/horrible.html');
        $this->assertEquals(0, count($dom->find('script')));
    }

    public function testRemoveScriptsFalse()
    {
        $dom = new Dom;
        $dom->setOptions([
            'removeScripts' => false,
        ]);
        $dom->loadFromFile('tests/files/horrible.html');
        $this->assertEquals(1, count($dom->find('script')));
        $this->assertEquals('text/JavaScript',
            $dom->find('script')->getAttribute('type'));
    }

}
