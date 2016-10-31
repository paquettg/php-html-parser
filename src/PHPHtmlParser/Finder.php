<?php

namespace PHPHtmlParser;

use PHPHtmlParser\Dom\AbstractNode;

class Finder
{
    private $id;

    /**
     * Finder constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     *
     * Find node in tree
     *
     * @param AbstractNode $node
     * @return bool|AbstractNode
     */
    public function find(AbstractNode $node)
    {

        if (!$node->id()) {
            return $this->find($node->firstChild());
        }

        if ($node->id() == $this->id) {
            return $node;
        }

        if ($node->hasNextSibling()) {
            $nextSibling = $node->nextSibling();
            if ($nextSibling->id() == $this->id) {
                return $nextSibling;
            }
            if ($nextSibling->id() > $this->id) {
                return $this->find($node->firstChild());
            }
            if ($nextSibling->id() < $this->id) {
                return $this->find($nextSibling);
            }
        } else {
            return $this->find($node->firstChild());
        }

        return false;
    }

}