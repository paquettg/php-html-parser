<?php

declare(strict_types=1);

use PHPHtmlParser\Dom\Node\HtmlNode;
use PHPHtmlParser\Dom\Tag;
use PHPHtmlParser\Selector\Parser;
use PHPHtmlParser\Selector\Selector;
use PHPUnit\Framework\TestCase;

class SelectorTest extends TestCase
{
    public function testParseSelectorStringId()
    {
        $selector = new Selector('#all', new Parser());
        $selectors = $selector->getParsedSelectorCollectionDTO();
        $this->assertEquals('id', $selectors->getParsedSelectorDTO()[0]->getRules()[0]->getKey());
    }

    public function testParseSelectorStringClass()
    {
        $selector = new Selector('div.post', new Parser());
        $selectors = $selector->getParsedSelectorCollectionDTO();
        $this->assertEquals('class', $selectors->getParsedSelectorDTO()[0]->getRules()[0]->getKey());
    }

    public function testParseSelectorStringAttribute()
    {
        $selector = new Selector('div[visible=yes]', new Parser());
        $selectors = $selector->getParsedSelectorCollectionDTO();
        $this->assertEquals('yes', $selectors->getParsedSelectorDTO()[0]->getRules()[0]->getValue());
    }

    public function testParseSelectorStringNoKey()
    {
        $selector = new Selector('div[!visible]', new Parser());
        $selectors = $selector->getParsedSelectorCollectionDTO();
        $this->assertTrue($selectors->getParsedSelectorDTO()[0]->getRules()[0]->isNoKey());
    }

    public function testFind()
    {
        $root = new HtmlNode('root');
        $parent = new HtmlNode('div');
        $child1 = new HtmlNode('a');
        $child2 = new HtmlNode('p');
        $parent->addChild($child1);
        $parent->addChild($child2);
        $root->addChild($parent);

        $selector = new Selector('div a', new Parser());
        $this->assertEquals($child1->id(), $selector->find($root)[0]->id());
    }

    public function testFindId()
    {
        $parent = new HtmlNode(new Tag('div'));
        $child1 = new HtmlNode(new Tag('a'));
        $child2 = new HtmlNode(new Tag('p'));
        $child2->getTag()->setAttributes([
            'id' => [
                'value' => 'content',
                'doubleQuote' => true,
            ],
        ]);
        $parent->addChild($child1);
        $parent->addChild($child2);

        $selector = new Selector('#content', new Parser());
        $this->assertEquals($child2->id(), $selector->find($parent)[0]->id());
    }

    public function testFindClass()
    {
        $parent = new HtmlNode(new Tag('div'));
        $child1 = new HtmlNode(new Tag('a'));
        $child2 = new HtmlNode(new Tag('p'));
        $child3 = new HtmlNode('a');
        $child3->getTag()->setAttributes([
            'class' => [
                'value' => 'link',
                'doubleQuote' => true,
            ],
        ]);
        $parent->addChild($child1);
        $parent->addChild($child2);
        $parent->addChild($child3);

        $selector = new Selector('.link', new Parser());
        $this->assertEquals($child3->id(), $selector->find($parent)[0]->id());
    }

    public function testFindClassMultiple()
    {
        $parent = new HtmlNode(new Tag('div'));
        $child1 = new HtmlNode(new Tag('a'));
        $child2 = new HtmlNode(new Tag('p'));
        $child3 = new HtmlNode(new Tag('a'));
        $child3->getTag()->setAttributes([
            'class' => [
                'value' => 'link outer',
                'doubleQuote' => false,
            ],
        ]);
        $parent->addChild($child1);
        $parent->addChild($child2);
        $parent->addChild($child3);

        $selector = new Selector('.outer', new Parser());
        $this->assertEquals($child3->id(), $selector->find($parent)[0]->id());
    }

    public function testFindWild()
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
        $this->assertEquals($child3->id(), $selector->find($root)[0]->id());
    }

    public function testFindMultipleSelectors()
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

        $selector = new Selector('a, p', new Parser());
        $this->assertEquals(3, \count($selector->find($root)));
    }

    public function testFindXpathKeySelector()
    {
        $parent = new HtmlNode(new Tag('div'));
        $child1 = new HtmlNode(new Tag('a'));
        $child2 = new HtmlNode(new Tag('p'));
        $child3 = new HtmlNode(new Tag('a'));
        $child3->getTag()->setAttributes([
            'class' => [
                'value' => 'link outer',
                'doubleQuote' => false,
            ],
        ]);
        $parent->addChild($child1);
        $parent->addChild($child2);
        $parent->addChild($child3);

        $selector = new Selector('div[1]', new Parser());
        $this->assertEquals($parent->id(), $selector->find($parent)[0]->id());
    }

    public function testFindChildMultipleLevelsDeep()
    {
        $root = new HtmlNode(new Tag('root'));
        $parent = new HtmlNode(new Tag('div'));
        $child1 = new HtmlNode(new Tag('ul'));
        $child2 = new HtmlNode(new Tag('li'));
        $root->addChild($parent);
        $parent->addChild($child1);
        $child1->addChild($child2);

        $selector = new Selector('div li', new Parser());
        $this->assertEquals(1, \count($selector->find($root)));
    }

    public function testFindAllChildren()
    {
        $root = new HtmlNode(new Tag('root'));
        $parent = new HtmlNode(new Tag('div'));
        $child1 = new HtmlNode(new Tag('ul'));
        $child2 = new HtmlNode(new Tag('span'));
        $child3 = new HtmlNode(new Tag('ul'));
        $root->addChild($parent);
        $parent->addChild($child1);
        $child2->addChild($child3);
        $parent->addChild($child2);

        $selector = new Selector('div ul', new Parser());
        $this->assertEquals(2, \count($selector->find($root)));
    }

    public function testFindChildUsingChildSelector()
    {
        $root = new HtmlNode(new Tag('root'));
        $parent = new HtmlNode(new Tag('div'));
        $child1 = new HtmlNode(new Tag('ul'));
        $child2 = new HtmlNode(new Tag('span'));
        $child3 = new HtmlNode(new Tag('ul'));
        $root->addChild($parent);
        $parent->addChild($child1);
        $child2->addChild($child3);
        $parent->addChild($child2);

        $selector = new Selector('div > ul', new Parser());
        $this->assertEquals(1, \count($selector->find($root)));
    }

    public function testFindNodeByAttributeOnly()
    {
        $root = new HtmlNode(new Tag('root'));
        $child1 = new HtmlNode(new Tag('ul'));
        $child1->setAttribute('custom-attr', null);
        $root->addChild($child1);

        $selector = new Selector('[custom-attr]', new Parser());
        $this->assertEquals(1, \count($selector->find($root)));
    }

    public function testFindMultipleClasses()
    {
        $root = new HtmlNode(new Tag('root'));
        $child1 = new HtmlNode(new Tag('a'));
        $child1->setAttribute('class', 'b');
        $child2 = new HtmlNode(new Tag('a'));
        $child2->setAttribute('class', 'b c');
        $root->addChild($child1);
        $root->addChild($child2);

        $selector = new Selector('a.b.c', new Parser());
        $this->assertEquals(1, \count($selector->find($root)));
    }
}
