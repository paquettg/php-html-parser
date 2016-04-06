<?php

use PHPHtmlParser\Selector;
use PHPHtmlParser\Dom\HtmlNode;
use PHPHtmlParser\Dom\Tag;
use PHPHtmlParser\Dom\Collection;

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

    /**
     * @expectedException PHPHtmlParser\Exceptions\EmptyCollectionException
     */
    public function testCallNoNodes()
    {
        $collection = new Collection();
        $collection->innerHtml();
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

    public function testGetMagic()
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
        $this->assertEquals($child3->innerHtml, $selector->find($root)->innerHtml);
    }

    /**
     * @expectedException PHPHtmlParser\Exceptions\EmptyCollectionException
     */
    public function testGetNoNodes()
    {
        $collection = new Collection();
        $collection->innerHtml;
    }

    public function testToStringMagic()
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
        $this->assertEquals((string)$child3, (string)$selector->find($root));
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
