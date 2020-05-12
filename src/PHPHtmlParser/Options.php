<?php

declare(strict_types=1);

namespace PHPHtmlParser;

use PHPHtmlParser\Exceptions\UnknownOptionException;

/**
 * Class Options.
 *
 * @property bool        $whitespaceTextNode
 * @property bool        $strict
 * @property string|null $enforceEncoding
 * @property bool        $cleanupInput
 * @property bool        $removeScripts
 * @property bool        $removeStyles
 * @property bool        $preserveLineBreaks
 * @property bool        $removeDoubleSpace
 * @property bool        $removeSmartyScripts
 * @property bool        $htmlSpecialCharsDecode
 */
class Options
{
    /**
     * The default options array.
     *
     * @var array
     */
    protected $defaults = [
        'whitespaceTextNode'     => true,
        'strict'                 => false,
        'enforceEncoding'        => null,
        'cleanupInput'           => true,
        'removeScripts'          => true,
        'removeStyles'           => true,
        'preserveLineBreaks'     => false,
        'removeDoubleSpace'      => true,
        'removeSmartyScripts'    => true,
        'htmlSpecialCharsDecode' => false,
    ];

    /**
     * The list of all current options set.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Sets the default options in the options array.
     */
    public function __construct()
    {
        $this->options = $this->defaults;
    }

    /**
     * A magic get to call the get() method.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @uses $this->get()
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * The whitespaceTextNode, by default true, option tells the parser to save textnodes even if the content of the
     * node is empty (only whitespace). Setting it to false will ignore all whitespace only text node found in the document.
     *
     * @return Options
     */
    public function setWhitespaceTextNode(bool $value): self
    {
        $this->options['whitespaceTextNode'] = $value;

        return $this;
    }

    /**
     * Strict, by default false, will throw a StrictException if it finds that the html is not strictly compliant
     * (all tags must have a closing tag, no attribute with out a value, etc.).
     *
     * @return Options
     */
    public function setStrict(bool $value): self
    {
        $this->options['strict'] = $value;

        return $this;
    }

    /**
     * The enforceEncoding, by default null, option will enforce an character set to be used for reading the content
     * and returning the content in that encoding. Setting it to null will trigger an attempt to figure out
     * the encoding from within the content of the string given instead.
     *
     * @return Options
     */
    public function setEnforceEncoding(?string $value): self
    {
        $this->options['enforceEncoding'] = $value;

        return $this;
    }

    /**
     * Set this to false to skip the entire clean up phase of the parser. Defaults to true.
     *
     * @return Options
     */
    public function setCleanupInput(bool $value): self
    {
        $this->options['cleanupInput'] = $value;

        return $this;
    }

    /**
     * Set this to false to skip removing the script tags from the document body. This might have adverse effects.
     * Defaults to true.
     *
     * NOTE: Ignored if cleanupInit is true.
     *
     * @return Options
     */
    public function setRemoveScripts(bool $value): self
    {
        $this->options['removeScripts'] = $value;

        return $this;
    }

    /**
     * Set this to false to skip removing of style tags from the document body. This might have adverse effects. Defaults to true.
     *
     * NOTE: Ignored if cleanupInit is true.
     *
     * @return Options
     */
    public function setRemoveStyles(bool $value): self
    {
        $this->options['removeStyles'] = $value;

        return $this;
    }

    /**
     * Preserves Line Breaks if set to true. If set to false line breaks are cleaned up
     * as part of the input clean up process. Defaults to false.
     *
     * NOTE: Ignored if cleanupInit is true.
     *
     * @return Options
     */
    public function setPreserveLineBreaks(bool $value): self
    {
        $this->options['preserveLineBreaks'] = $value;

        return $this;
    }

    /**
     * Set this to false if you want to preserve whitespace inside of text nodes. It is set to true by default.
     *
     * @return Options
     */
    public function setRemoveDoubleSpace(bool $value): self
    {
        $this->options['removeDoubleSpace'] = $value;

        return $this;
    }

    /**
     * Set this to false if you want to preserve smarty script found in the html content. It is set to true by default.
     *
     * @return Options
     */
    public function setRemoveSmartyScripts(bool $value): self
    {
        $this->options['removeSmartyScripts'] = $value;

        return $this;
    }

    /**
     * By default this is set to false. Setting this to true will apply the php function htmlspecialchars_decode too all attribute values and text nodes.
     *
     * @return Options
     */
    public function setHtmlSpecialCharsDecode(bool $value): self
    {
        $this->options['htmlSpecialCharsDecode'] = $value;

        return $this;
    }

    /**
     * Sets a new options param to override the current option array.
     *
     * @chainable
     *
     * @throws UnknownOptionException
     */
    public function setOptions(array $options): Options
    {
        foreach ($options as $key => $option) {
            if (!\array_key_exists($key, $this->defaults)) {
                throw new UnknownOptionException("Option '$key' is not recognized");
            }
            $this->options[$key] = $option;
        }

        return $this;
    }

    /**
     * Gets the value associated to the key, or null if the key is not
     * found.
     *
     * @return mixed
     */
    public function get(string $key)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
    }

    /**
     * Return current options as array.
     *
     * @return array
     */
    public function asArray()
    {
        return $this->options;
    }
}
