<?php

declare(strict_types=1);

use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\UnknownOptionException;
use PHPHtmlParser\Options;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    public function testDefaultWhitespaceTextNode()
    {
        $options = new Options();

        $this->assertTrue($options->whitespaceTextNode);
    }

    public function testSettingOption()
    {
        $options = new Options();
        $options->setOptions([
            'strict' => true,
        ]);

        $this->assertTrue($options->strict);
    }

    public function testAddingOption()
    {
        $this->expectException(UnknownOptionException::class);

        $options = new Options();
        $options->setOptions([
            'test' => true,
        ]);
    }

    public function testOverwritingOption()
    {
        $options = new Options();
        $options->setOptions([
            'strict' => false,
        ])->setOptions([
            'strict'             => true,
            'whitespaceTextNode' => false,
        ]);

        $this->assertTrue($options->get('strict'));
        $this->assertFalse($options->get('whitespaceTextNode'));
    }

    public function testGettingNoOption()
    {
        $options = new Options();
        $this->assertEquals(null, $options->get('doesnotexist'));
    }

    public function testSetters()
    {
        $options = new Options();

        $options->setOptions([
            'whitespaceTextNode'     => false,
            'strict'                 => false,
            'enforceEncoding'        => null,
            'cleanupInput'           => false,
            'removeScripts'          => false,
            'removeStyles'           => false,
            'preserveLineBreaks'     => false,
            'removeDoubleSpace'      => false,
            'removeSmartyScripts'    => false,
            'htmlSpecialCharsDecode' => false,
        ]);

        $options->setWhitespaceTextNode(true);
        $this->assertTrue($options->get('whitespaceTextNode'));

        $options->setStrict(true);
        $this->assertTrue($options->get('strict'));

        $options->setEnforceEncoding('utf8');
        $this->assertEquals('utf8', $options->get('enforceEncoding'));

        $options->setCleanupInput(true);
        $this->assertTrue($options->get('cleanupInput'));

        $options->setRemoveScripts(true);
        $this->assertTrue($options->get('removeScripts'));

        $options->setRemoveStyles(true);
        $this->assertTrue($options->get('removeStyles'));

        $options->setPreserveLineBreaks(true);
        $this->assertTrue($options->get('preserveLineBreaks'));

        $options->setRemoveDoubleSpace(true);
        $this->assertTrue($options->get('removeDoubleSpace'));

        $options->setRemoveSmartyScripts(true);
        $this->assertTrue($options->get('removeSmartyScripts'));

        $options->setHtmlSpecialCharsDecode(true);
        $this->assertTrue($options->get('htmlSpecialCharsDecode'));

        // now reset to false

        $options->setWhitespaceTextNode(false);
        $this->assertFalse($options->get('whitespaceTextNode'));

        $options->setStrict(false);
        $this->assertFalse($options->get('strict'));

        $options->setEnforceEncoding(null);
        $this->assertNull($options->get('enforceEncoding'));

        $options->setCleanupInput(false);
        $this->assertFalse($options->get('cleanupInput'));

        $options->setRemoveScripts(false);
        $this->assertFalse($options->get('removeScripts'));

        $options->setRemoveStyles(false);
        $this->assertFalse($options->get('removeStyles'));

        $options->setPreserveLineBreaks(false);
        $this->assertFalse($options->get('preserveLineBreaks'));

        $options->setRemoveDoubleSpace(false);
        $this->assertFalse($options->get('removeDoubleSpace'));

        $options->setRemoveSmartyScripts(false);
        $this->assertFalse($options->get('removeSmartyScripts'));

        $options->setHtmlSpecialCharsDecode(false);
        $this->assertFalse($options->get('htmlSpecialCharsDecode'));
    }

    public function testUnknownOptionDom()
    {
        $dom = new Dom();
        $dom->setOptions([
            'unknown_option' => true,
        ]);

        $this->expectException(UnknownOptionException::class);
        $dom->load('<div></div>');
    }
}
