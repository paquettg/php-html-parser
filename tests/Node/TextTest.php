<?php

use PHPHtmlParser\Dom\TextNode;
use Mockery as m;

class NodeTextTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}

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
}
