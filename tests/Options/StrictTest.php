<?php

use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\StrictException;

class StrictTest extends PHPUnit_Framework_TestCase {

    public function testConfigStrict()
    {
        $dom = new Dom;
        $dom->setOptions([
            'strict' => true,
        ]);
        $dom->load('<div><p id="hey">Hey you</p> <p id="ya">Ya you!</p></div>');
        $this->assertEquals(' ', $dom->getElementById('hey')->nextSibling()->text);
    }

    public function testConfigStrictMissingSelfClosing()
    {
        $dom = new Dom;
        $dom->setOptions([
            'strict' => true,
        ]);
        try
        {
            // should throw an exception
            $dom->load('<div><p id="hey">Hey you</p><br><p id="ya">Ya you!</p></div>');
            // we should not get here
            $this->assertTrue(false);
        }
        catch (StrictException $e)
        {
            $this->assertEquals("Tag 'br' is not self closing! (character #31)", $e->getMessage());
        }
    }

    public function testConfigStrictMissingAttribute()
    {
        $dom = new Dom;
        $dom->setOptions([
            'strict' => true,
        ]);
        try
        {
            // should throw an exception
            $dom->load('<div><p id="hey" block>Hey you</p> <p id="ya">Ya you!</p></div>');
            // we should not get here
            $this->assertTrue(false);
        }
        catch (StrictException $e)
        {
            $this->assertEquals("Tag 'p' has an attribute 'block' with out a value! (character #22)", $e->getMessage());
        }
    }
}
