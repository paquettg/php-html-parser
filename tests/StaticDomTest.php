<?php

use PHPHtmlParser\StaticDom;
use PHPUnit\Framework\TestCase;

class StaticDomTest extends TestCase {

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

    public function testLoadFromFile()
    {
        $dom = Dom::loadFromFile('tests/files/small.html');
        $this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
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
        Dom::load('tests/files/big.html');
        $this->assertEquals('В кустах блестит металл<br /> И искрится ток<br /> Человечеству конец', Dom::find('i')[1]->innerHtml);
    }

    public function testLoadFromUrl()
    {
        $curl = Mockery::mock('PHPHtmlParser\CurlInterface');
        $curl->shouldReceive('get')
             ->once()
             ->with('http://google.com')
             ->andReturn(file_get_contents('tests/files/small.html'));

        Dom::loadFromUrl('http://google.com', [], $curl);
        $this->assertEquals('VonBurgermeister', Dom::find('.post-row div .post-user font', 0)->text);
    }

}
