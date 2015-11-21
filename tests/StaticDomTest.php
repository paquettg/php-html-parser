<?php

use PHPHtmlParser\StaticDom;

class StaticDomTest extends PHPUnit_Framework_TestCase {

	public function setUp()
	{
		StaticDom::mount();
	}

	public function tearDown()
	{
		StaticDom::unload();
	}

	public function testMountWithDom()
	{
		$dom = new PHPHtmlParser\Dom;
		StaticDom::unload();
		$status = StaticDom::mount('newDom', $dom);
		$this->assertTrue($status);
	}

	public function testLoad()
	{
		$dom = Dom::load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
		$div = $dom->find('div', 0);
		$this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>', $div->outerHtml);
	}

	public function testLoadWithFile()
	{
		$dom = Dom::load('tests/files/small.html');
		$this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
	}

	public function testFind()
	{
		Dom::load('tests/files/horrible.html');
		$this->assertEquals('<input type="submit" tabindex="0" name="submit" value="Информации" />', Dom::find('table input', 1)->outerHtml);
	}

  public function testSpaceInOpeningTag()
  {
    $dom = Dom::load('<tr ><td >cell 1 1</td><td >cell 2 1</td></tr><tr ><td>cell 1 2</td><td></td></tr>');
    $this->assertEquals(2, count($dom->find('tr')));
  }

  public function testTabInOpeningTag()
  {
    $dom = Dom::load("<tr\t><td >cell 1 1</td><td\t>cell 2 1</td></tr><tr\t><td>cell 1 2</td><td></td></tr>");
    $this->assertEquals(2, count($dom->find('tr')));
  }

  public function testLineFeedInOpeningTag()
  {
    $dom = Dom::load("<tr\n><td >cell 1 1</td><td\n>cell 2 1</td></tr><tr\n><td>cell 1 2</td><td></td></tr>");
    $this->assertEquals(2, count($dom->find('tr')));
  }

  public function testCarriageReturnInOpeningTag()
  {
    $dom = Dom::load("<tr\r><td >cell 1 1</td><td\r>cell 2 1</td></tr><tr\r><td>cell 1 2</td><td></td></tr>");
    $this->assertEquals(2, count($dom->find('tr')));
  }

	/**
	 * @expectedException PHPHtmlParser\Exceptions\NotLoadedException
	 */
	public function testFindNoLoad()
	{
		Dom::find('.post-user font', 0);
	}

	public function testFindI()
	{
		Dom::load('tests/files/horrible.html');
		$this->assertEquals('[ Досие бр:12928 ]', Dom::find('i')[0]->innerHtml);
	}
}
