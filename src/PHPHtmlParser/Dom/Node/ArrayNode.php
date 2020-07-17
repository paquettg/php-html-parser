<?php

declare(strict_types=1);

namespace PHPHtmlParser\Dom\Node;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Dom node object which will allow users to use it as
 * an array.
 */
abstract class ArrayNode extends AbstractNode implements IteratorAggregate, Countable
{
    /**
     * Gets the iterator.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->getIteratorArray());
    }

    /**
     * Returns the count of the iterator array.
     */
    public function count(): int
    {
        return \count($this->getIteratorArray());
    }

    /**
     * Returns the array to be used the the iterator.
     */
    abstract protected function getIteratorArray(): array;
}
