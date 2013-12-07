<?php

use PHPHtmlParser\Dom\HtmlNode;
use PHPHtmlParser\Dom\TextNode;
use PHPHtmlParser\Dom\Tag;
use Mockery as m;

class NodeHtmlTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}

	public function testInnerHtml()
	{
		$div = new Tag('div');
		$div->setAttributes([
			'class' => [
				'value'       => 'all',
				'doubleQuote' => true,
			],
		]);
		$a = new Tag('a');
		$a->setAttributes([
			'href' => [
				'value'       => 'http://google.com',
				'doubleQuote' => false,
			],
		]);
		$br = new Tag('br');
		$br->selfClosing();

		$parent  = new HtmlNode($div);
		$childa  = new HtmlNode($a);
		$childbr = new HtmlNode($br);
		$parent->addChild($childa);
		$parent->addChild($childbr);
		$childa->addChild(new TextNode('link'));

		$this->assertEquals("<a href='http://google.com'>link</a><br />", $parent->innerHtml());
	}

	public function testOuterHtml()
	{
		$div = new Tag('div');
		$div->setAttributes([
			'class' => [
				'value'       => 'all',
				'doubleQuote' => true,
			],
		]);
		$a = new Tag('a');
		$a->setAttributes([
			'href' => [
				'value'       => 'http://google.com',
				'doubleQuote' => false,
			],
		]);
		$br = new Tag('br');
		$br->selfClosing();

		$parent  = new HtmlNode($div);
		$childa  = new HtmlNode($a);
		$childbr = new HtmlNode($br);
		$parent->addChild($childa);
		$parent->addChild($childbr);
		$childa->addChild(new TextNode('link'));

		$this->assertEquals('<div class="all"><a href=\'http://google.com\'>link</a><br /></div>', $parent->outerHtml());
	}

	public function testOuterHtmlEmpty()
	{
		$a = new Tag('a');
		$a->setAttributes([
			'href' => [
				'value'       => 'http://google.com',
				'doubleQuote' => false,
			],
		]);
		$node = new HtmlNode($a);
		
		$this->assertEquals("<a href='http://google.com'></a>", $node->OuterHtml());
	}

	public function testText()
	{
		$a    = new Tag('a');
		$node = new HtmlNode($a);
		$node->addChild(new TextNode('link'));
		
		$this->assertEquals('link', $node->text());
	}

	public function testTextNone()
	{
		$a    = new Tag('a');
		$node = new HtmlNode($a);
		
		$this->assertEmpty($node->text());
	}
}
