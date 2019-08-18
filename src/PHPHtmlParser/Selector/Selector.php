<?php declare(strict_types=1);
namespace PHPHtmlParser\Selector;

use PHPHtmlParser\Dom\AbstractNode;
use PHPHtmlParser\Dom\Collection;
use PHPHtmlParser\Dom\InnerNode;
use PHPHtmlParser\Dom\LeafNode;
use PHPHtmlParser\Exceptions\ChildNotFoundException;

/**
 * Class Selector
 *
 * @package PHPHtmlParser
 */
class Selector
{

    /**
     * @var array
     */
    protected $selectors = [];

    /**
     * @var bool
     */
    private $depthFirst = false;

    /**
     * Constructs with the selector string
     * @param string          $selector
     * @param ParserInterface $parser
     */
    public function __construct(string $selector, ParserInterface $parser)
    {
        $this->selectors = $parser->parseSelectorString($selector);
    }

    /**
     * Returns the selectors that where found in __construct
     *
     * @return array
     */
    public function getSelectors()
    {
        return $this->selectors;
    }

    /**
     * @param bool $status
     * @return void
     */
    public function setDepthFirstFind(bool $status): void
    {
        $this->depthFirst = $status;
    }

    /**
     * Attempts to find the selectors starting from the given
     * node object.
     * @param AbstractNode $node
     * @return Collection
     * @throws ChildNotFoundException
     */
    public function find(AbstractNode $node): Collection
    {
        $results = new Collection;
        foreach ($this->selectors as $selector) {
            $nodes = [$node];
            if (count($selector) == 0) {
                continue;
            }

            $options = [];
            foreach ($selector as $rule) {
                if ($rule['alterNext']) {
                    $options[] = $this->alterNext($rule);
                    continue;
                }
                $nodes = $this->seek($nodes, $rule, $options);
                // clear the options
                $options = [];
            }

            // this is the final set of nodes
            foreach ($nodes as $result) {
                $results[] = $result;
            }
        }

        return $results;
    }


    /**
     * Attempts to find all children that match the rule
     * given.
     *
     * @param array $nodes
     * @param array $rule
     * @param array $options
     *
     * @return array
     * @throws ChildNotFoundException
     */
    protected function seek(array $nodes, array $rule, array $options): array
    {
        // XPath index
        if (array_key_exists('tag', $rule) &&
            array_key_exists('key', $rule) &&
            is_numeric($rule['key'])
        ) {
            $count = 0;
            /** @var AbstractNode $node */
            foreach ($nodes as $node) {
                if ($rule['tag'] == '*' ||
                    $rule['tag'] == $node->getTag()->name()
                ) {
                    ++$count;
                    if ($count == $rule['key']) {
                        // found the node we wanted
                        return [$node];
                    }
                }
            }

            return [];
        }

        $options = $this->flattenOptions($options);

        $return = [];
        /** @var InnerNode $node */
        foreach ($nodes as $node) {
            // check if we are a leaf
            if ($node instanceof LeafNode ||
                ! $node->hasChildren()
            ) {
                continue;
            }

            $children = [];
            $child    = $node->firstChild();
            while ( ! is_null($child)) {
                // wild card, grab all
                if ($rule['tag'] == '*' && is_null($rule['key'])) {
                    $return[] = $child;
                    $child = $this->getNextChild($node, $child);
                    continue;
                }

                $pass = $this->checkTag($rule, $child);
                if ($pass && ! is_null($rule['key'])) {
                    $pass = $this->checkKey($rule, $child);
                }
                if ($pass && ! is_null($rule['key']) &&
                    ! is_null($rule['value']) && $rule['value'] != '*'
                ) {
                    $pass = $this->checkComparison($rule, $child);
                }

                if ($pass) {
                    // it passed all checks
                    $return[] = $child;
                } else {
                    // this child failed to be matched
                    if ($child instanceof InnerNode &&
                        $child->hasChildren()
                    ) {
                        if ($this->depthFirst) {
                            if ( ! isset($options['checkGrandChildren']) ||
                                $options['checkGrandChildren']) {
                                // we have a child that failed but are not leaves.
                                $matches = $this->seek([$child], $rule, $options);
                                foreach ($matches as $match) {
                                    $return[] = $match;
                                }
                            }
                        } else {
                            // we still want to check its children
                            $children[] = $child;
                        }
                    }
                }

                $child = $this->getNextChild($node, $child);
            }

            if (( ! isset($options['checkGrandChildren']) ||
                    $options['checkGrandChildren'])
                && count($children) > 0
            ) {
                // we have children that failed but are not leaves.
                $matches = $this->seek($children, $rule, $options);
                foreach ($matches as $match) {
                    $return[] = $match;
                }
            }
        }

        return $return;
    }

    /**
     * Attempts to match the given arguments with the given operator.
     *
     * @param string $operator
     * @param string $pattern
     * @param string $value
     * @return bool
     */
    protected function match(string $operator, string $pattern, string $value): bool
    {
        $value   = strtolower($value);
        $pattern = strtolower($pattern);
        switch ($operator) {
            case '=':
                return $value === $pattern;
            case '!=':
                return $value !== $pattern;
            case '^=':
                return preg_match('/^'.preg_quote($pattern, '/').'/', $value) == 1;
            case '$=':
                return preg_match('/'.preg_quote($pattern, '/').'$/', $value) == 1;
            case '*=':
                if ($pattern[0] == '/') {
                    return preg_match($pattern, $value) == 1;
                }

                return preg_match("/".$pattern."/i", $value) == 1;
        }

        return false;
    }

    /**
     * Attempts to figure out what the alteration will be for
     * the next element.
     *
     * @param array $rule
     * @return array
     */
    protected function alterNext(array $rule): array
    {
        $options = [];
        if ($rule['tag'] == '>') {
            $options['checkGrandChildren'] = false;
        }

        return $options;
    }

    /**
     * Flattens the option array.
     *
     * @param array $optionsArray
     * @return array
     */
    protected function flattenOptions(array $optionsArray)
    {
        $options = [];
        foreach ($optionsArray as $optionArray) {
            foreach ($optionArray as $key => $option) {
                $options[$key] = $option;
            }
        }

        return $options;
    }

    /**
     * Returns the next child or null if no more children.
     *
     * @param AbstractNode $node
     * @param AbstractNode $currentChild
     * @return AbstractNode|null
     */
    protected function getNextChild(AbstractNode $node, AbstractNode $currentChild)
    {
        try {
            $child = null;
            if ($node instanceof InnerNode) {
                // get next child
                $child = $node->nextChild($currentChild->id());
            }
        } catch (ChildNotFoundException $e) {
            // no more children
            $child = null;
        }

        return $child;
    }

    /**
     * Checks tag condition from rules against node.
     *
     * @param array $rule
     * @param AbstractNode $node
     * @return bool
     */
    protected function checkTag(array $rule, AbstractNode $node): bool
    {
        if ( ! empty($rule['tag']) && $rule['tag'] != $node->getTag()->name() &&
            $rule['tag'] != '*'
        ) {
            return false;
        }

        return true;
    }

    /**
     * Checks key condition from rules against node.
     *
     * @param array $rule
     * @param AbstractNode $node
     * @return bool
     */
    protected function checkKey(array $rule, AbstractNode $node): bool
    {
        if ($rule['noKey']) {
            if ( ! is_null($node->getAttribute($rule['key']))) {
                return false;
            }
        } else {
            if ($rule['key'] != 'plaintext' && !$node->hasAttribute($rule['key'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks comparison condition from rules against node.
     *
     * @param array $rule
     * @param AbstractNode $node
     * @return bool
     */
    public function checkComparison(array $rule, AbstractNode $node): bool
    {
        if ($rule['key'] == 'plaintext') {
            // plaintext search
            $nodeValue = $node->text();
        } else {
            // normal search
            $nodeValue = $node->getAttribute($rule['key']);
        }

        $check = false;
        if (!is_array($rule['value'])) {
            $check = $this->match($rule['operator'], $rule['value'], $nodeValue);
        }

        // handle multiple classes
        if ( ! $check && $rule['key'] == 'class') {
            $nodeClasses = explode(' ', $node->getAttribute('class'));
            foreach ($rule['value'] as $value) {
                foreach ($nodeClasses as $class) {
                    if ( ! empty($class)) {
                        $check = $this->match($rule['operator'], $value, $class);
                    }
                    if ($check) {
                        break;
                    }
                }
                if (!$check) {
                    break;
                }
            }
        }

        return $check;
    }
}
