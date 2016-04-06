<?php
namespace PHPHtmlParser\Dom;

use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ParentNotFoundException;
use PHPHtmlParser\Selector;
use stringEncode\Encode;

/**
 * Dom node object.
 *
 * @property string outerhtml
 * @property string innerhtml
 * @property string text
 * @property \PHPHtmlParser\Dom\Tag tag
 * @property InnerNode parent
 */
abstract class AbstractNode
{

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
     * Contains the parent Node.
     *
     * @var InnerNode
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
        if ( ! is_null($this->getAttribute($key))) {
            return $this->getAttribute($key);
        }
        switch (strtolower($key)) {
            case 'outerhtml':
                return $this->outerHtml();
            case 'innerhtml':
                return $this->innerHtml();
            case 'text':
                return $this->text();
            case 'tag':
                return $this->getTag();
            case 'parent':
                $this->getParent();
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
     * @param InnerNode $parent
     * @return $this
     * @throws CircularException
     */
    public function setParent(InnerNode $parent)
    {
        // remove from old parent
        if ( ! is_null($this->parent)) {
            if ($this->parent->id() == $parent->id()) {
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
     * Removes this node and all its children from the
     * DOM tree.
     *
     * @return void
     */
    public function delete()
    {
        if ( ! is_null($this->parent)) {
            $this->parent->removeChild($this->id);
        }

        $this->parent = null;
    }

    /**
     * Sets the encoding class to this node.
     *
     * @param Encode $encode
     * @return void
     */
    public function propagateEncoding(Encode $encode)
    {
        $this->encode = $encode;
        $this->tag->setEncoding($encode);
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
        if ( ! is_null($this->getAncestor($id))) {
            return true;
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
        if ( ! is_null($this->parent)) {
            if ($this->parent->id() == $id) {
                return $this->parent;
            }

            return $this->parent->getAncestor($id);
        }

        return null;
    }

    /**
     * Attempts to get the next sibling.
     *
     * @return AbstractNode
     * @throws ParentNotFoundException
     */
    public function nextSibling()
    {
        if (is_null($this->parent)) {
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
        if (is_null($this->parent)) {
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
        foreach ($attributes as $name => $info) {
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
        if ( ! is_null($attribute)) {
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
     * A wrapper method that simply calls the removeAttribute method
     * on the tag of this node.
     *
     * @param string $key
     * @return void
     */
    public function removeAttribute($key)
    {
        $this->tag->removeAttribute($key);
    }

    /**
     * A wrapper method that simply calls the removeAllAttributes
     * method on the tag of this node.
     *
     * @return void
     */
    public function removeAllAttributes()
    {
        $this->tag->removeAllAttributes();
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

        while ( ! is_null($node)) {
            if ($node->tag->name() == $tag) {
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

        if ( ! is_null($nth)) {
            // return nth-element or array
            if (isset($nodes[$nth])) {
                return $nodes[$nth];
            }

            return null;
        }

        return $nodes;
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
