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
 * @property bool        $depthFirstSearch
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
        'depthFirstSearch'       => false,
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
     * Sets a new options param to override the current option array.
     *
     * @chainable
     * @throws UnknownOptionException
     */
    public function setOptions(array $options): Options
    {
        foreach ($options as $key => $option) {
            if (!array_key_exists($key, $this->defaults)) {
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
     * Return current options as array
     *
     * @return array
     */
    public function asArray() {
        return $this->options;
    }
}
