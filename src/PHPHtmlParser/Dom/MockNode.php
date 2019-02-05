<?php
namespace PHPHtmlParser\Dom;

/**
 * This mock object is used solely for testing the abstract
 * class Node with out any potential side effects caused
 * by testing a supper class of Node.
 *
 * This object is not to be used for any other reason.
 */
class MockNode extends InnerNode
{

    /**
     * Mock of innner html.
     */
    public function innerHtml(): string
    {
        return '';
    }

    /**
     * Mock of outer html.
     */
    public function outerHtml(): string
    {
        return '';
    }

    /**
     * Mock of text.
     */
    public function text(): string
    {
        return '';
    }

    /**
     * Clear content of this node
     */
    protected function clear(): void
    {
        $this->innerHtml = null;
        $this->outerHtml = null;
        $this->text      = null;
        if (is_null($this->parent) === false) {
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
