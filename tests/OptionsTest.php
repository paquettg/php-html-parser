<?php

use PHPHtmlParser\Options;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase {

    public function testDefaultWhitespaceTextNode()
    {
        $options = new Options;

        $this->assertTrue($options->whitespaceTextNode);
    }

    public function testAddingOption()
    {
        $options = new Options;
        $options->setOptions([
            'test' => true,
        ]);

        $this->assertTrue($options->test);
    }

    public function testAddingOver()
    {
        $options = new Options;
        $options->setOptions([
            'test' => false,
        ])->setOptions([
            'test' => true,
            'whitespaceTextNode' => false,
        ]);

        $this->assertFalse($options->get('whitespaceTextNode'));
    }

    public function testGettingNoOption()
    {
        $options = new Options;
        $this->assertEquals(null, $options->get('doesnotexist'));
    }
}

