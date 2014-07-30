<?php
namespace PHPHtmlParser;

use PHPHtmlParser\Dom\HtmlNode;
use PHPHtmlParser\Dom\TextNode;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;
use stringEncode\Encode;

class Dom {
	
	/**
	 * The charset we would like the output to be in.
	 *
	 * @var string
	 */
	protected $defaultCharset = 'UTF-8';

	/**
	 * Contains the root node of this dom tree.
	 *
	 * @var HtmlNode
	 */
	public $root;

	/**
	 * The raw version of the document string.
	 *
	 * @var string
	 */
	protected $raw;

	/**
	 * The document string.
	 *
	 * @var Content
	 */
	protected $content = null;

	/**
	 * The original file size of the document.
	 *
	 * @var int
	 */
	protected $rawSize;
	
	/**
	 * The size of the document after it is cleaned.
	 *
	 * @var int
	 */
	protected $size;

	/**
	 * A global options array to be used by all load calls.
	 *
	 * @var array
	 */
	protected $globalOptions = array();

	/**
	 * A persistent option object to be used for all options in the 
	 * parsing of the file.
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * A list of tags which will always be self closing
	 *
	 * @var array
	 */
	protected $selfClosing = array(
		'img',
		'br',
		'input',
		'meta',
		'link',
		'hr',
		'base',
		'embed',
		'spacer',
	);

	/**
	 * Returns the inner html of the root node.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->root->innerHtml();
	}

	/**
	 * A simple wrapper around the root node.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->root->$name;
	}

	/**
	 * Attempts to load the dom from any resource, string, file, or URL.
	 *
	 * @param string $str
	 * @param array $option
	 * @chainable
	 */
	public function load($str, $options = array())
	{
		// check if it's a file
		if (is_file($str))
		{
			return $this->loadFromFile($str, $options);
		}
		// check if it's a url
		if (preg_match("/^https?:\/\//i",$str))
		{
			return $this->loadFromUrl($str, $options);
		}

		return $this->loadStr($str, $options);
	}

	/**
	 * Loads the dom from a document file/url
	 *
	 * @param string $file
	 * @param array $option
	 * @chainable
	 */
	public function loadFromFile($file, $options = array())
	{
		return $this->loadStr(file_get_contents($file), $options);
	}

	/**
	 * Use a curl interface implementation to attempt to load
	 * the content from a url.
	 *
	 * @param string $url
	 * @param array $option
	 * @param CurlInterface $curl
	 * @chainable
	 */
	public function loadFromUrl($url, $options = array(), CurlInterface $curl = null)
	{
		if (is_null($curl))
		{
			// use the default curl interface
			$curl = new Curl;
		}
		$content = $curl->get($url);

		return $this->loadStr($content, $options);
	}

	/**
	 * Sets a global options array to be used by all load calls.
	 *
	 * @param array $options
	 * @chainable
	 */
	public function setOptions(array $options)
	{
		$this->globalOptions = $options;
		return $this;
	}

	/**
	 * Find elements by css selector on the root node.
	 *
	 * @param string $selector
	 * @param int	 $nth
	 * @return array
	 */
	public function find($selector, $nth = null)
	{
		$this->isLoaded();
		return $this->root->find($selector, $nth);
	}

	/**
	 * Adds the tag (or tags in an array) to the list of tags that will always
	 * be self closing.
	 *
	 * @param string|array $tag
	 * @chainable
	 */
	public function addSelfClosingTag($tag)
	{
		if ( ! is_array($tag))
		{
			$tag = array($tag);
		}
		foreach ($tag as $value)
		{
			$this->selfClosing[] = $value;
		}
		return $this;
	}
	
	/**
	 * Removes the tag (or tags in an array) from the list of tags that will
	 * always be self closing.
	 *
	 * @param string|array $tag
	 * @chainable
	 */
	public function removeSelfClosingTag($tag)
	{
		if ( ! is_array($tag))
		{
			$tag = array($tag);
		}
		$this->selfClosing = array_diff($this->selfClosing, $tag);
		return $this;
	}

	/**
	 * Sets the list of self closing tags to empty.
	 *
	 * @chainable
	 */
	public function clearSelfClosingTags()
	{
		$this->selfClosing = array();
		return $this;
	}

	/**
	 * Simple wrapper function that returns the first child.
	 *
	 * @return Node
	 */
	public function firstChild()
	{
		$this->isLoaded();
		return $this->root->firstChild();
	}

	/**
	 * Simple wrapper function that returns the last child.
	 *
	 * @return AbstractNode
	 */
	public function lastChild()
	{
		$this->isLoaded();
		return $this->root->lastChild();
	}

	/**
	 * Simple wrapper function that returns an element by the
	 * id.
	 *
	 * @return AbstractNode
	 */
	public function getElementById($id)
	{
		$this->isLoaded();
		return $this->find('#'.$id, 0);
	}

	/**
	 * Simple wrapper function that returns all elements by 
	 * tag name.
	 *
	 * @return array
	 */
	public function getElementsByTag($name)
	{
		$this->isLoaded();
		return $this->find($name);
	}

	/**
	 * Simple wrapper function that returns all elements by
	 * class name.
	 *
	 * @return array
	 */
	public function getElementsByClass($class)
	{
		$this->isLoaded();
		return $this->find('.'.$class);
	}

	/**
	 * Parsers the html of the given string. Used for load(), loadFromFile(),
	 * and loadFromUrl().
	 *
	 * @param string $str
	 * @param array $option
	 * @chainable
	 */
	protected function loadStr($str, $option)
	{
		$this->options = new Options;
		$this->options->setOptions($this->globalOptions)
		              ->setOptions($option);

		$this->rawSize = strlen($str);
		$this->raw     = $str;

		$html = $this->clean($str);

		$this->size    = strlen($str);
		$this->content = new Content($html);

		$this->parse();
		$this->detectCharset();

		return $this;
	}

	/**
	 * Checks if the load methods have been called.
	 *
	 * @throws NotLoadedException
	 */
	protected function isLoaded()
	{
		if (is_null($this->content))
		{
			throw new NotLoadedException('Content is not loaded!');
		}
	}

	/**
	 * Cleans the html of any none-html information.
	 *
	 * @param string $str
	 * @return string
	 */
	protected function clean($str)
	{
		// clean out the \n\r
		$str = str_replace(array("\r\n", "\r", "\n"), ' ', $str);

		// strip the doctype
		$str = preg_replace("'<!doctype(.*?)>'is", '', $str);

		// strip out comments
		$str = preg_replace("'<!--(.*?)-->'is", '', $str);
		
		// strip out cdata
		$str = preg_replace("'<!\[CDATA\[(.*?)\]\]>'is", '', $str);
		
		// strip out <script> tags
		$str = preg_replace("'<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>'is", '', $str);
		$str = preg_replace("'<\s*script\s*>(.*?)<\s*/\s*script\s*>'is", '', $str);
		
		// strip out <style> tags
		$str = preg_replace("'<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>'is", '', $str);
		$str = preg_replace("'<\s*style\s*>(.*?)<\s*/\s*style\s*>'is", '', $str);
		
		// strip out preformatted tags
		$str = preg_replace("'<\s*(?:code)[^>]*>(.*?)<\s*/\s*(?:code)\s*>'is", '', $str);
		
		// strip out server side scripts
		$str = preg_replace("'(<\?)(.*?)(\?>)'s", '', $str);
		
		// strip smarty scripts
		$str = preg_replace("'(\{\w)(.*?)(\})'s", '', $str);

		return $str;
	}

	/**
	 * Attempts to parse the html in content.
	 */
	protected function parse()
	{
		// add the root node
		$this->root = new HtmlNode('root');
		$activeNode = $this->root;
		while ( ! is_null($activeNode))
		{
			$str = $this->content->copyUntil('<');
			if ($str == '')
			{
				$info = $this->parseTag();
				if ( ! $info['status'])
				{
					// we are done here
					$activeNode = null;
					continue;
				}

				// check if it was a closing tag
				if ($info['closing'])
				{
					$originalNode = $activeNode;
					while ($activeNode->getTag()->name() != $info['tag'])
					{
						$activeNode = $activeNode->getParent();
						if (is_null($activeNode))
						{
							// we could not find opening tag
							$activeNode = $originalNode;
							break;
						}
					}
					if ( ! is_null($activeNode))
					{
						$activeNode = $activeNode->getParent();
					}
					continue;
				}

				if ( ! isset($info['node']))
				{
					continue;
				}

				$node = $info['node'];
				$activeNode->addChild($node);

				// check if node is self closing
				if ( ! $node->getTag()->isSelfClosing())
				{
					$activeNode = $node;
				}
			}
			else if ($this->options->whitespaceTextNode or
				     trim($str) != '')
			{
				// we found text we care about
				$textNode = new TextNode($str);
				$activeNode->addChild($textNode);
			}
		}
	}

	/**
	 * Attempt to parse a tag out of the content.
	 *
	 * @return array
	 */
	protected function parseTag()
	{
		$return = array( 
			'status'  => false,
			'closing' => false,
			'node'	  => null,
		);
		if ($this->content->char() != '<')
		{
			// we are not at the beginning of a tag
			return $return;
		}

		// check if this is a closing tag
		if ($this->content->fastForward(1)->char() == '/')
		{
			// end tag
			$tag = $this->content->fastForward(1)
			                     ->copyByToken('slash', true);
			// move to end of tag
			$this->content->copyUntil('>');
			$this->content->fastForward(1);
			
			// check if this closing tag counts
			$tag = strtolower($tag);
			if (in_array($tag, $this->selfClosing))
			{
				$return['status'] = true;
				return $return;
			}
			else
			{
				$return['status']  = true;
				$return['closing'] = true;
				$return['tag']	   = strtolower($tag);
			}
			return $return;
		}

		$tag   = strtolower($this->content->copyByToken('slash', true));
		$node  = new HtmlNode($tag);

		// attributes
		while ($this->content->char() != '>' and
		       $this->content->char() != '/')
		{
			$space = $this->content->skipByToken('blank', true);
			if (empty($space))
			{
				break;
			}

			$name = $this->content->copyByToken('equal', true);
			if ($name == '/' OR
			    empty($name))
			{
				break;
			}

			if (empty($name))
			{
				$this->content->fastForward(1);
				continue;
			}

			$this->content->skipByToken('blank');
			if ($this->content->char() == '=')
			{
				$attr = array();
				$this->content->fastForward(1)
				              ->skipByToken('blank');
				switch ($this->content->char())
				{
					case '"':
						$attr['doubleQuote'] = true;
						$this->content->fastForward(1);
						$attr['value'] = $this->content->copyUntil('"', false, true);
						$this->content->fastForward(1);
						$node->getTag()->$name = $attr;
						break;
					case "'":
						$attr['doubleQuote'] = false;
						$this->content->fastForward(1);
						$attr['value'] = $this->content->copyUntil("'", false, true);
						$this->content->fastForward(1);
						$node->getTag()->$name = $attr;
						break;
					default:
						$attr['doubleQuote']   = true;
						$attr['value']         = $this->content->copyByToken('attr', true);
						$node->getTag()->$name = $attr;
						break;
				}
			}
			else
			{
				// no value attribute
				if ($this->options->strict)
				{
					// can't have this in strict html
					$character = $this->content->getPosition();
					throw new StrictException("Tag '$tag' has an attribute '$name' with out a value! (character #$character)");
				}
				$node->getTag()->$name = array(
					'value'       => null,
					'doubleQuote' => true,
				);
				$this->content->rewind(1);
			}
		}

		$this->content->skipByToken('blank');
		if ($this->content->char() == '/')
		{
			// self closing tag
			$node->getTag()->selfClosing();
			$this->content->fastForward(1);
		}
		elseif (in_array($tag, $this->selfClosing))
		{
			
			// Should be a self closing tag, check if we are strict
			if ( $this->options->strict)
			{
				$character = $this->content->getPosition();
				throw new StrictException("Tag '$tag' is not self clossing! (character #$character)");
			}

			// We force self closing on this tag.
			$node->getTag()->selfClosing();
		}
		
		$this->content->fastForward(1);

		$return['status'] = true;
		$return['node']   = $node;
		return $return;
	}

	/**
	 * Attempts to detect the charset that the html was sent in.
	 *
	 * @return bool
	 */
	protected function detectCharset()
	{
		// set the default
		$encode = new Encode;
		$encode->from($this->defaultCharset);
		$encode->to($this->defaultCharset);

		$meta = $this->root->find('meta[http-equiv=Content-Type]', 0);
		if (is_null($meta))
		{
			// could not find meta tag
			$this->root->propagateEncoding($encode);
			return false;
		}
		$content = $meta->content;
		if (empty($content))
		{
			// could not find content
			$this->root->propagateEncoding($encode);
			return false;
		}
		$matches = array();
		if (preg_match('/charset=(.+)/', $content, $matches))
		{
			$encode->from(trim($matches[1]));
			$this->root->propagateEncoding($encode);
			return true;
		}
		
		// no charset found
		$this->root->propagateEncoding($encode);
		return false;
	}
}
