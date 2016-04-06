<?php


use PHPHtmlParser\Dom\HtmlNode;
use PHPHtmlParser\Dom\TextNode;
use PHPHtmlParser\Dom\MockNode;
use PHPHtmlParser\Dom\Tag;

class NodeHtmlTest extends PHPUnit_Framework_TestCase {

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

    public function testInnerHtmlTwice()
    {
        $div = new Tag('div');
        $div->setAttributes([
            'class' => [
                'value'       => 'all',
                'doubleQuote' => true,
            ],
        ]);
        $a = new Tag('a');
        $br = new Tag('br');
        $br->selfClosing();

        $parent  = new HtmlNode($div);
        $childa  = new HtmlNode($a);
        $childa->setAttribute('href', [
            'value'       => 'http://google.com',
            'doubleQuote' => false,
        ]);
        $childbr = new HtmlNode($br);
        $parent->addChild($childa);
        $parent->addChild($childbr);
        $childa->addChild(new TextNode('link'));

        $inner = $parent->innerHtml();
        $this->assertEquals($inner, $parent->innerHtml());
    }

    /**
     * @expectedException PHPHtmlParser\Exceptions\UnknownChildTypeException
     */
    public function testInnerHtmlUnkownChild()
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
        $childbr = new MockNode($br);
        $parent->addChild($childa);
        $parent->addChild($childbr);
        $childa->addChild(new TextNode('link'));

        $inner = $parent->innerHtml();
        $this->assertEquals($inner, $parent->innerHtml());
    }

    public function testInnerHtmlMagic()
    {
        $parent  = new HtmlNode('div');
        $parent->tag->setAttributes([
            'class' => [
                'value'       => 'all',
                'doubleQuote' => true,
            ],
        ]);
        $childa  = new HtmlNode('a');
        $childa->getTag()->setAttributes([
            'href' => [
                'value'       => 'http://google.com',
                'doubleQuote' => false,
            ],
        ]);
        $childbr = new HtmlNode('br');
        $childbr->getTag()->selfClosing();

        $parent->addChild($childa);
        $parent->addChild($childbr);
        $childa->addChild(new TextNode('link'));

        $this->assertEquals("<a href='http://google.com'>link</a><br />", $parent->innerHtml);
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

    public function testOuterHtmlTwice()
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

        $outer = $parent->outerHtml();
        $this->assertEquals($outer, $parent->outerHtml());
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

    public function testOuterHtmlMagic()
    {
        $parent  = new HtmlNode('div');
        $parent->getTag()->setAttributes([
            'class' => [
                'value'       => 'all',
                'doubleQuote' => true,
            ],
        ]);
        $childa  = new HtmlNode('a');
        $childa->getTag()->setAttributes([
            'href' => [
                'value'       => 'http://google.com',
                'doubleQuote' => false,
            ],
        ]);
        $childbr = new HtmlNode('br');
        $childbr->getTag()->selfClosing();

        $parent->addChild($childa);
        $parent->addChild($childbr);
        $childa->addChild(new TextNode('link'));

        $this->assertEquals('<div class="all"><a href=\'http://google.com\'>link</a><br /></div>', $parent->outerHtml);
    }

    public function testOuterHtmlNoValueAttribute()
    {
        $parent  = new HtmlNode('div');
        $parent->setAttribute('class', [
            'value'       => 'all',
            'doubleQuote' => true,
        ]);
        $childa  = new HtmlNode('a');
        $childa->setAttribute('href', [
            'value'       => 'http://google.com',
            'doubleQuote' => false,
        ]);
        $childa->setAttribute('ui-view', null);
        $childbr = new HtmlNode('br');
        $childbr->getTag()->selfClosing();

        $parent->addChild($childa);
        $parent->addChild($childbr);
        $childa->addChild(new TextNode('link'));

        $this->assertEquals('<div class="all"><a href=\'http://google.com\' ui-view>link</a><br /></div>', $parent->outerHtml);
    }

    public function testText()
    {
        $a    = new Tag('a');
        $node = new HtmlNode($a);
        $node->addChild(new TextNode('link'));
        
        $this->assertEquals('link', $node->text());
    }

    public function testTextTwice()
    {
        $a    = new Tag('a');
        $node = new HtmlNode($a);
        $node->addChild(new TextNode('link'));
        
        $text = $node->text();
        $this->assertEquals($text, $node->text());
    }

    public function testTextNone()
    {
        $a    = new Tag('a');
        $node = new HtmlNode($a);

        $this->assertEmpty($node->text());
    }

    public function testTextMagic()
    {
        $node = new HtmlNode('a');
        $node->addChild(new TextNode('link'));
        
        $this->assertEquals('link', $node->text);
    }

    public function testTextLookInChildren()
    {
        $p = new HtmlNode('p');
        $a = new HtmlNode('a');
        $a->addChild(new TextNode('click me'));
        $p->addChild(new TextNode('Please '));
        $p->addChild($a);
        $p->addChild(new TextNode('!'));
        $node = new HtmlNode('div');
        $node->addChild($p);

        $this->assertEquals('Please click me!', $node->text(true));
    }

    public function testTextLookInChildrenAndNoChildren()
    {
        $p = new HtmlNode('p');
        $a = new HtmlNode('a');
        $a->addChild(new TextNode('click me'));
        $p->addChild(new TextNode('Please '));
        $p->addChild($a);
        $p->addChild(new TextNode('!'));

        $p->text;
        $p->text(true);

        $this->assertEquals('Please click me!', $p->text(true));
    }

    public function testGetAttribute()
    {
        $node = new HtmlNode('a');
        $node->getTag()->setAttributes([
            'href' => [
                'value'       => 'http://google.com',
                'doubleQuote' => false,
            ],
            'class' => [
                'value'       => 'outerlink rounded',
                'doubleQuote' => true,
            ],
        ]);
        
        $this->assertEquals('outerlink rounded', $node->getAttribute('class'));
    }

    public function testGetAttributeMagic()
    {
        $node = new HtmlNode('a');
        $node->getTag()->setAttributes([
            'href' => [
                'value'       => 'http://google.com',
                'doubleQuote' => false,
            ],
            'class' => [
                'value'       => 'outerlink rounded',
                'doubleQuote' => true,
            ],
        ]);
        
        $this->assertEquals('http://google.com', $node->href);
    }

    public function testGetAttributes()
    {
        $node = new HtmlNode('a');
        $node->getTag()->setAttributes([
            'href' => [
                'value'       => 'http://google.com',
                'doubleQuote' => false,
            ],
            'class' => [
                'value'       => 'outerlink rounded',
                'doubleQuote' => true,
            ],
        ]);
        
        $this->assertEquals('outerlink rounded', $node->getAttributes()['class']);
    }

    public function testSetAttribute()
    {
        $node = new HtmlNode('a');
        $node->setAttribute('class', 'foo');
        $this->assertEquals('foo', $node->getAttribute('class'));
    }

    public function testRemoveAttribute()
    {
        $node = new HtmlNode('a');
        $node->setAttribute('class', 'foo');
        $node->removeAttribute('class');
        $this->assertnull($node->getAttribute('class'));
    }

    public function testRemoveAllAttributes()
    {
        $node = new HtmlNode('a');
        $node->setAttribute('class', 'foo');
        $node->setAttribute('href', 'http://google.com');
        $node->removeAllAttributes();
        $this->assertEquals(0, count($node->getAttributes()));
    }

    public function testCountable()
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

        $this->assertEquals(count($parent->getChildren()), count($parent));
    }

    public function testIterator()
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

        $children = 0;
        foreach ($parent as $child) {
            ++$children;
        }
        $this->assertEquals(2, $children);
    }

    /**
     * @expectedException PHPHtmlParser\Exceptions\ParentNotFoundException
     */
    public function testAncestorByTagFailure()
    {
        $a    = new Tag('a');
        $node = new HtmlNode($a);
        $node->ancestorByTag('div');
    }
}
