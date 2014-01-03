<?php

use PHPHtmlParser\Selector;
use PHPHtmlParser\Dom\HtmlNode;
use PHPHtmlParser\Dom\Tag;

class CollectionTest extends PHPUnit_Framework_TestCase {
	
	public function testEach()
	{
		$root   = new HtmlNode(new Tag('root'));
		$parent = new HtmlNode(new Tag('div'));
		$child1 = new HtmlNode(new Tag('a'));
		$child2 = new HtmlNode(new Tag('p'));
		$child3 = new HtmlNode(new Tag('a'));
		$root->addChild($parent);
		$parent->addChild($child1);
		$parent->addChild($child2);
		$child2->addChild($child3);

		$selector   = new Selector('a');
		$collection = $selector->find($root);
		$count      = 0;
		$collection->each(function ($node) use (&$count) {
			++$count;
		});
		$this->assertEquals(2, $count);
	}

	public function testCallMagic()
	{
		$root   = new HtmlNode(new Tag('root'));
		$parent = new HtmlNode(new Tag('div'));
		$child1 = new HtmlNode(new Tag('a'));
		$child2 = new HtmlNode(new Tag('p'));
		$child3 = new HtmlNode(new Tag('a'));
		$root->addChild($parent);
		$parent->addChild($child1);
		$parent->addChild($child2);
		$child2->addChild($child3);

		$selector = new Selector('div * a');
		$this->assertEquals($child3->id(), $selector->find($root)->id());
	}

	public function testToArray()
	{
		$root   = new HtmlNode(new Tag('root'));
		$parent = new HtmlNode(new Tag('div'));
		$child1 = new HtmlNode(new Tag('a'));
		$child2 = new HtmlNode(new Tag('p'));
		$child3 = new HtmlNode(new Tag('a'));
		$root->addChild($parent);
		$parent->addChild($child1);
		$parent->addChild($child2);
		$child2->addChild($child3);

		$selector   = new Selector('a');
		$collection = $selector->find($root);
		$array      = $collection->toArray();
		$lastA      = end($array);
		$this->assertEquals($child3->id(), $lastA->id());
	}
}
