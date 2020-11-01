<?php

declare(strict_types=1);

use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    /**
     * @var Dom
     */
    private $dom;

    public function setUp()
    {
        $dom = new Dom();
        $options = new Options();
        $options->setCleanupInput(false);
        $dom->loadStr('<!-- test comment with number 2 -->', $options);
        $this->dom = $dom;
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testLoadCommentInnerHtml()
    {
        $this->assertEquals('<!-- test comment with number 2 -->', $this->dom->innerHtml);
    }
}
