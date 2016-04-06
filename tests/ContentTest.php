<?php

use PHPHtmlParser\Content;

class ContentTest extends PHPUnit_Framework_TestCase {

    public function testChar()
    {
        $content = new Content('abcde');
        $this->assertEquals('a', $content->char());
    }

    public function testCharSelection()
    {
        $content = new Content('abcde');
        $this->assertEquals('d', $content->char(3));
    }

    public function testFastForward()
    {
        $content = new Content('abcde');
        $content->fastForward(2);
        $this->assertEquals('c', $content->char());
    }

    public function testRewind()
    {
        $content = new Content('abcde');
        $content->fastForward(2)
                ->rewind(1);
        $this->assertEquals('b', $content->char());
    }

    public function testRewindNegative()
    {
        $content = new Content('abcde');
        $content->fastForward(2)
                ->rewind(100);
        $this->assertEquals('a', $content->char());
    }

    public function testCopyUntil()
    {
        $content = new Content('abcdeedcba');
        $this->assertEquals('abcde', $content->copyUntil('ed'));
    }

    public function testCopyUntilChar()
    {
        $content = new Content('abcdeedcba');
        $this->assertEquals('ab', $content->copyUntil('edc', true));
    }

    public function testCopyUntilEscape()
    {
        $content = new Content('foo\"bar"bax');
        $this->assertEquals('foo\"bar', $content->copyUntil('"', false, true));
    }

    public function testCopyUntilNotFound()
    {
        $content = new Content('foo\"bar"bax');
        $this->assertEquals('foo\"bar"bax', $content->copyUntil('baz'));
    }

    public function testCopyByToken()
    {
        $content = new Content('<a href="google.com">');
        $content->fastForward(3);
        $this->assertEquals('href="google.com"', $content->copyByToken('attr', true));
    }

    public function testSkip()
    {
        $content = new Content('abcdefghijkl');
        $content->skip('abcd');
        $this->assertEquals('e', $content->char());
    }

    public function testSkipCopy()
    {
        $content = new Content('abcdefghijkl');
        $this->assertEquals('abcd', $content->skip('abcd', true));
    }

    public function testSkipByToken()
    {
        $content = new Content(' b c');
        $content->fastForward(1);
        $content->skipByToken('blank');
        $this->assertEquals('b', $content->char());
    }

}
