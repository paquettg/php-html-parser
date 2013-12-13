<?php
namespace PHPHtmlParser\Dom;

use PHPHtmlParser\Dom;
use stringEncode\Encode;

class Tag {

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
	protected $selfclosing = false;

	/**
	 * Tag noise
	 */
	protected $noise = '';

	public function __construct($name)
	{
		$this->name = $name;
	}

	public function __get($key)
	{
		return $this->getAttribute($key);
	}

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
	 * @chainable
	 */
	public function selfClosing()
	{
		$this->selfclosing = true;
		return $this;
	}

	/**
	 * Checks if the tag is self closing.
	 *
	 * @return bool
	 */
	public function isSelfClosing()
	{
		return $this->selfclosing;
	}

	/**
	 * Sets the noise for this tag (if any)
	 *
	 * @chainable
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
	 * @param mixed $value
	 * @chainable
	 */
	public function setAttribute($key, $value)
	{
		// convert charset
		$encode = new Encode;
		$encode->from(Dom::$expectedCharset);
		$encode->to(Dom::$charset);
		$value['value'] = $encode->convert($value['value']);

		$this->attr[$key] = $value;
		return $this;
	}

	/**
	 * Sets the attributes for this tag
	 *
	 * @param array $attr
	 * @chainable
	 */
	public function setAttributes(array $attr)
	{
		foreach ($attr as $key => $value)
		{
			$this->attr[$key] = $value;
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
		return $this->attr;
	}

	/**
	 * Returns an attribute by the key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getAttribute($key)
	{
		if ( ! isset($this->attr[$key]))
		{
			return null;
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
		foreach ($this->attr as $key => $info)
		{
			$val = $info['value'];
			if (is_null($val))
			{
				$return .= ' '.$key;
			}
			elseif ($info['doubleQuote'])
			{
				$return .= ' '.$key.'="'.$val.'"';
			}
			else
			{
				$return .= ' '.$key.'=\''.$val.'\'';
			}
		}

		if ($this->selfclosing)
		{
			return $return.' />';
		}
		else
		{
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
		if ($this->selfclosing)
		{
			return '';
		}

		return '</'.$this->name.'>';
	}
}
