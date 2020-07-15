<?php

declare(strict_types=1);

use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;
use PHPUnit\Framework\TestCase;

class NoSlashTest extends TestCase
{
    public function testLoadClosingTagOnSelfClosingNoSlash()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->addNoSlashTag('br'));

        $dom->loadStr('<div class="all"><br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></br></div>');
        $this->assertEquals('<br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadClosingTagOnSelfClosingRemoveNoSlash()
    {
        $dom = new Dom();
        $dom->setOptions(
            (new Options())
                ->addNoSlashTag('br')
                ->removeNoSlashTag('br')
        );

        $dom->loadStr('<div class="all"><br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></br></div>');
        $this->assertEquals('<br /><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadClosingTagOnSelfClosingClearNoSlash()
    {
        $dom = new Dom();
        $dom->setOptions(
            (new Options())
                ->addNoSlashTag('br')
                ->clearNoSlashTags()
        );

        $dom->loadStr('<div class="all"><br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></br></div>');
        $this->assertEquals('<br /><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p>', $dom->find('div', 0)->innerHtml);
    }
}
