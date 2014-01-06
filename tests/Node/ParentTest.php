<?php

use PHPHtmlParser\Dom\MockNode as Node;

class NodeParentTest extends PHPUnit_Framework_TestCase {

	public function testHasChild()
	{
		$parent = new Node;
		$child  = new Node;
		$parent->addChild($child);
		$this->assertTrue($parent->hasChildren());
	}

	public function testHasChildNoChildren()
	{
		$node = new Node;
		$this->assertFalse($node->hasChildren());
	}

	public function testAddChild()
	{
		$parent = new Node;
		$child  = new Node;
		$this->assertTrue($parent->addChild($child));
	}

	public function testAddChildTwoParent()
	{
		$parent  = new Node;
		$parent2 = new Node;
		$child   = new Node;
		$parent->addChild($child);
		$parent2->addChild($child);
		$this->assertFalse($parent->hasChildren());
	}

	public function testGetChild()
	{
		$parent = new Node;
		$child  = new Node;
		$child2 = new Node;
		$parent->addChild($child);
		$parent->addChild($child2);
		$this->assertTrue($parent->getChild($child2->id()) instanceof Node);
	}

	public function testRemoveChild()
	{
		$parent = new Node;
		$child  = new Node;
		$parent->addChild($child);
		$parent->removeChild($child->id());
		$this->assertFalse($parent->hasChildren());
	}

	public function testNextChild()
	{
		$parent = new Node;
		$child  = new Node;
		$child2 = new Node;
		$parent->addChild($child);
		$parent->addChild($child2);
		
		$this->assertEquals($child2->id(), $parent->nextChild($child->id())->id());
	}

	public function testNextChildWithRemove()
	{
		$parent = new Node;
		$child  = new Node;
		$child2 = new Node;
		$child3 = new Node;
		$parent->addChild($child);
		$parent->addChild($child2);
		$parent->addChild($child3);

		$parent->removeChild($child2->id());
		$this->assertEquals($child3->id(), $parent->nextChild($child->id())->id());
	}

	public function testPreviousChild()
	{
		$parent = new Node;
		$child  = new Node;
		$child2 = new Node;
		$parent->addChild($child);
		$parent->addChild($child2);
		
		$this->assertEquals($child->id(), $parent->previousChild($child2->id())->id());
	}

	public function testPreviousChildWithRemove()
	{
		$parent = new Node;
		$child  = new Node;
		$child2 = new Node;
		$child3 = new Node;
		$parent->addChild($child);
		$parent->addChild($child2);
		$parent->addChild($child3);

		$parent->removeChild($child2->id());
		$this->assertEquals($child->id(), $parent->previousChild($child3->id())->id());
	}

	public function testFirstChild()
	{
		$parent = new Node;
		$child  = new Node;
		$child2 = new Node;
		$child3 = new Node;
		$parent->addChild($child);
		$parent->addChild($child2);
		$parent->addChild($child3);

		$this->assertEquals($child->id(), $parent->firstChild()->id());
	}

	public function testLastChild()
	{
		$parent = new Node;
		$child  = new Node;
		$child2 = new Node;
		$child3 = new Node;
		$parent->addChild($child);
		$parent->addChild($child2);
		$parent->addChild($child3);

		$this->assertEquals($child3->id(), $parent->lastChild()->id());
	}
}
