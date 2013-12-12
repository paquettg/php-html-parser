<?php
namespace PHPHtmlParser\Dom;

class HtmlNode extends Node {

	/**
	 * Sets up the tag of this node.
	 */
	public function __construct($tag)
	{
		if ( ! $tag instanceof Tag)
		{
			$tag = new Tag($tag);
		}
		$this->tag = $tag;
		parent::__construct();
	}

	/**
	 * Gets the inner html of this node.
	 *
	 * @return string
	 */
	public function innerHtml()
	{
		if ( ! $this->hasChildren())
		{
			// no children
			return '';
		}

		$child  = $this->firstChild();
		$string = '';

		// continue to loop until we are out of children
		while( ! is_null($child))
		{
			if ($child instanceof TextNode)
			{
				$string .= $child->text();
			}
			elseif ($child instanceof HtmlNode)
			{
				$string .= $child->outerHtml();
			}
			else
			{
				throw new Exception('Error: Unkowne child type "'.get_class($child).'" found in node');
			}

			try
			{
				$child = $this->nextChild($child->id());
			}
			catch (Exception $e)
			{
				// no more children
				$child = null;
			}
		}
		
		return $string;
	}

	/**
	 * Gets the html of this node, including it's own
	 * tag.
	 *
	 * @return string
	 */
	public function outerHtml()
	{
		// special handling for root
		if ( $this->tag->name() == 'root')
		{
			return $this->innerHtml();
		}

		$return = $this->tag->makeOpeningTag();
		if ($this->tag->isSelfClosing())
		{
			// ignore any children... there should not be any though
			return $return;
		}

		// get the inner html
		$return .= $this->innerHtml();

		// add closing tag
		$return .= $this->tag->makeClosingTag();

		return $return;
	}

	/**
	 * Gets the text of this node (if there is any text).
	 *
	 * @return string
	 */
	public function text()
	{
		// find out if this node has any text children
		foreach ($this->children as $child)
		{
			if ($child['node'] instanceof TextNode)
			{
				// we found a text node
				return $child['node']->text();
			}
		}

		// no text found in this node
		return '';
	}
}
