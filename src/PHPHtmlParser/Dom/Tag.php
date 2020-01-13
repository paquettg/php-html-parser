<?php declare(strict_types=1);
namespace PHPHtmlParser\Dom;

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
     * If self-closing, will this use a trailing slash. />
     *
     * @var bool
     */
    protected $trailingSlash = true;

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
     * @var bool
     */
    private $HtmlSpecialCharsDecode = false;

    /**
     * Sets up the tag with a name.
     *
     * @param $name
     */
    public function __construct(string $name)
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
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Sets the tag to be self closing.
     *
     * @return Tag
     * @chainable
     */
    public function selfClosing(): Tag
    {
        $this->selfClosing = true;

        return $this;
    }


    /**
     * Sets the tag to not use a trailing slash.
     *
     * @return Tag
     * @chainable
     */
    public function noTrailingSlash(): Tag
    {
        $this->trailingSlash = false;

        return $this;
    }

    /**
     * Checks if the tag is self closing.
     *
     * @return bool
     */
    public function isSelfClosing(): bool
    {
        return $this->selfClosing;
    }

    /**
     * Sets the encoding type to be used.
     *
     * @param Encode $encode
     * @return void
     */
    public function setEncoding(Encode $encode): void
    {
        $this->encode = $encode;
    }

    /**
     * @param bool $htmlSpecialCharsDecode
     * @return void
     */
    public function setHtmlSpecialCharsDecode($htmlSpecialCharsDecode = false): void
    {
        $this->HtmlSpecialCharsDecode = $htmlSpecialCharsDecode;
    }

    /**
     * Sets the noise for this tag (if any)
     *
     * @param string $noise
     * @return Tag
     * @chainable
     */
    public function noise(string $noise): Tag
    {
        $this->noise = $noise;

        return $this;
    }

    /**
     * Set an attribute for this tag.
     *
     * @param string $key
     * @param string|array $value
     * @return Tag
     * @chainable
     */
    public function setAttribute(string $key, $value): Tag
    {
        $key = strtolower($key);
        if ( ! is_array($value)) {
            $value = [
                'value'       => $value,
                'doubleQuote' => true,
            ];
        }
        if ($this->HtmlSpecialCharsDecode) {
            $value['value'] = htmlspecialchars_decode($value['value']);
        }
        $this->attr[$key] = $value;

        return $this;
    }

    /**
     * Set inline style attribute value.
     *
     * @param mixed $attr_key
     * @param mixed $attr_value
     */
    public function setStyleAttributeValue($attr_key, $attr_value): void
    {
        $style_array = $this->getStyleAttributeArray();
        $style_array[$attr_key] = $attr_value;

        $style_string = '';
        foreach ($style_array as $key => $value) {
            $style_string .= $key . ':' . $value . ';';
        }

        $this->setAttribute('style', $style_string);
    }

    /**
     * Get style attribute in array
     *
     * @return array
     */
    public function getStyleAttributeArray(): array
    {
        $value = $this->getAttribute('style')['value'];

        if ($value === null) {
            return [];
        }

        $value = explode(';', substr(trim($value), 0, -1));
        $result = [];
        foreach ($value as $attr) {
            $attr = explode(':', $attr);
            $result[$attr[0]] = $attr[1];
        }

        return $result;
    }



    /**
     * Removes an attribute from this tag.
     *
     * @param mixed $key
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
        foreach (array_keys($this->attr) as $attr) {
            $return[$attr] = $this->getAttribute($attr);
        }

        return $return;
    }

    /**
     * Returns an attribute by the key
     *
     * @param string $key
     * @return array
     */
    public function getAttribute(string $key):array
    {
        $key = strtolower($key);
        if ( ! isset($this->attr[$key])) {
            return ['value' => null, 'doubleQuote' => true];
        }
        $value = $this->attr[$key]['value'];
        if (is_string($value) && ! is_null($this->encode)) {
            // convert charset
            $this->attr[$key]['value'] = $this->encode->convert($value);
        }

        return $this->attr[$key];
    }

    /**
     * Returns TRUE if node has attribute
     *
     * @param string $key
     * @return bool
     */
    public function hasAttribute(string $key)
    {
        return isset($this->attr[$key]);
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
        foreach (array_keys($this->attr) as $key) {
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

        if ($this->selfClosing && $this->trailingSlash) {
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
