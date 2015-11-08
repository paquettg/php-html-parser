<?php
namespace PHPHtmlParser\Dom;

use PHPHtmlParser\Selector;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ParentNotFoundException;
use stringEncode\Encode;

/**
 * Dom node object.
 *
 * @property string outerhtml
 * @property string innerhtml
 * @property string text
 */
abstract class AbstractNode {

	/**
	 * Contains the tag name/type
	 *
	 * @var \PHPHtmlParser\Dom\Tag
	 */
	protected $tag;

	/**
	 * Contains a list of attributes on this tag.
	 *
	 * @var array
	 */
	protected $attr = [];

	/**
	 * An array of all the children.
	 *
	 * @var array
	 */
	protected $children = [];

	/**
	 * Contains the parent Node.
	 *
	 * @var AbstractNode
	 */
	protected $parent = null;

	/**
	 * The unique id of the class. Given by PHP.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The encoding class used to encode strings.
	 *
	 * @var mixed
	 */
	protected $encode;

	/**
	 * Creates a unique spl hash for this node.
	 */
	public function __construct()
	{
		$this->id = spl_object_hash($this);
	}

	/**
	 * Magic get method for attributes and certain methods.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		// check attribute first
		if ( ! is_null($this->getAttribute($key)))
		{
			return $this->getAttribute($key);
		}
		switch (strtolower($key))
		{
			case 'outerhtml':
				return $this->outerHtml();
			case 'innerhtml':
				return $this->innerHtml();
			case 'text':
				return $this->text();
		}

		return null;
	}

	/**
	 * Attempts to clear out any object references.
	 */
	public function __destruct()
	{
		$this->tag      = null;
		$this->attr     = [];
		$this->parent   = null;
		$this->children = [];
	}

	/**
	 * Simply calls the outer text method.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->outerHtml();
	}

	/**
	 * Returns the id of this object.
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * Returns the parent of node.
	 *
	 * @return AbstractNode
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Sets the parent node.
	 *
	 * @param AbstractNode $parent
	 * @return $this
	 * @throws CircularException
	 */
	public function setParent(AbstractNode $parent)
	{
		// check integrity 
		if ($this->isDescendant($parent->id()))
		{
			throw new CircularException('Can not add descendant "'.$parent->id().'" as my parent.');
		}

		// remove from old parent
		if ( ! is_null($this->parent))
		{
			if ($this->parent->id() == $parent->id())
			{
				// already the parent
				return $this;
			}

			$this->parent->removeChild($this->id);
		}

		$this->parent = $parent;

		// assign child to parent
		$this->parent->addChild($this);

		//clear any cache
		$this->clear();

		return $this;
	}

	/**
	 * Sets the encoding class to this node and propagates it
	 * to all its children.
	 *
	 * @param Encode $encode
	 */
	public function propagateEncoding(Encode $encode)
	{
		$this->encode = $encode;
		$this->tag->setEncoding($encode);
		// check children
		foreach ($this->children as $id => $child)
		{
			/** @var AbstractNode $node */
			$node = $child['node'];
			$node->propagateEncoding($encode);
		}
	}

	/**
	 * Checks if this node has children.
	 *
	 * @return bool
	 */
	public function hasChildren()
	{
		return ! empty($this->children);
	}

	/**
	 * Returns the child by id.
	 *
	 * @param int $id
	 * @return AbstractNode
	 * @throws ChildNotFoundException
	 */
	public function getChild($id)
	{
		if ( ! isset($this->children[$id]))
		{
			throw new ChildNotFoundException("Child '$id' not found in this node.");
		}

		return $this->children[$id]['node'];
	}

	/**
	 * Returns a new array of child nodes
	 *
	 * @return array
	 */
	public function getChildren()
	{
		$nodes = [];
		try
		{
			$child = $this->firstChild();
			do
			{
				$nodes[] = $child;
				$child   = $this->nextChild($child->id());
			} while ( ! is_null($child));
		}
		catch (ChildNotFoundException $e)
		{
			// we are done looking for children
		}

		return $nodes;
	}

	/**
	 * Counts children
	 *
	 * @return int
	 */
	public function countChildren()
	{
		return count($this->children);
	}

	/**
	 * Adds a child node to this node and returns the id of the child for this
	 * parent.
	 *
	 * @param AbstractNode $child
	 * @return bool
	 * @throws CircularException
	 */
	public function addChild(AbstractNode $child)
	{
		$key = null;

		// check integrity
		if ($this->isAncestor($child->id()))
		{
			throw new CircularException('Can not add child. It is my ancestor.');
		}

		// check if child is itself
		if ($child->id() == $this->id)
		{
			throw new CircularException('Can not set itself as a child.');
		}

		if ($this->hasChildren())
		{
			if (isset($this->children[$child->id()]))
			{
				// we already have this child
				return false;
			}
			$sibling                      = $this->lastChild();
			$key                          = $sibling->id();
			$this->children[$key]['next'] = $child->id();
		}

		// add the child
		$this->children[$child->id()] = [
			'node' => $child,
			'next' => null,
			'prev' => $key,
		];

		// tell child I am the new parent
		$child->setParent($this);

		//clear any cache
		$this->clear();

		return true;
	}

	/**
	 * Removes the child by id.
	 *
	 * @param int $id
	 * @return $this
	 */
	public function removeChild($id)
	{
		if ( ! isset($this->children[$id]))
		{
			return $this;
		}

		// handle moving next and previous assignments.
		$next = $this->children[$id]['next'];
		$prev = $this->children[$id]['prev'];
		if ( ! is_null($next))
		{
			$this->children[$next]['prev'] = $prev;
		}
		if ( ! is_null($prev))
		{
			$this->children[$prev]['next'] = $next;
		}

		// remove the child
		unset($this->children[$id]);

		//clear any cache
		$this->clear();

		return $this;
	}

	/**
	 * Attempts to get the next child.
	 *
	 * @param int $id
	 * @return AbstractNode
	 * @uses $this->getChild()
	 */
	public function nextChild($id)
	{
		$child = $this->getChild($id);
		$next  = $this->children[$child->id()]['next'];

		return $this->getChild($next);
	}

	/**
	 * Attempts to get the previous child.
	 *
	 * @param int $id
	 * @return AbstractNode
	 * @uses $this->getChild()
	 */
	public function previousChild($id)
	{
		$child = $this->getchild($id);
		$next  = $this->children[$child->id()]['prev'];

		return $this->getChild($next);
	}

	/**
	 * Checks if the given node id is a child of the
	 * current node.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function isChild($id)
	{
		foreach ($this->children as $childId => $child)
		{
			if ($id == $childId)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the given node id is a descendant of the
	 * current node.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function isDescendant($id)
	{
		if ($this->isChild($id))
		{
			return true;
		}

		foreach ($this->children as $childId => $child)
		{
			/** @var AbstractNode $node */
			$node = $child['node'];
			if ($node->hasChildren() &&
				$node->isDescendant($id)
			)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the given node id is an ancestor of
	 * the current node.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function isAncestor($id)
	{
		if ( ! is_null($this->parent))
		{
			if ($this->parent->id() == $id)
			{
				return true;
			}

			return $this->parent->isAncestor($id);
		}

		return false;
	}

	/**
	 * Attempts to get an ancestor node by the given id.
	 *
	 * @param int $id
	 * @return null|AbstractNode
	 */
	public function getAncestor($id)
	{
		if ( ! is_null($this->parent))
		{
			if ($this->parent->id() == $id)
			{
				return $this->parent;
			}

			return $this->parent->getAncestor($id);
		}

		return null;
	}

	/**
	 * Shortcut to return the first child.
	 *
	 * @return AbstractNode
	 * @uses $this->getChild()
	 */
	public function firstChild()
	{
		reset($this->children);
		$key = key($this->children);

		return $this->getChild($key);
	}

	/**
	 * Attempts to get the last child.
	 *
	 * @return AbstractNode
	 */
	public function lastChild()
	{
		end($this->children);
		$key = key($this->children);

		return $this->getChild($key);
	}

	/**
	 * Attempts to get the next sibling.
	 *
	 * @return AbstractNode
	 * @throws ParentNotFoundException
	 */
	public function nextSibling()
	{
		if (is_null($this->parent))
		{
			throw new ParentNotFoundException('Parent is not set for this node.');
		}

		return $this->parent->nextChild($this->id);
	}

	/**
	 * Attempts to get the previous sibling
	 *
	 * @return AbstractNode
	 * @throws ParentNotFoundException
	 */
	public function previousSibling()
	{
		if (is_null($this->parent))
		{
			throw new ParentNotFoundException('Parent is not set for this node.');
		}

		return $this->parent->previousChild($this->id);
	}

	/**
	 * Gets the tag object of this node.
	 *
	 * @return Tag
	 */
	public function getTag()
	{
		return $this->tag;
	}

	/**
	 * A wrapper method that simply calls the getAttribute method
	 * on the tag of this node.
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		$attributes = $this->tag->getAttributes();
		foreach ($attributes as $name => $info)
		{
			$attributes[$name] = $info['value'];
		}

		return $attributes;
	}

	/**
	 * A wrapper method that simply calls the getAttribute method
	 * on the tag of this node.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getAttribute($key)
	{
		$attribute = $this->tag->getAttribute($key);
		if ( ! is_null($attribute))
		{
			$attribute = $attribute['value'];
		}

		return $attribute;
	}

	/**
	 * A wrapper method that simply calls the setAttribute method
	 * on the tag of this node.
	 *
	 * @param string $key
	 * @param string $value
	 * @return $this
	 */
	public function setAttribute($key, $value)
	{
		$this->tag->setAttribute($key, $value);

		return $this;
	}

	/**
	 * Function to locate a specific ancestor tag in the path to the root.
	 *
	 * @param  string $tag
	 * @return AbstractNode
	 * @throws ParentNotFoundException
	 */
	public function ancestorByTag($tag)
	{
		// Start by including ourselves in the comparison.
		$node = $this;

		while ( ! is_null($node))
		{
			if ($node->tag->name() == $tag)
			{
				return $node;
			}

			$node = $node->getParent();
		}

		throw new ParentNotFoundException('Could not find an ancestor with "'.$tag.'" tag');
	}

	/**
	 * Find elements by css selector
	 *
	 * @param string $selector
	 * @param int $nth
	 * @return array|AbstractNode
	 */
	public function find($selector, $nth = null)
	{
		$selector = new Selector($selector);
		$nodes    = $selector->find($this);

		if ( ! is_null($nth))
		{
			// return nth-element or array
			if (isset($nodes[$nth]))
			{
				return $nodes[$nth];
			}

			return null;
		}

		return $nodes;
	}

	/**
	 * Function to try a few tricks to determine the displayed size of an img on the page.
	 * NOTE: This will ONLY work on an IMG tag. Returns FALSE on all other tag types.
	 *
	 * Future enhancement:
	 * Look in the tag to see if there is a class or id specified that has a height or width attribute to it.
	 *
	 * Far future enhancement
	 * Look at all the parent tags of this image to see if they specify a class or id that has an img selector that specifies a height or width
	 * Note that in this case, the class or id will have the img sub-selector for it to apply to the image.
	 *
	 * ridiculously far future development
	 * If the class or id is specified in a SEPARATE css file that's not on the page, go get it and do what we were just doing for the ones on the page.
	 *
	 * @author John Schlick
	 * @return array an array containing the 'height' and 'width' of the image on the page or -1 if we can't figure it out.
	 */
	public function get_display_size()
	{
		$width  = -1;
		$height = -1;

		if ($this->tag->name() != 'img')
		{
			return false;
		}

		// See if there is a height or width attribute in the tag itself.
		if ( ! is_null($this->tag->getAttribute('width')))
		{
			$width = $this->tag->getAttribute('width');
		}

		if ( ! is_null($this->tag->getAttribute('height')))
		{
			$height = $this->tag->getAttribute('height');
		}

		// Now look for an inline style.
		if ( ! is_null($this->tag->getAttribute('style')))
		{
			// Thanks to user 'gnarf' from stackoverflow for this regular expression.
			$attributes = [];
			preg_match_all("/([\w-]+)\s*:\s*([^;]+)\s*;?/", $this->tag->getAttribute('style'), $matches, PREG_SET_ORDER);
			foreach ($matches as $match)
			{
				$attributes[$match[1]] = $match[2];
			}

			// If there is a width in the style attributes:
			if (isset($attributes['width']) and $width == -1)
			{
				// check that the last two characters are px (pixels)
				if (strtolower(substr($attributes['width'], -2)) == 'px')
				{
					$proposed_width = substr($attributes['width'], 0, -2);
					// Now make sure that it's an integer and not something stupid.
					if (filter_var($proposed_width, FILTER_VALIDATE_INT))
					{
						$width = $proposed_width;
					}
				}
			}

			// If there is a width in the style attributes:
			if (isset($attributes['height']) and $height == -1)
			{
				// check that the last two characters are px (pixels)
				if (strtolower(substr($attributes['height'], -2)) == 'px')
				{
					$proposed_height = substr($attributes['height'], 0, -2);
					// Now make sure that it's an integer and not something stupid.
					if (filter_var($proposed_height, FILTER_VALIDATE_INT))
					{
						$height = $proposed_height;
					}
				}
			}

		}

		$result = [
			'height' => $height,
			'width'  => $width,
		];

		return $result;
	}

	/**
	 * Gets the inner html of this node.
	 *
	 * @return string
	 */
	abstract public function innerHtml();

	/**
	 * Gets the html of this node, including it's own
	 * tag.
	 *
	 * @return string
	 */
	abstract public function outerHtml();

	/**
	 * Gets the text of this node (if there is any text).
	 *
	 * @return string
	 */
	abstract public function text();

	/**
	 * Call this when something in the node tree has changed. Like a child has been added
	 * or a parent has been changed.
	 *
	 * @return void
	 */
	abstract protected function clear();
}
