<?php

use PHPHtmlParser\Dom\TextNode;
use PHPUnit\Framework\TestCase;

class NodeTextTest extends TestCase {

    public function testText()
    {
        $node = new TextNode('foo bar');
        $this->assertEquals('foo bar', $node->text());
    }

    public function testGetTag()
    {
        $node = new TextNode('foo bar');
        $this->assertEquals('text', $node->getTag()->name());
    }

    public function testAncestorByTag()
    {
        $node = new TextNode('foo bar');
        $text = $node->ancestorByTag('text');
        $this->assertEquals($node, $text);
    }

    public function testPreserveEntity()
    {
        $node = new TextNode('&#x69;');
        $text = $node->innerhtml;
        $this->assertEquals('&#x69;', $text);
    }
}
