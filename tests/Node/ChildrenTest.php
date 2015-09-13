<?php

use PHPHtmlParser\Dom\MockNode as Node;

class NodeChildTest extends PHPUnit_Framework_TestCase {

	public function testGetParent()
	{
		$parent = new Node;
		$child  = new Node;
		$child->setParent($parent);
		$this->assertEquals($parent->id(), $child->getParent()->id());
	}

	public function testSetParentTwice()
	{
		$parent  = new Node;
		$parent2 = new Node;
		$child   = new Node;
		$child->setParent($parent);
		$child->setParent($parent2);
		$this->assertEquals($parent2->id(), $child->getParent()->id());
	}

	public function testNextSibling()
	{
		$parent = new Node;
		$child  = new Node;
		$child2 = new Node;
		$child->setParent($parent);
		$child2->setParent($parent);
		$this->assertEquals($child2->id(), $child->nextSibling()->id());
	}

	public function testPreviousSibling()
	{
		$parent = new Node;
		$child  = new Node;
		$child2 = new Node;
		$child->setParent($parent);
		$child2->setParent($parent);
		$this->assertEquals($child->id(), $child2->previousSibling()->id());
	}

	public function testGetChildren()
	{
		$parent = new Node;
		$child  = new Node;
		$child2 = new Node;
		$child->setParent($parent);
		$child2->setParent($parent);
		$this->assertEquals($child->id(), $parent->getChildren()[0]->id());
	}

	public function testCountChildren()
	{
		$parent = new Node;
		$child  = new Node;
		$child2 = new Node;
		$child->setParent($parent);
		$child2->setParent($parent);
		$this->assertEquals(2, $parent->countChildren());
	}
}
