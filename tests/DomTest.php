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

	public function testLoadNoOpeningTag()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><font color="red"><strong>PR Manager</strong></font></b><div class="content">content</div></div>');
		$this->assertEquals('content', $dom->find('.content', 0)->text);
	}

	public function testLoadNoClosingTag()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br />');
		$root = $dom->find('div', 0)->getParent();
		$this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div><br />', $root->outerHtml);
	}

	public function testLoadAttributeOnSelfClosing()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br class="both" />');
		$br = $dom->find('br', 0);
		$this->assertEquals('both', $br->getAttribute('class'));
	}

	public function testLoadClosingTagOnSelfClosing()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></br></div>');
		$this->assertEquals('<br /><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p>', $dom->find('div', 0)->innerHtml);
	}

	public function testLoadUpperCase()
	{
		$dom = new Dom;
		$dom->load('<DIV CLASS="ALL"><BR><P>hEY BRO, <A HREF="GOOGLE.COM" DATA-QUOTE="\"">CLICK HERE</A></BR></DIV>');
		$this->assertEquals('<br /><p>hEY BRO, <a href="GOOGLE.COM" data-quote="\"">CLICK HERE</a></p>', $dom->find('div', 0)->innerHtml);
	}

	public function testLoadWithFile()
	{
		$dom = new Dom;
		$dom->loadFromFile('tests/small.html');
		$this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
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

	public function testLoadFileBigTwice()
	{
		$dom = new Dom;
		$dom->loadFromFile('tests/big.html');
		$post = $dom->find('.post-row', 0);
		$this->assertEquals(' <p>Журчанье воды<br /> Черно-белые тени<br /> Вновь на фонтане</p> ', $post->find('.post-message', 0)->innerHtml);
	}

	public function testToStringMagic()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
		$this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>', (string) $dom);
	}

	public function testGetMagic()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
		$this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>', $dom->innerHtml);
	}

	public function testFirstChild()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br />');
		$this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div>', $dom->firstChild()->outerHtml);
	}

	public function testLastChild()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br />');
		$this->assertEquals('<br />', $dom->lastChild()->outerHtml);
	}

	public function testGetElementById()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><p>Hey bro, <a href="google.com" id="78">click here</a></div><br />');
		$this->assertEquals('<a href="google.com" id="78">click here</a>', $dom->getElementById('78')->outerHtml);
	}

	public function testGetElementsByTag()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><p>Hey bro, <a href="google.com" id="78">click here</a></div><br />');
		$this->assertEquals('<p>Hey bro, <a href="google.com" id="78">click here</a></p>', $dom->getElementsByTag('p')[0]->outerHtml);
	}

	public function testGetElementsByClass()
	{
		$dom = new Dom;
		$dom->load('<div class="all"><p>Hey bro, <a href="google.com" id="78">click here</a></div><br />');
		$this->assertEquals('<p>Hey bro, <a href="google.com" id="78">click here</a></p>', $dom->getElementsByClass('all')[0]->innerHtml);
	}
}
