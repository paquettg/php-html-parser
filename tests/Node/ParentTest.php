<?php

declare(strict_types=1);
require_once 'tests/data/MockNode.php';

use PHPHtmlParser\Dom\Node\MockNode as Node;
use PHPUnit\Framework\TestCase;

class NodeParentTest extends TestCase
{
    public function testHasChild()
    {
        $parent = new Node();
        $child = new Node();
        $parent->addChild($child);
        $this->assertTrue($parent->hasChildren());
    }

    public function testHasChildNoChildren()
    {
        $node = new Node();
        $this->assertFalse($node->hasChildren());
    }

    public function testAddChild()
    {
        $parent = new Node();
        $child = new Node();
        $this->assertTrue($parent->addChild($child));
    }

    public function testAddChildTwoParent()
    {
        $parent = new Node();
        $parent2 = new Node();
        $child = new Node();
        $parent->addChild($child);
        $parent2->addChild($child);
        $this->assertFalse($parent->hasChildren());
    }

    public function testGetChild()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $parent->addChild($child);
        $parent->addChild($child2);
        $this->assertTrue($parent->getChild($child2->id()) instanceof Node);
    }

    public function testRemoveChild()
    {
        $parent = new Node();
        $child = new Node();
        $parent->addChild($child);
        $parent->removeChild($child->id());
        $this->assertFalse($parent->hasChildren());
    }

    public function testRemoveChildNotExists()
    {
        $parent = new Node();
        $parent->removeChild(1);
        $this->assertFalse($parent->hasChildren());
    }

    public function testNextChild()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $parent->addChild($child);
        $parent->addChild($child2);

        $this->assertEquals($child2->id(), $parent->nextChild($child->id())->id());
    }

    public function testHasNextChild()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $parent->addChild($child);
        $parent->addChild($child2);

        $this->assertEquals($child2->id(), $parent->hasNextChild($child->id()));
    }

    public function testHasNextChildNotExists()
    {
        $this->expectException(\PHPHtmlParser\Exceptions\ChildNotFoundException::class);
        $parent = new Node();
        $child = new Node();
        $parent->hasNextChild($child->id());
    }

    public function testNextChildWithRemove()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $child3 = new Node();
        $parent->addChild($child);
        $parent->addChild($child2);
        $parent->addChild($child3);

        $parent->removeChild($child2->id());
        $this->assertEquals($child3->id(), $parent->nextChild($child->id())->id());
    }

    public function testPreviousChild()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $parent->addChild($child);
        $parent->addChild($child2);

        $this->assertEquals($child->id(), $parent->previousChild($child2->id())->id());
    }

    public function testPreviousChildWithRemove()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $child3 = new Node();
        $parent->addChild($child);
        $parent->addChild($child2);
        $parent->addChild($child3);

        $parent->removeChild($child2->id());
        $this->assertEquals($child->id(), $parent->previousChild($child3->id())->id());
    }

    public function testFirstChild()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $child3 = new Node();
        $parent->addChild($child);
        $parent->addChild($child2);
        $parent->addChild($child3);

        $this->assertEquals($child->id(), $parent->firstChild()->id());
    }

    public function testLastChild()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $child3 = new Node();
        $parent->addChild($child);
        $parent->addChild($child2);
        $parent->addChild($child3);

        $this->assertEquals($child3->id(), $parent->lastChild()->id());
    }

    public function testInsertBeforeFirst()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $child3 = new Node();
        $parent->addChild($child2);
        $parent->addChild($child3);

        $parent->insertBefore($child, $child2->id());

        $this->assertTrue($parent->isChild($child->id()));
        $this->assertEquals($parent->firstChild()->id(), $child->id());
        $this->assertEquals($child->nextSibling()->id(), $child2->id());
        $this->assertEquals($child2->nextSibling()->id(), $child3->id());
        $this->assertEquals($parent->lastChild()->id(), $child3->id());
    }

    public function testInsertBeforeLast()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $child3 = new Node();
        $parent->addChild($child);
        $parent->addChild($child3);

        $parent->insertBefore($child2, $child3->id());

        $this->assertTrue($parent->isChild($child2->id()));
        $this->assertEquals($parent->firstChild()->id(), $child->id());
        $this->assertEquals($child->nextSibling()->id(), $child2->id());
        $this->assertEquals($child2->nextSibling()->id(), $child3->id());
        $this->assertEquals($parent->lastChild()->id(), $child3->id());
    }

    public function testInsertAfterFirst()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $child3 = new Node();
        $parent->addChild($child);
        $parent->addChild($child3);

        $parent->insertAfter($child2, $child->id());

        $this->assertTrue($parent->isChild($child2->id()));
        $this->assertEquals($parent->firstChild()->id(), $child->id());
        $this->assertEquals($child->nextSibling()->id(), $child2->id());
        $this->assertEquals($child2->nextSibling()->id(), $child3->id());
        $this->assertEquals($parent->lastChild()->id(), $child3->id());
    }

    public function testInsertAfterLast()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $child3 = new Node();
        $parent->addChild($child);
        $parent->addChild($child2);

        $parent->insertAfter($child3, $child2->id());

        $this->assertTrue($parent->isChild($child2->id()));
        $this->assertEquals($parent->firstChild()->id(), $child->id());
        $this->assertEquals($child->nextSibling()->id(), $child2->id());
        $this->assertEquals($child2->nextSibling()->id(), $child3->id());
        $this->assertEquals($parent->lastChild()->id(), $child3->id());
    }

    public function testReplaceChild()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $child3 = new Node();
        $parent->addChild($child);
        $parent->addChild($child2);
        $parent->replaceChild($child->id(), $child3);

        $this->assertFalse($parent->isChild($child->id()));
    }

    public function testSetParentDescendantException()
    {
        $this->expectException(\PHPHtmlParser\Exceptions\CircularException::class);
        $parent = new Node();
        $child = new Node();
        $parent->addChild($child);
        $parent->setParent($child);
    }

    public function testAddChildAncestorException()
    {
        $this->expectException(\PHPHtmlParser\Exceptions\CircularException::class);
        $parent = new Node();
        $child = new Node();
        $parent->addChild($child);
        $child->addChild($parent);
    }

    public function testAddItselfAsChild()
    {
        $this->expectException(\PHPHtmlParser\Exceptions\CircularException::class);
        $parent = new Node();
        $parent->addChild($parent);
    }

    public function testIsAncestorParent()
    {
        $parent = new Node();
        $child = new Node();
        $parent->addChild($child);
        $this->assertTrue($child->isAncestor($parent->id()));
    }

    public function testGetAncestor()
    {
        $parent = new Node();
        $child = new Node();
        $parent->addChild($child);
        $ancestor = $child->getAncestor($parent->id());
        $this->assertEquals($parent->id(), $ancestor->id());
    }

    public function testGetGreatAncestor()
    {
        $parent = new Node();
        $child = new Node();
        $child2 = new Node();
        $parent->addChild($child);
        $child->addChild($child2);
        $ancestor = $child2->getAncestor($parent->id());
        $this->assertNotNull($ancestor);
        $this->assertEquals($parent->id(), $ancestor->id());
    }

    public function testGetAncestorNotFound()
    {
        $parent = new Node();
        $ancestor = $parent->getAncestor(1);
        $this->assertNull($ancestor);
    }
}
