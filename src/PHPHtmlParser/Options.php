<?php

declare(strict_types=1);

namespace PHPHtmlParser;

class Options
{
    /**
     * The whitespaceTextNode, by default true, option tells the parser to save textnodes even if the content of the
     * node is empty (only whitespace). Setting it to false will ignore all whitespace only text node found in the document.
     *
     * @var bool
     */
    private $whitespaceTextNode = true;

    /**
     * Strict, by default false, will throw a StrictException if it finds that the html is not strictly compliant
     * (all tags must have a closing tag, no attribute with out a value, etc.).
     *
     * @var bool
     */
    private $strict = false;

    /**
     * The enforceEncoding, by default null, option will enforce an character set to be used for reading the content
     * and returning the content in that encoding. Setting it to null will trigger an attempt to figure out
     * the encoding from within the content of the string given instead.
     *
     * @var ?string
     */
    private $enforceEncoding;

    /**
     * Set this to false to skip the entire clean up phase of the parser. Defaults to true.
     *
     * @var bool
     */
    private $cleanupInput = true;

    /**
     * Set this to false to skip removing the script tags from the document body. This might have adverse effects.
     * Defaults to true.
     *
     * NOTE: Ignored if cleanupInit is true.
     *
     * @var bool
     */
    private $removeScripts = true;

    /**
     * Set this to false to skip removing of style tags from the document body. This might have adverse effects. Defaults to true.
     *
     * NOTE: Ignored if cleanupInit is true.
     *
     * @var bool
     */
    private $removeStyles = true;

    /**
     * Preserves Line Breaks if set to true. If set to false line breaks are cleaned up
     * as part of the input clean up process. Defaults to false.
     *
     * NOTE: Ignored if cleanupInit is true.
     *
     * @var bool
     */
    private $preserveLineBreaks = false;

    /**
     * Set this to false if you want to preserve whitespace inside of text nodes. It is set to true by default.
     *
     * @var bool
     */
    private $removeDoubleSpace = true;

    /**
     * Set this to false if you want to preserve smarty script found in the html content. It is set to true by default.
     *
     * @var bool
     */
    private $removeSmartyScripts = true;

    /**
     * By default this is set to false. Setting this to true will apply the php function htmlspecialchars_decode too all attribute values and text nodes.
     *
     * @var bool
     */
    private $htmlSpecialCharsDecode = false;

    public function isWhitespaceTextNode(): bool
    {
        return $this->whitespaceTextNode;
    }

    public function setWhitespaceTextNode(bool $whitespaceTextNode): Options
    {
        $this->whitespaceTextNode = $whitespaceTextNode;

        return $this;
    }

    public function isStrict(): bool
    {
        return $this->strict;
    }

    public function setStrict(bool $strict): Options
    {
        $this->strict = $strict;

        return $this;
    }

    public function getEnforceEncoding(): ?string
    {
        return $this->enforceEncoding;
    }

    public function setEnforceEncoding(?string $enforceEncoding): Options
    {
        $this->enforceEncoding = $enforceEncoding;

        return $this;
    }

    public function isCleanupInput(): bool
    {
        return $this->cleanupInput;
    }

    public function setCleanupInput(bool $cleanupInput): Options
    {
        $this->cleanupInput = $cleanupInput;

        return $this;
    }

    public function isRemoveScripts(): bool
    {
        return $this->removeScripts;
    }

    public function setRemoveScripts(bool $removeScripts): Options
    {
        $this->removeScripts = $removeScripts;

        return $this;
    }

    public function isRemoveStyles(): bool
    {
        return $this->removeStyles;
    }

    public function setRemoveStyles(bool $removeStyles): Options
    {
        $this->removeStyles = $removeStyles;

        return $this;
    }

    public function isPreserveLineBreaks(): bool
    {
        return $this->preserveLineBreaks;
    }

    public function setPreserveLineBreaks(bool $preserveLineBreaks): Options
    {
        $this->preserveLineBreaks = $preserveLineBreaks;

        return $this;
    }

    public function isRemoveDoubleSpace(): bool
    {
        return $this->removeDoubleSpace;
    }

    public function setRemoveDoubleSpace(bool $removeDoubleSpace): Options
    {
        $this->removeDoubleSpace = $removeDoubleSpace;

        return $this;
    }

    public function isRemoveSmartyScripts(): bool
    {
        return $this->removeSmartyScripts;
    }

    public function setRemoveSmartyScripts(bool $removeSmartyScripts): Options
    {
        $this->removeSmartyScripts = $removeSmartyScripts;

        return $this;
    }

    public function isHtmlSpecialCharsDecode(): bool
    {
        return $this->htmlSpecialCharsDecode;
    }

    public function setHtmlSpecialCharsDecode(bool $htmlSpecialCharsDecode): Options
    {
        $this->htmlSpecialCharsDecode = $htmlSpecialCharsDecode;

        return $this;
    }

    public function setFromOptions(Options $options): void
    {
        $this->setCleanupInput($options->isCleanupInput());
        $this->setEnforceEncoding($options->getEnforceEncoding());
        $this->setHtmlSpecialCharsDecode($options->isHtmlSpecialCharsDecode());
        $this->setPreserveLineBreaks($options->isPreserveLineBreaks());
        $this->setRemoveDoubleSpace($options->isRemoveDoubleSpace());
        $this->setRemoveScripts($options->isRemoveScripts());
        $this->setRemoveSmartyScripts($options->isRemoveSmartyScripts());
        $this->setRemoveStyles($options->isRemoveStyles());
        $this->setStrict($options->isStrict());
        $this->setWhitespaceTextNode($options->isWhitespaceTextNode());
    }
}
