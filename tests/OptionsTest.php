<?php

declare(strict_types=1);

use PHPHtmlParser\Options;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    public function testDefaultWhitespaceTextNode()
    {
        $options = new Options();

        $this->assertTrue($options->isWhitespaceTextNode());
    }

    public function testSettingOption()
    {
        $options = new Options();
        $options->setStrict(true);

        $this->assertTrue($options->isStrict());
    }

    public function testOverwritingOption()
    {
        $options = new Options();
        $options->setStrict(false);
        $options2 = new Options();
        $options2->setStrict(true);
        $options2->setWhitespaceTextNode(false);
        $options = $options->setFromOptions($options2);

        $this->assertTrue($options->isStrict());
        $this->assertFalse($options->isWhitespaceTextNode());
    }

    public function testSetters()
    {
        $options = new Options();

        $options->setWhitespaceTextNode(true);
        $this->assertTrue($options->isWhitespaceTextNode());

        $options->setStrict(true);
        $this->assertTrue($options->isStrict());

        $options->setEnforceEncoding('utf8');
        $this->assertEquals('utf8', $options->getEnforceEncoding());

        $options->setCleanupInput(true);
        $this->assertTrue($options->isCleanupInput());

        $options->setRemoveScripts(true);
        $this->assertTrue($options->isRemoveScripts());

        $options->setRemoveStyles(true);
        $this->assertTrue($options->isRemoveStyles());

        $options->setPreserveLineBreaks(true);
        $this->assertTrue($options->isPreserveLineBreaks());

        $options->setRemoveDoubleSpace(true);
        $this->assertTrue($options->isRemoveDoubleSpace());

        $options->setRemoveSmartyScripts(true);
        $this->assertTrue($options->isRemoveSmartyScripts());

        $options->setHtmlSpecialCharsDecode(true);
        $this->assertTrue($options->isHtmlSpecialCharsDecode());
    }
}
