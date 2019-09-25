<?php declare(strict_types=1);
namespace PHPHtmlParser\Dom;

use PHPHtmlParser\Exceptions\UnknownChildTypeException;
use PHPHtmlParser\Exceptions\ChildNotFoundException;

/**
 * Class HtmlNode
 *
 * @package PHPHtmlParser\Dom
 */
class HtmlNode extends InnerNode
{

    /**
     * Remembers what the innerHtml was if it was scanned previously.
     *
     * @var null|string
     */
    protected $innerHtml = null;

    /**
     * Remembers what the outerHtml was if it was scanned previously.
     *
     * @var null|string
     */
    protected $outerHtml = null;

    /**
     * Remembers what the text was if it was scanned previously.
     *
     * @var null|string
     */
    protected $text = null;

    /**
     * Remembers what the text was when we looked into all our
     * children nodes.
     *
     * @var null|string
     */
    protected $textWithChildren = null;

    /**
     * Sets up the tag of this node.
     *
     * @param string|Tag $tag
     */
    public function __construct($tag)
    {
        if ( ! $tag instanceof Tag) {
            $tag = new Tag($tag);
        }
        $this->tag = $tag;
        parent::__construct();
    }

    /**
     * @param bool $htmlSpecialCharsDecode
     * @return void
     */
    public function setHtmlSpecialCharsDecode($htmlSpecialCharsDecode = false): void
    {
        parent::setHtmlSpecialCharsDecode($htmlSpecialCharsDecode);
        if ( ! is_null($this->tag)) {
            $this->tag->setHtmlSpecialCharsDecode($htmlSpecialCharsDecode);
        }
    }

    /**
     * Gets the inner html of this node.
     * @return string
     * @throws ChildNotFoundException
     * @throws UnknownChildTypeException
     */
    public function innerHtml(): string
    {
        if ( ! $this->hasChildren()) {
            // no children
            return '';
        }

        if ( ! is_null($this->innerHtml)) {
            // we already know the result.
            return $this->innerHtml;
        }

        $child  = $this->firstChild();
        $string = '';

        // continue to loop until we are out of children
        while ( ! is_null($child)) {
            if ($child instanceof TextNode) {
                $string .= $child->text();
            } elseif ($child instanceof HtmlNode) {
                $string .= $child->outerHtml();
            } else {
                throw new UnknownChildTypeException('Unknown child type "'.get_class($child).'" found in node');
            }

            try {
                $child = $this->nextChild($child->id());
            } catch (ChildNotFoundException $e) {
                // no more children
                $child = null;
            }
        }

        // remember the results
        $this->innerHtml = $string;

        return $string;
    }

    /**
     * Gets the html of this node, including it's own
     * tag.
     * @return string
     * @throws ChildNotFoundException
     * @throws UnknownChildTypeException
     */
    public function outerHtml(): string
    {
        // special handling for root
        if ( ! is_null($this->tag) && $this->tag->name() == 'root') {
            return $this->innerHtml();
        }

        if ( ! is_null($this->outerHtml)) {
            // we already know the results.
            return $this->outerHtml;
        }

        $return = '';

        if ( ! is_null($this->tag)) {
            $return .= $this->tag->makeOpeningTag();
            if ($this->tag->isSelfClosing()) {
                // ignore any children... there should not be any though
                return $return;
            }
        }

        // get the inner html
        $return .= $this->innerHtml();

        if ( ! is_null($this->tag)) {
            // add closing tag
            $return .= $this->tag->makeClosingTag();
        }

        // remember the results
        $this->outerHtml = $return;

        return $return;
    }

    /**
     * Gets the text of this node (if there is any text). Or get all the text
     * in this node, including children.
     *
     * @param bool $lookInChildren
     * @return string
     */
    public function text(bool $lookInChildren = false): string
    {
        if ($lookInChildren) {
            if ( ! is_null($this->textWithChildren)) {
                // we already know the results.
                return $this->textWithChildren;
            }
        } elseif ( ! is_null($this->text)) {
            // we already know the results.
            return $this->text;
        }

        // find out if this node has any text children
        $text = '';
        foreach ($this->children as $child) {
            /** @var AbstractNode $node */
            $node = $child['node'];
            if ($node instanceof TextNode) {
                $text .= $child['node']->text;
            } elseif ($lookInChildren &&
                $node instanceof HtmlNode
            ) {
                $text .= $node->text($lookInChildren);
            }
        }

        // remember our result
        if ($lookInChildren) {
            $this->textWithChildren = $text;
        } else {
            $this->text = $text;
        }

        return $text;
    }

    /**
     * Call this when something in the node tree has changed. Like a child has been added
     * or a parent has been changed.
     */
    protected function clear(): void
    {
        $this->innerHtml = null;
        $this->outerHtml = null;
        $this->text      = null;
        $this->textWithChildren = null;

        if (!is_null($this->parent)) {
            $this->parent->clear();
        }
    }

    /**
     * Returns all children of this html node.
     *
     * @return array
     */
    protected function getIteratorArray(): array
    {
        return $this->getChildren();
    }
}
