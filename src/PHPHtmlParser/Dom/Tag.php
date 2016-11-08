<?php
namespace PHPHtmlParser\Dom;

use PHPHtmlParser\Dom;
use stringEncode\Encode;

/**
 * Class Tag
 *
 * @package PHPHtmlParser\Dom
 */
class Tag
{

    /**
     * The name of the tag.
     *
     * @var string
     */
    protected $name;

    /**
     * The attributes of the tag.
     *
     * @var array
     */
    protected $attr = [];

    /**
     * Is this tag self closing.
     *
     * @var bool
     */
    protected $selfClosing = false;

    /**
     * Tag noise
     */
    protected $noise = '';

    /**
     * The encoding class to... encode the tags
     *
     * @var mixed
     */
    protected $encode = null;

    /**
     * Sets up the tag with a name.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Magic method to get any of the attributes.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic method to set any attribute.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Returns the name of this tag.
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Sets the tag to be self closing.
     *
     * @return $this
     */
    public function selfClosing()
    {
        $this->selfClosing = true;

        return $this;
    }

    /**
     * Checks if the tag is self closing.
     *
     * @return bool
     */
    public function isSelfClosing()
    {
        return $this->selfClosing;
    }

    /**
     * Sets the encoding type to be used.
     *
     * @param Encode $encode
     */
    public function setEncoding(Encode $encode)
    {
        $this->encode = $encode;
    }

    /**
     * Sets the noise for this tag (if any)
     *
     * @param $noise
     * @return $this
     */
    public function noise($noise)
    {
        $this->noise = $noise;

        return $this;
    }

    /**
     * Set an attribute for this tag.
     *
     * @param string $key
     * @param string|array $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $key = strtolower($key);
        if ( ! is_array($value)) {
            $value = [
                'value'       => $value,
                'doubleQuote' => true,
            ];
        }
        $this->attr[$key] = $value;

        return $this;
    }

    /**
     * Removes an attribute from this tag.
     *
     * @param $key
     * @return void
     */
    public function removeAttribute($key)
    {
        $key = strtolower($key);
        unset($this->attr[$key]);
    }

    /**
     * Removes all attributes on this tag.
     *
     * @return void
     */
    public function removeAllAttributes()
    {
        $this->attr = [];
    }

    /**
     * Sets the attributes for this tag
     *
     * @param array $attr
     * @return $this
     */
    public function setAttributes(array $attr)
    {
        foreach ($attr as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Returns all attributes of this tag.
     *
     * @return array
     */
    public function getAttributes()
    {
        $return = [];
        foreach ($this->attr as $attr => $info) {
            $return[$attr] = $this->getAttribute($attr);
        }

        return $return;
    }

    /**
     * Returns an attribute by the key
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if ( ! isset($this->attr[$key])) {
            return null;
        }
        $value = $this->attr[$key]['value'];
        if (is_string($value) && ! is_null($this->encode)) {
            // convert charset
            $this->attr[$key]['value'] = $this->encode->convert($value);
        }

        return $this->attr[$key];
    }

    /**
     * Generates the opening tag for this object.
     *
     * @return string
     */
    public function makeOpeningTag()
    {
        $return = '<'.$this->name;

        // add the attributes
        foreach ($this->attr as $key => $info) {
            $info = $this->getAttribute($key);
            $val  = $info['value'];
            if (is_null($val)) {
                $return .= ' '.$key;
            } elseif ($info['doubleQuote']) {
                $return .= ' '.$key.'="'.$val.'"';
            } else {
                $return .= ' '.$key.'=\''.$val.'\'';
            }
        }

        if ($this->selfClosing) {
            return $return.' />';
        } else {
            return $return.'>';
        }
    }

    /**
     * Generates the closing tag for this object.
     *
     * @return string
     */
    public function makeClosingTag()
    {
        if ($this->selfClosing) {
            return '';
        }

        return '</'.$this->name.'>';
    }
}
