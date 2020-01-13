<?php declare(strict_types=1);
namespace PHPHtmlParser\Dom;

use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ParentNotFoundException;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Selector\Selector;
use PHPHtmlParser\Selector\Parser as SelectorParser;
use stringEncode\Encode;
use PHPHtmlParser\Finder;

/**
 * Dom node object.
 * @property string    $outerhtml
 * @property string    $innerhtml
 * @property string    $text
 * @property int       $prev
 * @property int       $next
 * @property Tag       $tag
 * @property InnerNode $parent
 */
abstract class AbstractNode
{
    /**
     * @var int
     */
    private static $count = 0;

    /**
     * Contains the tag name/type
     *
     * @var ?Tag
     */
    protected $tag = null;

    /**
     * Contains a list of attributes on this tag.
     *
     * @var array
     */
    protected $attr = [];

    /**
     * Contains the parent Node.
     *
     * @var ?InnerNode
     */
    protected $parent = null;

    /**
     * The unique id of the class. Given by PHP.
     *
     * @var int
     */
    protected $id;

    /**
     * The encoding class used to encode strings.
     *
     * @var mixed
     */
    protected $encode;

    /**
     * An array of all the children.
     *
     * @var array
     */
    protected $children = [];

    /**
     * @var bool
     */
    protected $htmlSpecialCharsDecode = false;

    /**
     * Creates a unique id for this node.
     */
    public function __construct()
    {
        $this->id = self::$count;
        self::$count++;
    }

    /**
     * Magic get method for attributes and certain methods.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
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
                return $this->getParent();
        }

        return null;
    }

    /**
     * Attempts to clear out any object references.
     */
    public function __destruct()
    {
        $this->tag      = null;
        $this->parent   = null;
        $this->attr     = [];
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
     * @param bool $htmlSpecialCharsDecode
     * @return void
     */
    public function setHtmlSpecialCharsDecode($htmlSpecialCharsDecode = false): void
    {
        $this->htmlSpecialCharsDecode = $htmlSpecialCharsDecode;
    }


    /**
     * Reset node counter
     *
     * @return void
     */
    public static function resetCount()
    {
        self::$count = 0;
    }

    /**
     * Returns the id of this object.
     *
     * @return int
     */
    public function id(): int
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
     * @param InnerNode $parent
     * @return AbstractNode
     * @throws ChildNotFoundException
     * @throws CircularException
     */
    public function setParent(InnerNode $parent): AbstractNode
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
        $this->parent->clear();
        $this->clear();
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
    public function isAncestor(int $id): Bool
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
    public function getAncestor(int $id)
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
     * Checks if the current node has a next sibling.
     *
     * @return bool
     */
    public function hasNextSibling(): bool
    {
        try
        {
            $this->nextSibling();

            // sibling found, return true;
            return true;
        }
        catch (ParentNotFoundException $e)
        {
            // no parent, no next sibling
            unset($e);
            return false;
        }
        catch (ChildNotFoundException $e)
        {
            // no sibling found
            unset($e);
            return false;
        }
    }

    /**
     * Attempts to get the next sibling.
     * @return AbstractNode
     * @throws ChildNotFoundException
     * @throws ParentNotFoundException
     */
    public function nextSibling(): AbstractNode
    {
        if (is_null($this->parent)) {
            throw new ParentNotFoundException('Parent is not set for this node.');
        }

        return $this->parent->nextChild($this->id);
    }

    /**
     * Attempts to get the previous sibling.
     * @return AbstractNode
     * @throws ChildNotFoundException
     * @throws ParentNotFoundException
     */
    public function previousSibling(): AbstractNode
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
    public function getTag(): Tag
    {
        return $this->tag;
    }

    /**
     * A wrapper method that simply calls the getAttribute method
     * on the tag of this node.
     *
     * @return array
     */
    public function getAttributes(): array
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
     * @return string|null
     */
    public function getAttribute(string $key): ?string
    {
        $attribute = $this->tag->getAttribute($key);
        $attributeValue = $attribute['value'];

        return $attributeValue;
    }

    /**
     * A wrapper method that simply calls the hasAttribute method
     * on the tag of this node.
     *
     * @param string $key
     * @return bool
     */
    public function hasAttribute(string $key): bool
    {
        return $this->tag->hasAttribute($key);
    }

    /**
     * A wrapper method that simply calls the setAttribute method
     * on the tag of this node.
     *
     * @param string $key
     * @param string|array $value
     * @return AbstractNode
     * @chainable
     */
    public function setAttribute(string $key, $value): AbstractNode
    {
        $this->tag->setAttribute($key, $value);

        //clear any cache
        $this->clear();

        return $this;
    }

    /**
     * A wrapper method that simply calls the removeAttribute method
     * on the tag of this node.
     *
     * @param string $key
     * @return void
     */
    public function removeAttribute(string $key): void
    {
        $this->tag->removeAttribute($key);

        //clear any cache
        $this->clear();
    }

    /**
     * A wrapper method that simply calls the removeAllAttributes
     * method on the tag of this node.
     *
     * @return void
     */
    public function removeAllAttributes(): void
    {
        $this->tag->removeAllAttributes();

        //clear any cache
        $this->clear();
    }
    /**
     * Function to locate a specific ancestor tag in the path to the root.
     *
     * @param  string $tag
     * @return AbstractNode
     * @throws ParentNotFoundException
     */
    public function ancestorByTag(string $tag): AbstractNode
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
     * @param string   $selector
     * @param int|null $nth
     * @param bool     $depthFirst
     * @return mixed|Collection|null
     * @throws ChildNotFoundException
     */
    public function find(string $selector, int $nth = null, bool $depthFirst = false)
    {
        $selector = new Selector($selector, new SelectorParser());
        $selector->setDepthFirstFind($depthFirst);
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
     * Find node by id
     * @param int $id
     * @return bool|AbstractNode
     * @throws ChildNotFoundException
     * @throws ParentNotFoundException
     */
    public function findById(int $id)
    {
        $finder= new Finder($id);

        return $finder->find($this);
    }


    /**
     * Gets the inner html of this node.
     *
     * @return string
     */
    abstract public function innerHtml(): string;

    /**
     * Gets the html of this node, including it's own
     * tag.
     *
     * @return string
     */
    abstract public function outerHtml(): string;

    /**
     * Gets the text of this node (if there is any text).
     *
     * @return string
     */
    abstract public function text(): string;

    /**
     * Call this when something in the node tree has changed. Like a child has been added
     * or a parent has been changed.
     *
     * @return void
     */
    abstract protected function clear(): void;

    /**
     * Check is node type textNode
     *
     * @return boolean
     */
    public function isTextNode(): bool 
    {

        return false;
    }
}
