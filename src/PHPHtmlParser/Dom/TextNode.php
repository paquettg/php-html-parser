<?php declare(strict_types=1);
namespace PHPHtmlParser\Dom;

/**
 * Class TextNode
 *
 * @package PHPHtmlParser\Dom
 */
class TextNode extends LeafNode
{

    /**
     * This is a text node.
     *
     * @var Tag
     */
    protected $tag;

    /**
     * This is the text in this node.
     *
     * @var string
     */
    protected $text;

    /**
     * This is the converted version of the text.
     *
     * @var string
     */
    protected $convertedText = null;

    /**
     * Sets the text for this node.
     *
     * @param string $text
     * @param bool $removeDoubleSpace
     */
    public function __construct(string $text, $removeDoubleSpace = true)
    {
        if ($removeDoubleSpace) {
            // remove double spaces
            $text = mb_ereg_replace('\s+', ' ', $text);
        }

        // restore line breaks
        $text = str_replace('&#10;', "\n", $text);

        $this->text = $text;
        $this->tag  = new Tag('text');
        parent::__construct();
    }

    /**
     * @param bool $htmlSpecialCharsDecode
     * @return void
     */
    public function setHtmlSpecialCharsDecode($htmlSpecialCharsDecode = false): void
    {
        parent::setHtmlSpecialCharsDecode($htmlSpecialCharsDecode);
        $this->tag->setHtmlSpecialCharsDecode($htmlSpecialCharsDecode);
    }

    /**
     * Returns the text of this node.
     *
     * @return string
     */
    public function text(): string
    {
        if ($this->htmlSpecialCharsDecode) {
            $text = htmlspecialchars_decode($this->text);
        } else {
            $text = $this->text;
        }
        // convert charset
        if ( ! is_null($this->encode)) {
            if ( ! is_null($this->convertedText)) {
                // we already know the converted value
                return $this->convertedText;
            }
            $text = $this->encode->convert($text);

            // remember the conversion
            $this->convertedText = $text;

            return $text;
        }

        return $text;
    }

    /**
     * Sets the text for this node.
     *
     * @var string $text
     * @return void
     */
    public function setText(string $text): void
    {
        $this->text = $text;
        if ( ! is_null($this->encode)) {
            $text = $this->encode->convert($text);

            // remember the conversion
            $this->convertedText = $text;
        }
    }

    /**
     * This node has no html, just return the text.
     *
     * @return string
     * @uses $this->text()
     */
    public function innerHtml(): string
    {
        return $this->text();
    }

    /**
     * This node has no html, just return the text.
     *
     * @return string
     * @uses $this->text()
     */
    public function outerHtml(): string
    {
        return $this->text();
    }

    /**
     * Call this when something in the node tree has changed. Like a child has been added
     * or a parent has been changed.
     */
    protected function clear(): void
    {
        $this->convertedText = null;
    }

    /**
     * Checks if the current node is a text node.
     *
     * @return bool
     */
    public function isTextNode(): bool
    {
        return true;
    }
}
