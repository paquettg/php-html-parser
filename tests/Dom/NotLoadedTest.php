<?php

declare(strict_types=1);

use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPUnit\Framework\TestCase;

class NotLoadedTest extends TestCase
{
    /**
     * @var Dom
     */
    private $dom;

    public function setUp()
    {
        $dom = new Dom();
        $this->dom = $dom;
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testNotLoaded()
    {
        $this->expectException(NotLoadedException::class);
        $div = $this->dom->find('div', 0);
    }
}
