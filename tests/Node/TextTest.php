<?php

use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\TextNode;
use stringEncode\Encode;
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

    public function testIsTextNode()
    {
        $node = new TextNode('text');
        $this->assertEquals(true, $node->isTextNode());
    }

    public function testTextInTextNode()
    {
        $node = new TextNode('foo bar');
        $this->assertEquals('foo bar', $node->outerHtml());
    }

    public function testSetTextToTextNode()
    {
        $node = new TextNode('');
        $node->setText('foo bar');
        $this->assertEquals('foo bar', $node->innerHtml());
    }

    public function testSetText()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        $a   = $dom->find('a')[0];
        $a->firstChild()->setText('biz baz');
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">biz baz</a><br /> :)</p></div>', (string) $dom);
    }

    public function testSetTextEncoded()
    {
        $encode = new Encode;
        $encode->from('UTF-8');
        $encode->to('UTF-8');

        $node = new TextNode('foo bar');
        $node->propagateEncoding($encode);
        $node->setText('biz baz');
        $this->assertEquals('biz baz', $node->text());
    }
}