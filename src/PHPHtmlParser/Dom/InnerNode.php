<?php
namespace PHPHtmlParser\Dom;

use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use stringEncode\Encode;

/**
 * Inner node of the html tree, might have children.
 *
 * @package PHPHtmlParser\Dom
 */
abstract class InnerNode extends ArrayNode
{

    /**
     * An array of all the children.
     *
     * @var array
     */
    protected $children = [];

    /**
     * Sets the encoding class to this node and propagates it
     * to all its children.
     *
     * @param Encode $encode
     * @return void
     */
    public function propagateEncoding(Encode $encode)
    {
        $this->encode = $encode;
        $this->tag->setEncoding($encode);
        // check children
        foreach ($this->children as $id => $child) {
            /** @var AbstractNode $node */
            $node = $child['node'];
            $node->propagateEncoding($encode);
        }
    }

    /**
     * Checks if this node has children.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return ! empty($this->children);
    }

    /**
     * Returns the child by id.
     *
     * @param int $id
     * @return AbstractNode
     * @throws ChildNotFoundException
     */
    public function getChild($id)
    {
        if ( ! isset($this->children[$id])) {
            throw new ChildNotFoundException("Child '$id' not found in this node.");
        }

        return $this->children[$id]['node'];
    }

    /**
     * Returns a new array of child nodes
     *
     * @return array
     */
    public function getChildren()
    {
        $nodes = [];
        try {
            $child = $this->firstChild();
            do {
                $nodes[] = $child;
                $child   = $this->nextChild($child->id());
            } while ( ! is_null($child));
        } catch (ChildNotFoundException $e) {
            // we are done looking for children
        }

        return $nodes;
    }

    /**
     * Counts children
     *
     * @return int
     */
    public function countChildren()
    {
        return count($this->children);
    }

    /**
     * Adds a child node to this node and returns the id of the child for this
     * parent.
     *
     * @param AbstractNode $child
     * @return bool
     * @throws CircularException
     */
    public function addChild(AbstractNode $child, $before = null)
    {
        $key = null;

        // check integrity
        if ($this->isAncestor($child->id())) {
            throw new CircularException('Can not add child. It is my ancestor.');
        }

        // check if child is itself
        if ($child->id() == $this->id) {
            throw new CircularException('Can not set itself as a child.');
        }

		$next = null;

        if ($this->hasChildren()) {
			if (isset($this->children[$child->id()])) {
				// we already have this child
				return false;
			}

			if ($before) {
				if (!isset($this->children[$before])) {
					return false;
				}

				$key = $this->children[$before]['prev'];

				if($key){
					$this->children[$key]['next'] = $child->id();
				}

				$this->children[$before]['prev'] = $child->id();
				$next = $before;
			} else {
				$sibling = $this->lastChild();
				$key = $sibling->id();

				$this->children[$key]['next'] = $child->id();
			}
        }

		$keys = array_keys($this->children);

		$insert = [
			'node' => $child,
			'next' => $next,
			'prev' => $key,
		];

		$index = $key ? (array_search($key, $keys, true) + 1) : 0;
		array_splice($keys, $index, 0, $child->id());

		$children = array_values($this->children);
		array_splice($children, $index, 0, [$insert]);

		// add the child
		$this->children = array_combine($keys, $children);

        // tell child I am the new parent
        $child->setParent($this);

        //clear any cache
        $this->clear();

        return true;
    }

	/**
	 * Insert element before child with provided id
	 *
	 * @param AbstractNode $child
	 * @return bool
	 * @param int $id
	 */
	public function insertBefore(AbstractNode $child, $id){
		$this->addChild($child, $id);
	}

	/**
	 * Insert element before after with provided id
	 *
	 * @param AbstractNode $child
	 * @return bool
	 * @param int $id
	 */
	public function insertAfter(AbstractNode $child, $id){
		if (!isset($this->children[$id])) {
			return false;
		}

		if ($this->children[$id]['next']) {
			return $this->addChild($child, $this->children[$id]['next']);
		}

		return $this->addChild($child);
	}

    /**
     * Removes the child by id.
     *
     * @param int $id
     * @return $this
     */
    public function removeChild($id)
    {
        if ( ! isset($this->children[$id])) {
            return $this;
        }

        // handle moving next and previous assignments.
        $next = $this->children[$id]['next'];
        $prev = $this->children[$id]['prev'];
        if ( ! is_null($next)) {
            $this->children[$next]['prev'] = $prev;
        }
        if ( ! is_null($prev)) {
            $this->children[$prev]['next'] = $next;
        }

        // remove the child
        unset($this->children[$id]);

        //clear any cache
        $this->clear();

        return $this;
    }

    /**
     * Check if has next Child
     *
     * @param $id childId
     * @return mixed
     */
    public function hasNextChild($id)
    {
        $child= $this->getChild($id);
        return $this->children[$child->id()]['next'];
    }

    /**
     * Attempts to get the next child.
     *
     * @param int $id
     * @return AbstractNode
     * @uses $this->getChild()
     * @throws ChildNotFoundException
     */
    public function nextChild($id)
    {
        $child = $this->getChild($id);
        $next  = $this->children[$child->id()]['next'];

        return $this->getChild($next);
    }

    /**
     * Attempts to get the previous child.
     *
     * @param int $id
     * @return AbstractNode
     * @uses $this->getChild()
     * @throws ChildNotFoundException
     */
    public function previousChild($id)
    {
        $child = $this->getchild($id);
        $next  = $this->children[$child->id()]['prev'];

        return $this->getChild($next);
    }

    /**
     * Checks if the given node id is a child of the
     * current node.
     *
     * @param int $id
     * @return bool
     */
    public function isChild($id)
    {
        foreach ($this->children as $childId => $child) {
            if ($id == $childId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Removes the child with id $childId and replace it with the new child
     * $newChild.
     *
     * @param int $childId
     * @param AbstractNode $newChild
     * @throws ChildNotFoundException
     */
    public function replaceChild($childId, AbstractNode $newChild)
    {
        $oldChild = $this->children[$childId];

        $newChild->prev = $oldChild['prev'];
        $newChild->next = $oldChild['next'];

        $keys = array_keys($this->children);
        $index = array_search($childId, $keys, true);
        $keys[$index] = $newChild->id();
        $this->children = array_combine($keys, $this->children);
        $this->children[$newChild->id()] = array(
            'prev' => $oldChild['prev'],
            'node' => $newChild,
            'next' => $oldChild['next']
        );

        if ($oldChild['prev'] && isset($this->children[$newChild->prev])) {
            $this->children[$oldChild['prev']]['next'] = $newChild->id();
        }

        if ($oldChild['next'] && isset($this->children[$newChild->next])) {
            $this->children[$oldChild['next']]['prev'] = $newChild->id();
        }
    }

    /**
     * Shortcut to return the first child.
     *
     * @return AbstractNode
     * @uses $this->getChild()
     */
    public function firstChild()
    {
        reset($this->children);
        $key = key($this->children);

        return $this->getChild($key);
    }

    /**
     * Attempts to get the last child.
     *
     * @return AbstractNode
     */
    public function lastChild()
    {
        end($this->children);
        $key = key($this->children);

        return $this->getChild($key);
    }

    /**
     * Checks if the given node id is a descendant of the
     * current node.
     *
     * @param int $id
     * @return bool
     */
    public function isDescendant($id)
    {
        if ($this->isChild($id)) {
            return true;
        }

        foreach ($this->children as $childId => $child) {
            /** @var InnerNode $node */
            $node = $child['node'];
            if ($node instanceof InnerNode &&
                $node->hasChildren() &&
                $node->isDescendant($id)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets the parent node.
     *
     * @param InnerNode $parent
     * @return $this
     * @throws CircularException
     */
    public function setParent(InnerNode $parent)
    {
        // check integrity
        if ($this->isDescendant($parent->id())) {
            throw new CircularException('Can not add descendant "'.$parent->id().'" as my parent.');
        }

        return parent::setParent($parent);
    }
}
