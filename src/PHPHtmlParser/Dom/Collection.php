<?php
namespace PHPHtmlParser\Dom;

use IteratorAggregate;
use ArrayIterator;
use ArrayAccess;
use Countable;

class Collection implements IteratorAggregate, ArrayAccess, Countable {
	
	/**
	 * The collection of Nodes.
	 *
	 * @param array
	 */
	protected $collection = [];

	/** 
	 * Returns the count of the collection.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->collection);
	}

	/** 
	 * Returns an iterator for the collection.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return ArrayIterator($this->collection);
	}

	/**
	 * Set an attribute by the given offset
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 */
    public function offsetSet($offset, $value) 
    {
        if (is_null($offset)) 
        {
            $this->collection[] = $value;
        } 
        else 
        {
            $this->collection[$offset] = $value;
        }
    }

	/**
	 * Checks if an offset exists.
	 *
	 * @param mixed $offset
	 * @return bool
	 */
    public function offsetExists($offset) 
    {
        return isset($this->collection[$offset]);
    }

	/**
	 * Unset a collection Node.
	 *
	 * @param mixed $offset
	 */
    public function offsetUnset($offset) 
    {
        unset($this->collection[$offset]);
    }

	/**
	 * Gets a node at the given offset, or null
	 *
	 * @param mixed $offset
	 * @return $offset
	 */
    public function offsetGet($offset) 
    {
        return isset($this->collection[$offset]) ? $this->collection[$offset] : null;
    }
}
