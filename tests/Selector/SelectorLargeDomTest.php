<?php declare(strict_types=1);

use PHPHtmlParser\Selector\Selector;
use PHPUnit\Framework\TestCase;
use PHPHtmlParser\Dom\HtmlNode;
use PHPHtmlParser\Dom\Tag;

class SelectorLargeDomTest extends TestCase {

    const DivCount = 10000;

    private static $parent;
    private $time;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        $div = new Tag('div');
        $parent = new HtmlNode($div);

        for ($i=0; $i<self::DivCount; $i++) {
            $div = new Tag('div');
            $div->setAttribute("data-index", (string)$i);
            $parent->addChild(new HtmlNode($div));
        }

        self::$parent = $parent;
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        self::$parent = null;
    }

    protected function setUp() {
        parent::setUp();
        $this->time = microtime(true);
    }

    protected function tearDown() {
        parent::tearDown();
        // echo microtime(true) - $this->time."\n";
    }

    public function testGetAllDivs() {
        $result = self::$parent->find("div");
        $this->assertInstanceOf(\PHPHtmlParser\Dom\Collection::class, $result);
        $this->assertEquals(self::DivCount, count($result));
    }

    public function testGetFirstDiv() {
        $result = self::$parent->find("div", 0, true);
        $this->assertInstanceOf(\PHPHtmlParser\Dom\AbstractNode::class, $result);
        $this->assertEquals("0", $result->getAttribute("data-index"));
    }

    public function testGet10thDiv() {
        $result = self::$parent->find("div", 10, true);
        $this->assertInstanceOf(\PHPHtmlParser\Dom\AbstractNode::class, $result);
        $this->assertEquals("9", $result->getAttribute("data-index"));
    }
}
