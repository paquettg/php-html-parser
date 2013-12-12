<?php

use PHPHtmlParser\Dom;

class DomTest extends PHPUnit_Framework_TestCase {

	public function testLoad()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
		$div = $dom->find('div', 0);
		$this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>', $div->outerHtml);
	}

	public function testLoadSelfclosingAttr()
	{
		$dom = new Dom;
		$dom->load("<div class='all'><br  foo  bar  />baz</div>");
		$br = $dom->find('br', 0);
		$this->assertEquals('<br foo bar />', $br->outerHtml);
	}

	public function testLoadEscapeQuotes()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div>');
		$div = $dom->find('div', 0);
		$this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div>', $div->outerHtml);
	}

	public function testLoadNoClosingTag()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br />');
		$root = $dom->find('div', 0)->getParent();
		$this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div><br />', $root->outerHtml);
	}

	public function testLoadFromFile()
	{
		$dom = new Dom;
		$dom->loadFromFile('tests/small.html');
		$this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
	}

	public function testLoadFromFileFind()
	{
		$dom = new Dom;
		$dom->loadFromFile('tests/small.html');
		$this->assertEquals('VonBurgermeister', $dom->find('.post-row div .post-user font', 0)->text);
	}

	public function testLoadUtf8()
	{
		$dom = new Dom;
		$dom->load('<p>Dzień</p>');
		$this->assertEquals('Dzień', $dom->find('p', 0)->text);
	}

	public function testLoadFileBig()
	{
		$dom = new Dom;
		$dom->loadFromFile('tests/big.html');
		$this->assertEquals(10, count($dom->find('.content-border')));
	}
}
