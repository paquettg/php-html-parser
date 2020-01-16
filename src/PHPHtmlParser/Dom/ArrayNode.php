<?php declare(strict_types=1);
namespace PHPHtmlParser\Dom;

use Countable;
use ArrayIterator;
use IteratorAggregate;

/**
 * Dom node object which will allow users to use it as
 * an array.
 */
abstract class ArrayNode extends AbstractNode implements IteratorAggregate, Countable
{

    /**
     * Gets the iterator
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->getIteratorArray());
    }

    /**
     * Returns the count of the iterator array.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->getIteratorArray());
    }

    /**
     * Returns the array to be used the the iterator.
     *
     * @return array
     */
    abstract protected function getIteratorArray(): array;
}
