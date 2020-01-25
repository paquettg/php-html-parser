<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPHtmlParser\Options;

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

    public function testSetters() {
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
            'depthFirstSearch'       => false,
            'htmlSpecialCharsDecode' => false,
        ]);

        $options->setWhitespaceTextNode(true);
        $this->assertTrue($options->get('whitespaceTextNode'));

        $options->setStrict(true);
        $this->assertTrue($options->get('strict'));

        $options->setEnforceEncoding("utf8");
        $this->assertEquals("utf8", $options->get('enforceEncoding'));

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

        $options->setDepthFirstSearch(true);
        $this->assertTrue($options->get('depthFirstSearch'));

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

        $options->setDepthFirstSearch(false);
        $this->assertFalse($options->get('depthFirstSearch'));

        $options->setHtmlSpecialCharsDecode(false);
        $this->assertFalse($options->get('htmlSpecialCharsDecode'));
    }
}

