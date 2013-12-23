<?php
namespace PHPHtmlParser\Dom;

use PHPHtmlParser\Dom;

class TextNode extends Node {
	
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
	 * Sets the text for this node.
	 *
	 * @param string $text
	 */
	public function __construct($text)
	{
		// remove double spaces
		$text = preg_replace('/\s+/', ' ', $text);

		$this->text = $text;
		$this->tag  = new Tag('text');
		parent::__construct();
	}

	/**
	 * Returns the text of this node.
	 *
	 * @return string
	 */
	public function text()
	{
		// convert charset
		if ( ! is_null($this->encode))
		{
			$text = $this->encode->convert($this->text);
		}
		else
		{
			$text = $this->text;
		}

		return $text;
	}

}
