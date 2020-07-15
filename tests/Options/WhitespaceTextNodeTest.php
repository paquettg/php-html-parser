<?php

declare(strict_types=1);

use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;
use PHPUnit\Framework\TestCase;

class WhitespaceTextNodeTest extends TestCase
{
    public function testConfigGlobalNoWhitespaceTextNode()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setWhitespaceTextNode(false));
        $dom->loadStr('<div><p id="hey">Hey you</p> <p id="ya">Ya you!</p></div>');
        $this->assertEquals('Ya you!', $dom->getElementById('hey')->nextSibling()->text);
    }

    public function testConfigLocalOverride()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setWhitespaceTextNode(false));
        $dom->loadStr('<div><p id="hey">Hey you</p> <p id="ya">Ya you!</p></div>', (new Options())->setWhitespaceTextNode(true));
        $this->assertEquals(' ', $dom->getElementById('hey')->nextSibling()->text);
    }
}
