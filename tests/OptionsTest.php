<?php declare(strict_types=1);

use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\UnknownOptionException;
use PHPUnit\Framework\TestCase;
use PHPHtmlParser\Options;

class OptionsTest extends TestCase {

    public function testDefaultWhitespaceTextNode()
    {
        $options = new Options;

        $this->assertTrue($options->whitespaceTextNode);
    }

    public function testSettingOption()
    {
        $options = new Options;
        $options->setOptions([
            'strict' => true,
        ]);

        $this->assertTrue($options->strict);
    }

    public function testAddingOption()
    {
        $this->expectException(UnknownOptionException::class);

        $options = new Options;
        $options->setOptions([
            'test' => true,
        ]);
    }

    public function testOverwritingOption()
    {
        $options = new Options;
        $options->setOptions([
            'strict' => false,
        ])->setOptions([
            'strict' => true,
            'whitespaceTextNode' => false,
        ]);

        $this->assertTrue($options->get('strict'));
        $this->assertFalse($options->get('whitespaceTextNode'));
    }

    public function testGettingNoOption()
    {
        $options = new Options;
        $this->assertEquals(null, $options->get('doesnotexist'));
    }

    public function testUnknownOptionDom() {
        $dom = new Dom;
        $dom->setOptions([
            'unknown_option' => true,
        ]);

        $this->expectException(UnknownOptionException::class);
        $dom->load('<div></div>');
    }
}

