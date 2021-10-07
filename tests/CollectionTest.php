<?php

declare(strict_types=1);

use PHPHtmlParser\Dom\Node\Collection;
use PHPHtmlParser\Dom\Node\HtmlNode;
use PHPHtmlParser\Dom\Tag;
use PHPHtmlParser\Selector\Parser;
use PHPHtmlParser\Selector\Selector;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testEach()
    {
        $root = new HtmlNode(new Tag('root'));
        $parent = new HtmlNode(new Tag('div'));
        $child1 = new HtmlNode(new Tag('a'));
        $child2 = new HtmlNode(new Tag('p'));
        $child3 = new HtmlNode(new Tag('a'));
        $root->addChild($parent);
        $parent->addChild($child1);
        $parent->addChild($child2);
        $child2->addChild($child3);

        $selector = new Selector('a', new Parser());
        $collection = $selector->find($root);
        $count = 0;
        $collection->each(function ($node) use (&$count) {
            ++$count;
        });
        $this->assertEquals(2, $count);
    }

    public function testCallNoNodes()
    {
        $this->expectException(\PHPHtmlParser\Exceptions\EmptyCollectionException::class);
        $collection = new Collection();
        $collection->innerHtml();
    }

    public function testNoNodeString()
    {
        $collection = new Collection();
        $string = (string) $collection;
        $this->assertEmpty($string);
    }

    public function testCallMagic()
    {
        $root = new HtmlNode(new Tag('root'));
        $parent = new HtmlNode(new Tag('div'));
        $child1 = new HtmlNode(new Tag('a'));
        $child2 = new HtmlNode(new Tag('p'));
        $child3 = new HtmlNode(new Tag('a'));
        $root->addChild($parent);
        $parent->addChild($child1);
        $parent->addChild($child2);
        $child2->addChild($child3);

        $selector = new Selector('div * a', new Parser());
        $this->assertEquals($child3->id(), $selector->find($root)->id());
    }

    public function testGetMagic()
    {
        $root = new HtmlNode(new Tag('root'));
        $parent = new HtmlNode(new Tag('div'));
        $child1 = new HtmlNode(new Tag('a'));
        $child2 = new HtmlNode(new Tag('p'));
        $child3 = new HtmlNode(new Tag('a'));
        $root->addChild($parent);
        $parent->addChild($child1);
        $parent->addChild($child2);
        $child2->addChild($child3);

        $selector = new Selector('div * a', new Parser());
        $this->assertEquals($child3->innerHtml, $selector->find($root)->innerHtml);
    }

    public function testGetNoNodes()
    {
        $this->expectException(\PHPHtmlParser\Exceptions\EmptyCollectionException::class);
        $collection = new Collection();
        $collection->innerHtml;
    }

    public function testToStringMagic()
    {
        $root = new HtmlNode(new Tag('root'));
        $parent = new HtmlNode(new Tag('div'));
        $child1 = new HtmlNode(new Tag('a'));
        $child2 = new HtmlNode(new Tag('p'));
        $child3 = new HtmlNode(new Tag('a'));
        $root->addChild($parent);
        $parent->addChild($child1);
        $parent->addChild($child2);
        $child2->addChild($child3);

        $selector = new Selector('div * a', new Parser());
        $this->assertEquals((string) $child3, (string) $selector->find($root));
    }

    public function testToArray()
    {
        $root = new HtmlNode(new Tag('root'));
        $parent = new HtmlNode(new Tag('div'));
        $child1 = new HtmlNode(new Tag('a'));
        $child2 = new HtmlNode(new Tag('p'));
        $child3 = new HtmlNode(new Tag('a'));
        $root->addChild($parent);
        $parent->addChild($child1);
        $parent->addChild($child2);
        $child2->addChild($child3);

        $selector = new Selector('a', new Parser());
        $collection = $selector->find($root);
        $array = $collection->toArray();
        $lastA = \end($array);
        $this->assertEquals($child3->id(), $lastA->id());
    }

    public function testGetIterator()
    {
        $collection = new Collection();
        $iterator = $collection->getIterator();
        $this->assertTrue($iterator instanceof \ArrayIterator);
    }

    public function testOffsetSet()
    {
        $collection = new Collection();
        $collection->offsetSet(7, true);
        $this->assertTrue($collection->offsetGet(7));
    }

    public function testOffsetUnset()
    {
        $collection = new Collection();
        $collection->offsetSet(7, true);
        $collection->offsetUnset(7);
        $this->assertTrue(\is_null($collection->offsetGet(7)));
    }
}
