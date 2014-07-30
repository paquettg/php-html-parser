<?php
namespace PHPHtmlParser;

class Options {

	/**
	 * The default options array
	 * 
	 * @param array
	 */
	protected $defaults = array(
		'whitespaceTextNode' => true,
		'strict'             => false,
	);

	/**
	 * The list of all current options set.
	 *
	 * @param array
	 */
	protected $options = array();

	/**
	 * Sets the default options in the options array
	 */
	public function __construct()
	{
		$this->options = $this->defaults;
	}

	/**
	 * A magic get to call the get() method.
	 *
	 * @param string $key
	 * @return mixed
	 * @uses $this->get()
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * Sets a new options param to override the current option array.
	 *
	 * @param array $options
	 * @chainable
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $key => $option)
		{
			$this->options[$key] = $option;
		}
		return $this;
	}

	/**
	 * Gets the value associated to the key, or null if the key is not
	 * found.
	 *
	 * @param string
	 * @return mixed
	 */
	public function get($key)
	{
		if (isset($this->options[$key]))
		{
			return $this->options[$key];
		}
		
		return null;
	}
}
