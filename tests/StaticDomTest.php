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
        $dom = StaticDom::load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        $div = $dom->find('div', 0);
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>', $div->outerHtml);
    }

    public function testLoadWithFile()
    {
        $dom = StaticDom::load(__DIR__ . '/files/small.html');
        $this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
    }

    public function testLoadFromFile()
    {
        $dom = StaticDom::loadFromFile(__DIR__ . '/files/small.html');
        $this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
    }

    public function testFind()
    {
        StaticDom::load(__DIR__ . '/files/horrible.html');
        $this->assertEquals('<input type="submit" tabindex="0" name="submit" value="Информации" />', Dom::find('table input', 1)->outerHtml);
    }

    /**
     * @expectedException PHPHtmlParser\Exceptions\NotLoadedException
     */
    public function testFindNoLoad()
    {
        StaticDom::find('.post-user font', 0);
    }

    public function testFindI()
    {
        StaticDom::load(__DIR__ . '/files/horrible.html');
        $this->assertEquals('[ Досие бр:12928 ]', StaticDom::find('i')[0]->innerHtml);
    }

    public function testLoadFromUrl()
    {
        $curl = Mockery::mock('PHPHtmlParser\CurlInterface');
        $curl->shouldReceive('get')
             ->once()
             ->with('http://google.com')
             ->andReturn(file_get_contents(__DIR__ . '/files/small.html'));

        StaticDom::loadFromUrl('http://google.com', [], $curl);
        $this->assertEquals('VonBurgermeister', StaticDom::find('.post-row div .post-user font', 0)->text);
    }

}
