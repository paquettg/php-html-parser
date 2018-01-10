<?php

use PHPHtmlParser\Dom;
use PHPUnit\Framework\TestCase;

class PreserveLineBreaks extends TestCase {

    public function testPreserveLineBreakTrue()
    {
        $dom = new Dom;
        $dom->setOptions([
            'preserveLineBreaks' => true,
        ]);
        $dom->load("<div class=\"stream-container \">
<div class=\"stream-item js-new-items-bar-container\"> </div> <div class=\"stream\">");

        $this->assertEquals("<div class=\"stream-container \">\n<div class=\"stream-item js-new-items-bar-container\"> </div> <div class=\"stream\"></div></div>", (string) $dom);
    }

    public function testPreserveLineBreakBeforeClosingTag()
    {
        $dom = new Dom;
        $dom->setOptions([
            'preserveLineBreaks' => true,
        ]);
        $dom->load("<div class=\"stream-container \"
 ><div class=\"stream-item js-new-items-bar-container\"> </div> <div class=\"stream\">");

        $this->assertEquals("<div class=\"stream-container \"><div class=\"stream-item js-new-items-bar-container\"> </div> <div class=\"stream\"></div></div>", (string) $dom);
    }
}
