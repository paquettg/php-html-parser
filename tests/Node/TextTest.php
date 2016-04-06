<?php

use PHPHtmlParser\Dom\TextNode;

class NodeTextTest extends PHPUnit_Framework_TestCase {

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
