<?php

declare(strict_types=1);

use PHPHtmlParser\Dom\Tag;
use PHPUnit\Framework\TestCase;

class NodeTagTest extends TestCase
{
    public function testSelfClosing()
    {
        $tag = new Tag('a');
        $tag->selfClosing();
        $this->assertTrue($tag->isSelfClosing());
    }

    public function testSetAttributes()
    {
        $attr = [
            'href' => [
                'value'       => 'http://google.com',
                'doubleQuote' => false,
            ],
        ];

        $tag = new Tag('a');
        $tag->setAttributes($attr);
        $this->assertEquals('http://google.com', $tag->getAttribute('href')->getValue());
    }

    public function testRemoveAttribute()
    {
        $this->expectException(\PHPHtmlParser\Exceptions\Tag\AttributeNotFoundException::class);
        $tag = new Tag('a');
        $tag->setAttribute('href', 'http://google.com');
        $tag->removeAttribute('href');
        $tag->getAttribute('href');
    }

    public function testRemoveAllAttributes()
    {
        $tag = new Tag('a');
        $tag->setAttribute('href', 'http://google.com');
        $tag->setAttribute('class', 'clear-fix', true);
        $tag->removeAllAttributes();
        $this->assertEquals(0, \count($tag->getAttributes()));
    }

    public function testSetAttributeNoArray()
    {
        $tag = new Tag('a');
        $tag->setAttribute('href', 'http://google.com');
        $this->assertEquals('http://google.com', $tag->getAttribute('href')->getValue());
    }

    public function testSetAttributesNoDoubleArray()
    {
        $attr = [
            'href'  => 'http://google.com',
            'class' => 'funtimes',
        ];

        $tag = new Tag('a');
        $tag->setAttributes($attr);
        $this->assertEquals('funtimes', $tag->getAttribute('class')->getValue());
    }

    public function testUpdateAttributes()
    {
        $tag = new Tag('a');
        $tag->setAttributes([
            'href' => [
                'value'       => 'http://google.com',
                'doubleQuote' => false,
            ],
            'class' => [
                'value'       => null,
                'doubleQuote' => true,
            ],
        ]);

        $this->assertEquals(null, $tag->getAttribute('class')->getValue());
        $this->assertEquals('http://google.com', $tag->getAttribute('href')->getValue());

        $attr = [
            'href'  => 'https://www.google.com',
            'class' => 'funtimes',
        ];

        $tag->setAttributes($attr);
        $this->assertEquals('funtimes', $tag->getAttribute('class')->getValue());
        $this->assertEquals('https://www.google.com', $tag->getAttribute('href')->getValue());
    }

    public function testNoise()
    {
        $tag = new Tag('a');
        $this->assertTrue($tag->noise('noise') instanceof Tag);
    }

    public function testGetAttributeMagic()
    {
        $attr = [
            'href' => [
                'value'       => 'http://google.com',
                'doubleQuote' => false,
            ],
        ];

        $tag = new Tag('a');
        $tag->setAttributes($attr);
        $this->assertEquals('http://google.com', $tag->getAttribute('href')->getValue());
    }

    public function testSetAttributeMagic()
    {
        $tag = new Tag('a');
        $tag->setAttribute('href', 'http://google.com');
        $this->assertEquals('http://google.com', $tag->getAttribute('href')->getValue());
    }

    public function testMakeOpeningTag()
    {
        $attr = [
            'href' => [
                'value'       => 'http://google.com',
                'doubleQuote' => true,
            ],
        ];

        $tag = new Tag('a');
        $tag->setAttributes($attr);
        $this->assertEquals('<a href="http://google.com">', $tag->makeOpeningTag());
    }

    public function testMakeOpeningTagEmptyAttr()
    {
        $attr = [
            'href' => [
                'value'       => 'http://google.com',
                'doubleQuote' => true,
            ],
        ];

        $tag = new Tag('a');
        $tag->setAttributes($attr);
        $tag->setAttribute('selected', null);
        $this->assertEquals('<a href="http://google.com" selected>', $tag->makeOpeningTag());
    }

    public function testMakeOpeningTagSelfClosing()
    {
        $attr = [
            'class' => [
                'value'       => 'clear-fix',
                'doubleQuote' => true,
            ],
        ];

        $tag = (new Tag('div'))
            ->selfClosing()
            ->setAttributes($attr);
        $this->assertEquals('<div class="clear-fix" />', $tag->makeOpeningTag());
    }

    public function testMakeClosingTag()
    {
        $tag = new Tag('a');
        $this->assertEquals('</a>', $tag->makeClosingTag());
    }

    public function testMakeClosingTagSelfClosing()
    {
        $tag = new Tag('div');
        $tag->selfClosing();
        $this->assertEmpty($tag->makeClosingTag());
    }

    public function testSetTagAttribute()
    {
        $tag = new Tag('div');
        $tag->setStyleAttributeValue('display', 'none');
        $this->assertEquals('display:none;', $tag->getAttribute('style')->getValue());
    }

    public function testGetStyleAttributesArray()
    {
        $tag = new Tag('div');
        $tag->setStyleAttributeValue('display', 'none');
        $this->assertIsArray($tag->getStyleAttributeArray());
    }
}
