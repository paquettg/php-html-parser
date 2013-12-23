<?php

use PHPHtmlParser\StaticDom;

class StaticDomTest extends PHPUnit_Framework_TestCase {

	public function setUp()
	{
		StaticDom::mount();
	}

	public function testLoad()
	{
		$dom = Dom::load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
		$div = $dom->find('div', 0);
		$this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>', $div->outerHtml);
	}

	public function testLoadWithFile()
	{
		$dom = Dom::load('tests/small.html');
		$this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
	}

	public function testFind()
	{
		Dom::load('tests/small.html');
		$this->assertEquals('VonBurgermeister', Dom::find('.post-user font', 0)->text);
	}
}
