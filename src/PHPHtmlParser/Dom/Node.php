<?php
namespace PHPHtmlParser\Dom;

use PHPHtmlParser\Dom;

/**
 * Dom node object.
 */
class Node {

	/**
	 * Contains the tag name/type
	 *
	 * @var string
	 */
	protected $tag;

	/**
	 * Contains a list of attributes on this tag.
	 *
	 * @var array
	 */
	protected $attr = [];

	/**
	 * An array of all the children.
	 *
	 * @var array
	 */
    protected $children = [];

    /**
     * Contains the parent Node.
     *
     * @var Node
     */
    protected $parent = null;

    /**
     * Not sure what this is for yet.
     */
    protected $tag_start = 0;
   
    /**
     * Contains the dom object that this node belongs to.
     *
     * @var Dom
     */
   	protected $dom = null;

	/**
	 * The unique id of the class. Given by PHP.
	 *
	 * @string
	 */
   	protected $id;

	public function __construct()
	{
		$this->id = spl_object_hash($this);
	}

	/**
	 * Sets the dom object.
	 *
	 * @param Dom $dom
	 * @chainable
	 */
	public function setDom(Dom $dom)
	{
		$this->dom = $dom;

		return $this;
	}

	/**
	 * @todo Remove the need for this to be called.
	 */
    public function __destruct()
    {
        $this->clear();
    }

    public function __toString()
    {
        return $this->outertext();
    }

	/**
	 * Returns the id of this object.
	 */
    public function id()
    {
    	return $this->id;
    }

	/**
     * Returns the parent of node.
     *
     * @return Node
     */
    public function getParent()
    {
    	return $this->parent;
   	}

	/**
	 * Sets the parent node.
	 *
	 * @param Node $parent
	 * @chainable
	 */
   	public function setParent(Node $parent)
   	{
   		// remove from old parent
   		if ( ! is_null($this->parent))
   		{
   			if ($this->parent->id() == $parent->id())
   			{
   				// already the parent
   				return $this;
   			}

   			$this->parent->removeChild($this->id);
   		}

        $this->parent = $parent;

        // assign child to parent
        $this->parent->addChild($this);

        return $this;
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
     * @return Node
     * @throw Exception
     */
    public function getChild($id)
    {
    	if ( ! isset($this->children[$id]))
    	{
    		throw new Exception('Child "'.$id.'" not found in this node.');
    	}

        return $this->children[$id]['node'];
    }

	/**
	 * Adds a child node to this node and returns the id of the child for this
	 * parent.
	 * 
	 * @param Node $child
	 * @return bool
	 */
    public function addChild(Node $child)
    {
    	$key     = null;
    	$newKey  = 0;
    	if ($this->hasChildren())
    	{
    		if (isset($this->children[$child->id()]))
    		{
    			// we already have this child
    			return false;
    		}
    		$sibling = $this->lastChild();
    		$key     = $sibling->id();
    		$this->children[$key]['next'] = $child->id();
    	}

		// add the child
    	$this->children[$child->id()] = [
    		'node' => $child,
    		'next' => null,
    		'prev' => $key,
    	];

    	// tell child I am the new parent
    	$child->setParent($this);

    	return true;
    }

	/**
	 * Removes the child by id.
	 *
	 * @param int $id
	 * @chainable
	 */
	public function removeChild($id)
	{
		if ( ! isset($this->children[$id]))
			return $this;

		// handle moving next and previous assignments.
		$next = $this->children[$id]['next'];
		$prev = $this->children[$id]['prev'];
		if ( ! is_null($next))
		{
			$this->children[$next]['prev'] = $prev;
		}
		if ( ! is_null($prev))
		{
			$this->children[$prev]['next'] = $next;
		}
		
		// remove the child
		unset($this->children[$id]);

		return $this;
	}

	/**
	 * Attempts to get the next child.
	 *
	 * @param int $id
	 * @return Node
	 * @uses $this->getChild()
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
	 * @return Node
	 * @uses $this->getChild()
	 */
	public function previousChild($id)
	{
		$child = $this->getchild($id);
		$next  = $this->children[$child->id()]['prev'];
		return $this->getChild($next);
	}

    /**
     * Shortcut to return the first child.
     *
     * @return Node
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
     * @return Node
     */
    public function lastChild()
    {
    	end($this->children);
    	$key = key($this->children);
    	return $this->getChild($key);
    }

    /**
     * Attempts to get the next sibling.
     *
     * @return Node
     * @throws Exception
     */
    public function nextSibling()
    {
    	if (is_null($this->parent))
    	{
    		throw new Exception('Parent is not set for this node.');
    	}

    	return $this->parent->nextChild($this->id);
    }

	/**
	 * Attempts to get the previous sibling
	 *
	 * @return Node
	 * @throw Exception
	 */
    public function previousSibling()
    {
    	if (is_null($this->parent))
    	{
    		throw new Exception('Parent is not set for this node.');
    	}

    	return $this->parent->previousChild($this->id);
    }

	/**

    // function to locate a specific ancestor tag in the path to the root.
    function find_ancestor_tag($tag)
    {
        global $debugObject;
        if (is_object($debugObject)) { $debugObject->debugLogEntry(1); }

        // Start by including ourselves in the comparison.
        $returnDom = $this;

        while (!is_null($returnDom))
        {
            if (is_object($debugObject)) { $debugObject->debugLog(2, "Current tag is: " . $returnDom->tag); }

            if ($returnDom->tag == $tag)
            {
                break;
            }
            $returnDom = $returnDom->parent;
        }
        return $returnDom;
    }

    // get dom node's inner html
    function innertext()
    {
        if (isset($this->_[HDOM_INFO_INNER])) return $this->_[HDOM_INFO_INNER];
        if (isset($this->_[HDOM_INFO_TEXT])) return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);

        $ret = '';
        foreach ($this->nodes as $n)
            $ret .= $n->outertext();
        return $ret;
    }

    // get dom node's outer text (with tag)
    function outertext()
    {
        global $debugObject;
        if (is_object($debugObject))
        {
            $text = '';
            if ($this->tag == 'text')
            {
                if (!empty($this->text))
                {
                    $text = " with text: " . $this->text;
                }
            }
            $debugObject->debugLog(1, 'Innertext of tag: ' . $this->tag . $text);
        }

        if ($this->tag==='root') return $this->innertext();

        // trigger callback
        if ($this->dom && $this->dom->callback!==null)
        {
            call_user_func_array($this->dom->callback, array($this));
        }

        if (isset($this->_[HDOM_INFO_OUTER])) return $this->_[HDOM_INFO_OUTER];
        if (isset($this->_[HDOM_INFO_TEXT])) return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);

        // render begin tag
        if ($this->dom && $this->dom->nodes[$this->_[HDOM_INFO_BEGIN]])
        {
            $ret = $this->dom->nodes[$this->_[HDOM_INFO_BEGIN]]->makeup();
        } else {
            $ret = "";
        }

        // render inner text
        if (isset($this->_[HDOM_INFO_INNER]))
        {
            // If it's a br tag...  don't return the HDOM_INNER_INFO that we may or may not have added.
            if ($this->tag != "br")
            {
                $ret .= $this->_[HDOM_INFO_INNER];
            }
        } else {
            if ($this->nodes)
            {
                foreach ($this->nodes as $n)
                {
                    $ret .= $this->convert_text($n->outertext());
                }
            }
        }

        // render end tag
        if (isset($this->_[HDOM_INFO_END]) && $this->_[HDOM_INFO_END]!=0)
            $ret .= '</'.$this->tag.'>';
        return $ret;
    }

    // get dom node's plain text
    function text()
    {
        if (isset($this->_[HDOM_INFO_INNER])) return $this->_[HDOM_INFO_INNER];
        switch ($this->nodetype)
        {
            case HDOM_TYPE_TEXT: return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);
            case HDOM_TYPE_COMMENT: return '';
            case HDOM_TYPE_UNKNOWN: return '';
        }
        if (strcasecmp($this->tag, 'script')===0) return '';
        if (strcasecmp($this->tag, 'style')===0) return '';

        $ret = '';
        // In rare cases, (always node type 1 or HDOM_TYPE_ELEMENT - observed for some span tags, and some p tags) $this->nodes is set to NULL.
        // NOTE: This indicates that there is a problem where it's set to NULL without a clear happening.
        // WHY is this happening?
        if (!is_null($this->nodes))
        {
            foreach ($this->nodes as $n)
            {
                $ret .= $this->convert_text($n->text());
            }

            // If this node is a span... add a space at the end of it so multiple spans don't run into each other.  This is plaintext after all.
            if ($this->tag == "span")
            {
                $ret .= $this->dom->default_span_text;
            }


        }
        return $ret;
    }

    function xmltext()
    {
        $ret = $this->innertext();
        $ret = str_ireplace('<![CDATA[', '', $ret);
        $ret = str_replace(']]>', '', $ret);
        return $ret;
    }

    // build node's text with tag
    function makeup()
    {
        // text, comment, unknown
        if (isset($this->_[HDOM_INFO_TEXT])) return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);

        $ret = '<'.$this->tag;
        $i = -1;

        foreach ($this->attr as $key=>$val)
        {
            ++$i;

            // skip removed attribute
            if ($val===null || $val===false)
                continue;

            $ret .= $this->_[HDOM_INFO_SPACE][$i][0];
            //no value attr: nowrap, checked selected...
            if ($val===true)
                $ret .= $key;
            else {
                switch ($this->_[HDOM_INFO_QUOTE][$i])
                {
                    case HDOM_QUOTE_DOUBLE: $quote = '"'; break;
                    case HDOM_QUOTE_SINGLE: $quote = '\''; break;
                    default: $quote = '';
                }
                $ret .= $key.$this->_[HDOM_INFO_SPACE][$i][1].'='.$this->_[HDOM_INFO_SPACE][$i][2].$quote.$val.$quote;
            }
        }
        $ret = $this->dom->restore_noise($ret);
        return $ret . $this->_[HDOM_INFO_ENDSPACE] . '>';
    }

    // find elements by css selector
    //PaperG - added ability for find to lowercase the value of the selector.
    function find($selector, $idx=null, $lowercase=false)
    {
        $selectors = $this->parse_selector($selector);
        if (($count=count($selectors))===0) return array();
        $found_keys = array();

        // find each selector
        for ($c=0; $c<$count; ++$c)
        {
            // The change on the below line was documented on the sourceforge code tracker id 2788009
            // used to be: if (($levle=count($selectors[0]))===0) return array();
            if (($levle=count($selectors[$c]))===0) return array();
            if (!isset($this->_[HDOM_INFO_BEGIN])) return array();

            $head = array($this->_[HDOM_INFO_BEGIN]=>1);

            // handle descendant selectors, no recursive!
            for ($l=0; $l<$levle; ++$l)
            {
                $ret = array();
                foreach ($head as $k=>$v)
                {
                    $n = ($k===-1) ? $this->dom->root : $this->dom->nodes[$k];
                    //PaperG - Pass this optional parameter on to the seek function.
                    $n->seek($selectors[$c][$l], $ret, $lowercase);
                }
                $head = $ret;
            }

            foreach ($head as $k=>$v)
            {
                if (!isset($found_keys[$k]))
                    $found_keys[$k] = 1;
            }
        }

        // sort keys
        ksort($found_keys);

        $found = array();
        foreach ($found_keys as $k=>$v)
            $found[] = $this->dom->nodes[$k];

        // return nth-element or array
        if (is_null($idx)) return $found;
        else if ($idx<0) $idx = count($found) + $idx;
        return (isset($found[$idx])) ? $found[$idx] : null;
    }

    // seek for given conditions
    // PaperG - added parameter to allow for case insensitive testing of the value of a selector.
    protected function seek($selector, &$ret, $lowercase=false)
    {
        global $debugObject;
        if (is_object($debugObject)) { $debugObject->debugLogEntry(1); }

        list($tag, $key, $val, $exp, $no_key) = $selector;

        // xpath index
        if ($tag && $key && is_numeric($key))
        {
            $count = 0;
            foreach ($this->children as $c)
            {
                if ($tag==='*' || $tag===$c->tag) {
                    if (++$count==$key) {
                        $ret[$c->_[HDOM_INFO_BEGIN]] = 1;
                        return;
                    }
                }
            }
            return;
        }

        $end = (!empty($this->_[HDOM_INFO_END])) ? $this->_[HDOM_INFO_END] : 0;
        if ($end==0) {
            $parent = $this->parent;
            while (!isset($parent->_[HDOM_INFO_END]) && $parent!==null) {
                $end -= 1;
                $parent = $parent->parent;
            }
            $end += $parent->_[HDOM_INFO_END];
        }

        for ($i=$this->_[HDOM_INFO_BEGIN]+1; $i<$end; ++$i) {
            $node = $this->dom->nodes[$i];

            $pass = true;

            if ($tag==='*' && !$key) {
                if (in_array($node, $this->children, true))
                    $ret[$i] = 1;
                continue;
            }

            // compare tag
            if ($tag && $tag!=$node->tag && $tag!=='*') {$pass=false;}
            // compare key
            if ($pass && $key) {
                if ($no_key) {
                    if (isset($node->attr[$key])) $pass=false;
                } else {
                    if (($key != "plaintext") && !isset($node->attr[$key])) $pass=false;
                }
            }
            // compare value
            if ($pass && $key && $val  && $val!=='*') {
                // If they have told us that this is a "plaintext" search then we want the plaintext of the node - right?
                if ($key == "plaintext") {
                    // $node->plaintext actually returns $node->text();
                    $nodeKeyValue = $node->text();
                } else {
                    // this is a normal search, we want the value of that attribute of the tag.
                    $nodeKeyValue = $node->attr[$key];
                }
                if (is_object($debugObject)) {$debugObject->debugLog(2, "testing node: " . $node->tag . " for attribute: " . $key . $exp . $val . " where nodes value is: " . $nodeKeyValue);}

                //PaperG - If lowercase is set, do a case insensitive test of the value of the selector.
                if ($lowercase) {
                    $check = $this->match($exp, strtolower($val), strtolower($nodeKeyValue));
                } else {
                    $check = $this->match($exp, $val, $nodeKeyValue);
                }
                if (is_object($debugObject)) {$debugObject->debugLog(2, "after match: " . ($check ? "true" : "false"));}

                // handle multiple class
                if (!$check && strcasecmp($key, 'class')===0) {
                    foreach (explode(' ',$node->attr[$key]) as $k) {
                        // Without this, there were cases where leading, trailing, or double spaces lead to our comparing blanks - bad form.
                        if (!empty($k)) {
                            if ($lowercase) {
                                $check = $this->match($exp, strtolower($val), strtolower($k));
                            } else {
                                $check = $this->match($exp, $val, $k);
                            }
                            if ($check) break;
                        }
                    }
                }
                if (!$check) $pass = false;
            }
            if ($pass) $ret[$i] = 1;
            unset($node);
        }
        // It's passed by reference so this is actually what this function returns.
        if (is_object($debugObject)) {$debugObject->debugLog(1, "EXIT - ret: ", $ret);}
    }

    protected function match($exp, $pattern, $value) {
        global $debugObject;
        if (is_object($debugObject)) {$debugObject->debugLogEntry(1);}

        switch ($exp) {
            case '=':
                return ($value===$pattern);
            case '!=':
                return ($value!==$pattern);
            case '^=':
                return preg_match("/^".preg_quote($pattern,'/')."/", $value);
            case '$=':
                return preg_match("/".preg_quote($pattern,'/')."$/", $value);
            case '*=':
                if ($pattern[0]=='/') {
                    return preg_match($pattern, $value);
                }
                return preg_match("/".$pattern."/i", $value);
        }
        return false;
    }

    protected function parse_selector($selector_string) {
        global $debugObject;
        if (is_object($debugObject)) {$debugObject->debugLogEntry(1);}

        // pattern of CSS selectors, modified from mootools
        // Paperg: Add the colon to the attrbute, so that it properly finds <tag attr:ibute="something" > like google does.
        // Note: if you try to look at this attribute, yo MUST use getAttribute since $dom->x:y will fail the php syntax check.
		// Notice the \[ starting the attbute?  and the @? following?  This implies that an attribute can begin with an @ sign that is not captured.
		// This implies that an html attribute specifier may start with an @ sign that is NOT captured by the expression.
		// farther study is required to determine of this should be documented or removed.
		//        $pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
        $pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
        preg_match_all($pattern, trim($selector_string).' ', $matches, PREG_SET_ORDER);
        if (is_object($debugObject)) {$debugObject->debugLog(2, "Matches Array: ", $matches);}

        $selectors = array();
        $result = array();
        //print_r($matches);

        foreach ($matches as $m) {
            $m[0] = trim($m[0]);
            if ($m[0]==='' || $m[0]==='/' || $m[0]==='//') continue;
            // for browser generated xpath
            if ($m[1]==='tbody') continue;

            list($tag, $key, $val, $exp, $no_key) = array($m[1], null, null, '=', false);
            if (!empty($m[2])) {$key='id'; $val=$m[2];}
            if (!empty($m[3])) {$key='class'; $val=$m[3];}
            if (!empty($m[4])) {$key=$m[4];}
            if (!empty($m[5])) {$exp=$m[5];}
            if (!empty($m[6])) {$val=$m[6];}

            // convert to lowercase
            if ($this->dom->lowercase) {$tag=strtolower($tag); $key=strtolower($key);}
            //elements that do NOT have the specified attribute
            if (isset($key[0]) && $key[0]==='!') {$key=substr($key, 1); $no_key=true;}

            $result[] = array($tag, $key, $val, $exp, $no_key);
            if (trim($m[7])===',') {
                $selectors[] = $result;
                $result = array();
            }
        }
        if (count($result)>0)
            $selectors[] = $result;
        return $selectors;
    }

    function __get($name) {
        if (isset($this->attr[$name]))
        {
            return $this->convert_text($this->attr[$name]);
        }
        switch ($name) {
            case 'outertext': return $this->outertext();
            case 'innertext': return $this->innertext();
            case 'plaintext': return $this->text();
            case 'xmltext': return $this->xmltext();
            default: return array_key_exists($name, $this->attr);
        }
    }

    function __set($name, $value) {
        switch ($name) {
            case 'outertext': return $this->_[HDOM_INFO_OUTER] = $value;
            case 'innertext':
                if (isset($this->_[HDOM_INFO_TEXT])) return $this->_[HDOM_INFO_TEXT] = $value;
                return $this->_[HDOM_INFO_INNER] = $value;
        }
        if (!isset($this->attr[$name])) {
            $this->_[HDOM_INFO_SPACE][] = array(' ', '', '');
            $this->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_DOUBLE;
        }
        $this->attr[$name] = $value;
    }

    function __isset($name) {
        switch ($name) {
            case 'outertext': return true;
            case 'innertext': return true;
            case 'plaintext': return true;
        }
        //no value attr: nowrap, checked selected...
        return (array_key_exists($name, $this->attr)) ? true : isset($this->attr[$name]);
    }

    function __unset($name) {
        if (isset($this->attr[$name]))
            unset($this->attr[$name]);
    }

    // PaperG - Function to convert the text from one character set to another if the two sets are not the same.
    function convert_text($text)
    {
        global $debugObject;
        if (is_object($debugObject)) {$debugObject->debugLogEntry(1);}

        $converted_text = $text;

        $sourceCharset = "";
        $targetCharset = "";

        if ($this->dom)
        {
            $sourceCharset = strtoupper($this->dom->_charset);
            $targetCharset = strtoupper($this->dom->_target_charset);
        }
        if (is_object($debugObject)) {$debugObject->debugLog(3, "source charset: " . $sourceCharset . " target charaset: " . $targetCharset);}

        if (!empty($sourceCharset) && !empty($targetCharset) && (strcasecmp($sourceCharset, $targetCharset) != 0))
        {
            // Check if the reported encoding could have been incorrect and the text is actually already UTF-8
            if ((strcasecmp($targetCharset, 'UTF-8') == 0) && ($this->is_utf8($text)))
            {
                $converted_text = $text;
            }
            else
            {
                $converted_text = iconv($sourceCharset, $targetCharset, $text);
            }
        }

        // Lets make sure that we don't have that silly BOM issue with any of the utf-8 text we output.
        if ($targetCharset == 'UTF-8')
        {
            if (substr($converted_text, 0, 3) == "\xef\xbb\xbf")
            {
                $converted_text = substr($converted_text, 3);
            }
            if (substr($converted_text, -3) == "\xef\xbb\xbf")
            {
                $converted_text = substr($converted_text, 0, -3);
            }
        }

        return $converted_text;
    }

    /**
    * Returns true if $string is valid UTF-8 and false otherwise.
    *
    * @param mixed $str String to be tested
    * @return boolean
    *
    static function is_utf8($str)
    {
        $c=0; $b=0;
        $bits=0;
        $len=strlen($str);
        for($i=0; $i<$len; $i++)
        {
            $c=ord($str[$i]);
            if($c > 128)
            {
                if(($c >= 254)) return false;
                elseif($c >= 252) $bits=6;
                elseif($c >= 248) $bits=5;
                elseif($c >= 240) $bits=4;
                elseif($c >= 224) $bits=3;
                elseif($c >= 192) $bits=2;
                else return false;
                if(($i+$bits) > $len) return false;
                while($bits > 1)
                {
                    $i++;
                    $b=ord($str[$i]);
                    if($b < 128 || $b > 191) return false;
                    $bits--;
                }
            }
        }
        return true;
    }
    /*
    function is_utf8($string)
    {
        //this is buggy
        return (utf8_encode(utf8_decode($string)) == $string);
    }
    */

    /**
     * Function to try a few tricks to determine the displayed size of an img on the page.
     * NOTE: This will ONLY work on an IMG tag. Returns FALSE on all other tag types.
     *
     * @author John Schlick
     * @version April 19 2012
     * @return array an array containing the 'height' and 'width' of the image on the page or -1 if we can't figure it out.
     *
    function get_display_size()
    {
        global $debugObject;

        $width = -1;
        $height = -1;

        if ($this->tag !== 'img')
        {
            return false;
        }

        // See if there is aheight or width attribute in the tag itself.
        if (isset($this->attr['width']))
        {
            $width = $this->attr['width'];
        }

        if (isset($this->attr['height']))
        {
            $height = $this->attr['height'];
        }

        // Now look for an inline style.
        if (isset($this->attr['style']))
        {
            // Thanks to user gnarf from stackoverflow for this regular expression.
            $attributes = array();
            preg_match_all("/([\w-]+)\s*:\s*([^;]+)\s*;?/", $this->attr['style'], $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
              $attributes[$match[1]] = $match[2];
            }

            // If there is a width in the style attributes:
            if (isset($attributes['width']) && $width == -1)
            {
                // check that the last two characters are px (pixels)
                if (strtolower(substr($attributes['width'], -2)) == 'px')
                {
                    $proposed_width = substr($attributes['width'], 0, -2);
                    // Now make sure that it's an integer and not something stupid.
                    if (filter_var($proposed_width, FILTER_VALIDATE_INT))
                    {
                        $width = $proposed_width;
                    }
                }
            }

            // If there is a width in the style attributes:
            if (isset($attributes['height']) && $height == -1)
            {
                // check that the last two characters are px (pixels)
                if (strtolower(substr($attributes['height'], -2)) == 'px')
                {
                    $proposed_height = substr($attributes['height'], 0, -2);
                    // Now make sure that it's an integer and not something stupid.
                    if (filter_var($proposed_height, FILTER_VALIDATE_INT))
                    {
                        $height = $proposed_height;
                    }
                }
            }

        }

        // Future enhancement:
        // Look in the tag to see if there is a class or id specified that has a height or width attribute to it.

        // Far future enhancement
        // Look at all the parent tags of this image to see if they specify a class or id that has an img selector that specifies a height or width
        // Note that in this case, the class or id will have the img subselector for it to apply to the image.

        // ridiculously far future development
        // If the class or id is specified in a SEPARATE css file thats not on the page, go get it and do what we were just doing for the ones on the page.

        $result = array('height' => $height,
                        'width' => $width);
        return $result;
    }

    /**
     * clean up memory due to php5 circular references memory leak...
     *
     * @todo Remove the need for this. (Remove circular references)
     */
    protected function clear()
    {
        $this->dom = null;
        $this->nodes = null;
        $this->parent = null;
        $this->children = null;
    }

}
