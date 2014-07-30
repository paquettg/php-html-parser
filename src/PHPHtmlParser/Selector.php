<?php
namespace PHPHtmlParser;

use PHPHtmlParser\Dom\Collection;
use PHPHtmlParser\Exceptions\ChildNotFoundException;

class Selector {

	/** 
	 * Pattern of CSS selectors, modified from mootools
	 *
	 * @var string
	 */
	protected $pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";

	protected $selectors = array();

	/**
	 * Constructs with the selector string
	 *
	 * @param string $selector
	 */
	public function __construct($selector)
	{
		$this->parseSelectorString($selector);
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
	 * Attempts to find the selectors starting from the given
	 * node object.
	 *
	 * @param Node $noda
	 * @return array
	 */
	public function find($node)
	{
		$results = new Collection;
		foreach ($this->selectors as $selector)
		{
			$nodes = array($node);
			if (count($selector) == 0)
				continue;

			foreach ($selector as $rule)
			{
				$nodes = $this->seek($nodes, $rule);
			}

			// this is the final set of nodes
			foreach ($nodes as $result)
			{
				$results[] = $result;
			}
		}

		return $results;
	}

	/**
	 * Parses the selector string
	 *
	 * @param string $selector
	 */
	protected function parseSelectorString($selector)
	{
		$matches = array();
		preg_match_all($this->pattern, trim($selector).' ', $matches, PREG_SET_ORDER);
		
		// skip tbody
		$result = array();
		foreach ($matches as $match)
		{
			// default values
			$tag	  = strtolower(trim($match[1]));
			$operator = '=';
			$key	  = null;
			$value	  = null;
			$noKey	  = false;

			// check for id selector
			if ( ! empty($match[2]))
			{
				$key   = 'id';
				$value = $match[2];
			}

			// check for class selector
			if ( ! empty($match[3]))
			{
				$key = 'class';
				$value = $match[3];
			}

			// and final attribute selecter
			if ( ! empty($match[4]))
			{
				$key = strtolower($match[4]);
			}
			if ( ! empty($match[5]))
			{
				$operator = $match[5];
			}
			if ( ! empty($match[6]))
			{
				$value = $match[6];
			}
			
			// check for elements that do not have a specified attribute
			if ( isset($key[0]) AND $key[0] == '!')
			{
				$key   = substr($key, 1);
				$noKey = true;
			}

			$result[] = array( 
				'tag'	   => $tag,
				'key'	   => $key,
				'value'    => $value,
				'operator' => $operator,
				'noKey'    => $noKey,
            );
			if (trim($match[7]) == ',')
			{
				$this->selectors[] = $result;
				$result			   = array();
			}
		}
		
		// save last results
		if (count($result) > 0)
		{
			$this->selectors[] = $result;
		}
	}

	/**
	 * Attempts to find all children that match the rule 
	 * given.
	 *
	 * @param array $nodes
	 * @param array $rule
	 * @recursive
	 */
	protected function seek(array $nodes, array $rule)
	{
		// XPath index
		if ( ! empty($rule['tag']) AND ! empty($rule['key']) AND
			is_numeric($rule['key']))
		{
			$count = 0;
			foreach ($nodes as $node)
			{
				if ($rule['tag'] == '*' OR $rule['tag'] == $node->getTag()->name())
				{
					++$count;
					if ($count == $rule['key'])
					{
						// found the node we wanted
						return array($node);
					}
				}
			}
			return array();
		}

		$return = array();
		foreach ($nodes as $node)
		{
			// check if we are a leaf
			if ( ! $node->hasChildren())
				continue;

			$children = array();
			$child	  = $node->firstChild();
			while ( ! is_null($child))
			{
				// wild card, grab all
				if ($rule['tag'] == '*' AND is_null($rule['key']))
				{
					$return[] = $child;
					try
					{
						$child = $node->nextChild($child->id());
					}
					catch (ChildNotFoundException $e)
					{
						// no more children
						$child = null;
					}
					continue;
				}

				$pass = true;
				// check tag
				if ( ! empty($rule['tag']) AND $rule['tag'] != $child->getTag()->name() AND
					$rule['tag'] != '*')
				{
					// child faild tag check
					$pass = false;
				}
				
				// check key
				if ($pass AND ! is_null($rule['key']))
				{
					if ($rule['noKey'])
					{
						if ( ! is_null($child->getAttribute($rule['key'])))
						{
							$pass = false;
						}
					}
					else
					{
						if ($rule['key'] != 'plaintext' and 
							is_null($child->getAttribute($rule['key'])))
						{
							$pass = false;
						}
					}
				}

				// compare values
				if ($pass and ! is_null($rule['key']) and
					 ! is_null($rule['value']) and $rule['value'] != '*')
				{
					if ($rule['key'] == 'plaintext')
					{
						// plaintext search
						$nodeValue = $child->text();
					}
					else
					{
						// normal search
						$nodeValue = $child->getAttribute($rule['key']);
					}

					$check = $this->match($rule['operator'], $rule['value'], $nodeValue);

					// handle multiple classes
					if ( ! $check and $rule['key'] == 'class')
					{
						$childClasses = explode(' ', $child->getAttribute('class'));
						foreach ($childClasses as $class)
						{
							if ( ! empty($class))
							{
								$check = $this->match($rule['operator'], $rule['value'], $class);
							}
							if ($check) 
								break;
						}
					}

					if ( ! $check)
					{
						$pass = false;
					}
				}

				if ($pass)
				{
					// it passed all checks
					$return[] = $child;
				}
				else
				{
					// this child failed to be matched
					if ($child->hasChildren())
					{
						// we still want to check its children
						$children[] = $child;
					}
				}

				try
				{
					// get next child
					$child = $node->nextChild($child->id());
				}
				catch (ChildNotFoundException $e)
				{
					// no more children
					$child = null;
				}
			}

			if (count($children) > 0)
			{
				// we have children that failed but are not leaves.
				$matches = $this->seek($children, $rule);
				foreach ($matches as $match)
				{
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
	protected function match($operator, $pattern, $value)
	{
		$value	 = strtolower($value);
		$pattern = strtolower($pattern);
		switch ($operator) 
		{
			case '=':
				return $value === $pattern;
			case '!=':
				return $value !== $pattern;
			case '^=':
				return preg_match('/^'.preg_quote($pattern, '/').'/', $value);
			case '$=':
				return preg_match('/'.preg_quote($pattern,'/').'$/', $value);
			case '*=':
				if ($pattern[0]=='/') 
				{
					return preg_match($pattern, $value);
				}
				return preg_match("/".$pattern."/i", $value);
		}
		return false;
	}
}
