<?php declare(strict_types=1);
namespace PHPHtmlParser;

use PHPHtmlParser\Dom\AbstractNode;
use PHPHtmlParser\Dom\Collection;
use PHPHtmlParser\Dom\HtmlNode;
use PHPHtmlParser\Dom\TextNode;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\CurlException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\ParentNotFoundException;
use PHPHtmlParser\Exceptions\StrictException;
use PHPHtmlParser\Exceptions\UnknownChildTypeException;
use PHPHtmlParser\Exceptions\LogicalException;
use stringEncode\Encode;

/**
 * Class Dom
 *
 * @package PHPHtmlParser
 */
class Dom
{

    /**
     * The charset we would like the output to be in.
     *
     * @var string
     */
    protected $defaultCharset = 'UTF-8';

    /**
     * Contains the root node of this dom tree.
     *
     * @var HtmlNode
     */
    public $root;

    /**
     * The raw version of the document string.
     *
     * @var string
     */
    protected $raw;

    /**
     * The document string.
     *
     * @var Content
     */
    protected $content = null;

    /**
     * The original file size of the document.
     *
     * @var int
     */
    protected $rawSize;

    /**
     * The size of the document after it is cleaned.
     *
     * @var int
     */
    protected $size;

    /**
     * A global options array to be used by all load calls.
     *
     * @var array
     */
    protected $globalOptions = [];

    /**
     * A persistent option object to be used for all options in the
     * parsing of the file.
     *
     * @var Options
     */
    protected $options;

    /**
     * A list of tags which will always be self closing
     *
     * @var array
     */
    protected $selfClosing = [
        'area',
        'base',
        'basefont',
        'br',
        'col',
        'embed',
        'hr',
        'img',
        'input',
        'keygen',
        'link',
        'meta',
        'param',
        'source',
        'spacer',
        'track',
        'wbr'
    ];

    /**
     * A list of tags where there should be no /> at the end (html5 style)
     *
     * @var array
     */
    protected $noSlash = [];

    /**
     * Returns the inner html of the root node.
     *
     * @return string
     * @throws ChildNotFoundException
     * @throws UnknownChildTypeException
     */
    public function __toString(): string
    {
        return $this->root->innerHtml();
    }

    /**
     * A simple wrapper around the root node.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->root->$name;
    }

    /**
     * Attempts to load the dom from any resource, string, file, or URL.
     * @param string $str
     * @param array  $options
     * @return Dom
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws StrictException
     */
    public function load(string $str, array $options = []): Dom
    {
        AbstractNode::resetCount();
        // check if it's a file
        if (strpos($str, "\n") === false && is_file($str)) {
            return $this->loadFromFile($str, $options);
        }
        // check if it's a url
        if (preg_match("/^https?:\/\//i", $str)) {
            return $this->loadFromUrl($str, $options);
        }

        return $this->loadStr($str, $options);
    }

    /**
     * Loads the dom from a document file/url
     * @param string $file
     * @param array  $options
     * @return Dom
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws StrictException
     * @throws LogicalException
     */
    public function loadFromFile(string $file, array $options = []): Dom
    {
        $content = file_get_contents($file);
        if ($content === false) {
            throw new LogicalException('file_get_contents failed and returned false when trying to read "'.$file.'".');
        }
        return $this->loadStr($content, $options);
    }

    /**
     * Use a curl interface implementation to attempt to load
     * the content from a url.
     * @param string                            $url
     * @param array                             $options
     * @param CurlInterface|null $curl
     * @return Dom
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws StrictException
     */
    public function loadFromUrl(string $url, array $options = [], CurlInterface $curl = null): Dom
    {
        if (is_null($curl)) {
            // use the default curl interface
            $curl = new Curl;
        }
        $content = $curl->get($url, $options);

        return $this->loadStr($content, $options);
    }

    /**
     * Parsers the html of the given string. Used for load(), loadFromFile(),
     * and loadFromUrl().
     * @param string $str
     * @param array  $option
     * @return Dom
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws StrictException
     */
    public function loadStr(string $str, array $option = []): Dom
    {
        $this->options = new Options;
        $this->options->setOptions($this->globalOptions)
                      ->setOptions($option);

        $this->rawSize = strlen($str);
        $this->raw     = $str;

        $html = $this->clean($str);

        $this->size    = strlen($str);
        $this->content = new Content($html);

        $this->parse();
        $this->detectCharset();

        return $this;
    }

    /**
     * Sets a global options array to be used by all load calls.
     *
     * @param array $options
     * @return Dom
     * @chainable
     */
    public function setOptions(array $options): Dom
    {
        $this->globalOptions = $options;

        return $this;
    }

    /**
     * Find elements by css selector on the root node.
     * @param string   $selector
     * @param int|null $nth
     * @return mixed|Collection|null
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     */
    public function find(string $selector, int $nth = null)
    {
        $this->isLoaded();

        $depthFirstSearch = $this->options->get('depthFirstSearch');
        if (is_bool($depthFirstSearch)) {
            $result = $this->root->find($selector, $nth, $depthFirstSearch);
        } else {
            $result = $this->root->find($selector, $nth);
        }

        return $result;
    }

    /**
     * Find element by Id on the root node
     * @param int $id
     * @return bool|AbstractNode
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     * @throws ParentNotFoundException
     */
    public function findById(int $id)
    {
        $this->isLoaded();

        return $this->root->findById($id);
    }

    /**
     * Adds the tag (or tags in an array) to the list of tags that will always
     * be self closing.
     *
     * @param string|array $tag
     * @return Dom
     * @chainable
     */
    public function addSelfClosingTag($tag): Dom
    {
        if ( ! is_array($tag)) {
            $tag = [$tag];
        }
        foreach ($tag as $value) {
            $this->selfClosing[] = $value;
        }

        return $this;
    }

    /**
     * Removes the tag (or tags in an array) from the list of tags that will
     * always be self closing.
     *
     * @param string|array $tag
     * @return Dom
     * @chainable
     */
    public function removeSelfClosingTag($tag): Dom
    {
        if ( ! is_array($tag)) {
            $tag = [$tag];
        }
        $this->selfClosing = array_diff($this->selfClosing, $tag);

        return $this;
    }

    /**
     * Sets the list of self closing tags to empty.
     *
     * @return Dom
     * @chainable
     */
    public function clearSelfClosingTags(): Dom
    {
        $this->selfClosing = [];

        return $this;
    }


    /**
     * Adds a tag to the list of self closing tags that should not have a trailing slash
     *
     * @param $tag
     * @return Dom
     * @chainable
     */
    public function addNoSlashTag($tag): Dom
    {
        if ( ! is_array($tag)) {
            $tag = [$tag];
        }
        foreach ($tag as $value) {
            $this->noSlash[] = $value;
        }

        return $this;
    }

    /**
     * Removes a tag from the list of no-slash tags.
     *
     * @param $tag
     * @return Dom
     * @chainable
     */
    public function removeNoSlashTag($tag): Dom
    {
        if ( ! is_array($tag)) {
            $tag = [$tag];
        }
        $this->noSlash = array_diff($this->noSlash, $tag);

        return $this;
    }

    /**
     * Empties the list of no-slash tags.
     *
     * @return Dom
     * @chainable
     */
    public function clearNoSlashTags(): Dom
    {
        $this->noSlash = [];

        return $this;
    }

    /**
     * Simple wrapper function that returns the first child.
     * @return AbstractNode
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     */
    public function firstChild(): AbstractNode
    {
        $this->isLoaded();

        return $this->root->firstChild();
    }

    /**
     * Simple wrapper function that returns the last child.
     * @return AbstractNode
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     */
    public function lastChild(): AbstractNode
    {
        $this->isLoaded();

        return $this->root->lastChild();
    }

    /**
     * Simple wrapper function that returns count of child elements
     *
     * @return int
     * @throws NotLoadedException
     */
    public function countChildren(): int
    {
        $this->isLoaded();

        return $this->root->countChildren();
    }

    /**
     * Get array of children
     *
     * @return array
     * @throws NotLoadedException
     */
    public function getChildren(): array
    {
        $this->isLoaded();

        return $this->root->getChildren();
    }

    /**
     * Check if node have children nodes
     *
     * @return bool
     * @throws NotLoadedException
     */
    public function hasChildren(): bool
    {
        $this->isLoaded();

        return $this->root->hasChildren();
    }

    /**
     * Simple wrapper function that returns an element by the
     * id.
     * @param $id
     * @return mixed|Collection|null
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     */
    public function getElementById($id)
    {
        $this->isLoaded();

        return $this->find('#'.$id, 0);
    }

    /**
     * Simple wrapper function that returns all elements by
     * tag name.
     * @param string $name
     * @return mixed|Collection|null
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     */
    public function getElementsByTag(string $name)
    {
        $this->isLoaded();

        return $this->find($name);
    }

    /**
     * Simple wrapper function that returns all elements by
     * class name.
     * @param string $class
     * @return mixed|Collection|null
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     */
    public function getElementsByClass(string $class)
    {
        $this->isLoaded();

        return $this->find('.'.$class);
    }

    /**
     * Checks if the load methods have been called.
     *
     * @throws NotLoadedException
     */
    protected function isLoaded(): void
    {
        if (is_null($this->content)) {
            throw new NotLoadedException('Content is not loaded!');
        }
    }

    /**
     * Cleans the html of any none-html information.
     *
     * @param string $str
     * @return string
     */
    protected function clean(string $str): string
    {
        if ($this->options->get('cleanupInput') != true) {
            // skip entire cleanup step
            return $str;
        }

        $is_gzip = 0 === mb_strpos($str, "\x1f" . "\x8b" . "\x08", 0, "US-ASCII");
        if ($is_gzip) {
            $str = gzdecode($str);
            if ($str === false) {
                throw new LogicalException('gzdecode returned false. Error when trying to decode the string.');
            }
        }

        // remove white space before closing tags
        $str = mb_eregi_replace("'\s+>", "'>", $str);
        if ($str === false) {
            throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to clean single quotes.');
        }
        $str = mb_eregi_replace('"\s+>', '">', $str);
        if ($str === false) {
            throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to clean double quotes.');
        }

        // clean out the \n\r
        $replace = ' ';
        if ($this->options->get('preserveLineBreaks')) {
            $replace = '&#10;';
        }
        $str = str_replace(["\r\n", "\r", "\n"], $replace, $str);
        if ($str === false) {
            throw new LogicalException('str_replace returned false instead of a string. Error when attempting to clean input string.');
        }

        // strip the doctype
        $str = mb_eregi_replace("<!doctype(.*?)>", '', $str);
        if ($str === false) {
            throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to strip the doctype.');
        }

        // strip out comments
        $str = mb_eregi_replace("<!--(.*?)-->", '', $str);
        if ($str === false) {
            throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to strip comments.');
        }

        // strip out cdata
        $str = mb_eregi_replace("<!\[CDATA\[(.*?)\]\]>", '', $str);
        if ($str === false) {
            throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to strip out cdata.');
        }

        // strip out <script> tags
        if ($this->options->get('removeScripts')) {
            $str = mb_eregi_replace("<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>", '', $str);
            if ($str === false) {
                throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to remove scripts 1.');
            }
            $str = mb_eregi_replace("<\s*script\s*>(.*?)<\s*/\s*script\s*>", '', $str);
            if ($str === false) {
                throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to remove scripts 2.');
            }
        }

        // strip out <style> tags
        if ($this->options->get('removeStyles')) {
            $str = mb_eregi_replace("<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>", '', $str);
            if ($str === false) {
                throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to strip out style tags 1.');
            }
            $str = mb_eregi_replace("<\s*style\s*>(.*?)<\s*/\s*style\s*>", '', $str);
            if ($str === false) {
                throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to strip out style tags 2.');
            }
        }

        // strip out server side scripts
        if ($this->options->get('serverSideScripts')) {
            $str = mb_eregi_replace("(<\?)(.*?)(\?>)", '', $str);
            if ($str === false) {
                throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to strip out service side scripts.');
            }
        }

        // strip smarty scripts
        if ($this->options->get('removeSmartyScripts')) {
            $str = mb_eregi_replace("(\{\w)(.*?)(\})", '', $str);
            if ($str === false) {
                throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to remove smarty scripts.');
            }
        }

        return $str;
    }

    /**
     * Attempts to parse the html in content.
     *
     * @return void
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws StrictException
     */
    protected function parse(): void
    {
        // add the root node
        $this->root = new HtmlNode('root');
        $this->root->setHtmlSpecialCharsDecode($this->options->htmlSpecialCharsDecode);
        $activeNode = $this->root;
        while ( ! is_null($activeNode)) {
            $str = $this->content->copyUntil('<');
            if ($str == '') {
                $info = $this->parseTag();
                if ( ! $info['status']) {
                    // we are done here
                    $activeNode = null;
                    continue;
                }

                // check if it was a closing tag
                if ($info['closing']) {
                    $foundOpeningTag  = true;
                    $originalNode     = $activeNode;
                    while ($activeNode->getTag()->name() != $info['tag']) {
                        $activeNode = $activeNode->getParent();
                        if (is_null($activeNode)) {
                            // we could not find opening tag
                            $activeNode = $originalNode;
                            $foundOpeningTag = false;
                            break;
                        }
                    }
                    if ($foundOpeningTag) {
                        $activeNode = $activeNode->getParent();
                    }
                    continue;
                }

                if ( ! isset($info['node'])) {
                    continue;
                }

                /** @var AbstractNode $node */
                $node = $info['node'];
                $activeNode->addChild($node);

                // check if node is self closing
                if ( ! $node->getTag()->isSelfClosing()) {
                    $activeNode = $node;
                }
            } else if ($this->options->whitespaceTextNode ||
                trim($str) != ''
            ) {
                // we found text we care about
                $textNode = new TextNode($str, $this->options->removeDoubleSpace);
                $textNode->setHtmlSpecialCharsDecode($this->options->htmlSpecialCharsDecode);
                $activeNode->addChild($textNode);
            }
        }
    }

    /**
     * Attempt to parse a tag out of the content.
     *
     * @return array
     * @throws StrictException
     */
    protected function parseTag(): array
    {
        $return = [
            'status'  => false,
            'closing' => false,
            'node'    => null,
        ];
        if ($this->content->char() != '<') {
            // we are not at the beginning of a tag
            return $return;
        }

        // check if this is a closing tag
        if ($this->content->fastForward(1)->char() == '/') {
            // end tag
            $tag = $this->content->fastForward(1)
                                 ->copyByToken('slash', true);
            // move to end of tag
            $this->content->copyUntil('>');
            $this->content->fastForward(1);

            // check if this closing tag counts
            $tag = strtolower($tag);
            if (in_array($tag, $this->selfClosing, true)) {
                $return['status'] = true;

                return $return;
            } else {
                $return['status']  = true;
                $return['closing'] = true;
                $return['tag']     = strtolower($tag);
            }

            return $return;
        }

        $tag  = strtolower($this->content->copyByToken('slash', true));
        if (trim($tag) == '')
        {
            // no tag found, invalid < found
            return $return;
        }
        $node = new HtmlNode($tag);
        $node->setHtmlSpecialCharsDecode($this->options->htmlSpecialCharsDecode);

        // attributes
        while ($this->content->char() != '>' &&
            $this->content->char() != '/') {
            $space = $this->content->skipByToken('blank', true);
            if (empty($space)) {
                $this->content->fastForward(1);
                continue;
            }

            $name = $this->content->copyByToken('equal', true);
            if ($name == '/') {
                break;
            }

            if (empty($name)) {
				$this->content->skipByToken('blank');
				continue;
            }

            $this->content->skipByToken('blank');
            if ($this->content->char() == '=') {
                $attr = [];
                $this->content->fastForward(1)
                              ->skipByToken('blank');
                switch ($this->content->char()) {
                    case '"':
                        $attr['doubleQuote'] = true;
                        $this->content->fastForward(1);
                        $string = $this->content->copyUntil('"', true);
                        do {
                            $moreString = $this->content->copyUntilUnless('"', '=>');
                            $string .= $moreString;
                        } while ( ! empty($moreString));
                        $attr['value'] = $string;
                        $this->content->fastForward(1);
                        $node->getTag()->$name = $attr;
                        break;
                    case "'":
                        $attr['doubleQuote'] = false;
                        $this->content->fastForward(1);
                        $string = $this->content->copyUntil("'", true);
                        do {
                            $moreString = $this->content->copyUntilUnless("'", '=>');
                            $string .= $moreString;
                        } while ( ! empty($moreString));
                        $attr['value'] = $string;
                        $this->content->fastForward(1);
                        $node->getTag()->$name = $attr;
                        break;
                    default:
                        $attr['doubleQuote']   = true;
                        $attr['value']         = $this->content->copyByToken('attr', true);
                        $node->getTag()->$name = $attr;
                        break;
                }
            } else {
                // no value attribute
                if ($this->options->strict) {
                    // can't have this in strict html
                    $character = $this->content->getPosition();
                    throw new StrictException("Tag '$tag' has an attribute '$name' with out a value! (character #$character)");
                }
                $node->getTag()->$name = [
                    'value'       => null,
                    'doubleQuote' => true,
                ];
                if ($this->content->char() != '>') {
                    $this->content->rewind(1);
                }
            }
        }

        $this->content->skipByToken('blank');
        $tag = strtolower($tag);
        if ($this->content->char() == '/') {
            // self closing tag
            $node->getTag()->selfClosing();
            $this->content->fastForward(1);
        } elseif (in_array($tag, $this->selfClosing, true)) {

            // Should be a self closing tag, check if we are strict
            if ($this->options->strict) {
                $character = $this->content->getPosition();
                throw new StrictException("Tag '$tag' is not self closing! (character #$character)");
            }

            // We force self closing on this tag.
            $node->getTag()->selfClosing();

            // Should this tag use a trailing slash?
            if(in_array($tag, $this->noSlash, true))
            {
                $node->getTag()->noTrailingSlash();
            }

        }

        $this->content->fastForward(1);

        $return['status'] = true;
        $return['node']   = $node;

        return $return;
    }

    /**
     * Attempts to detect the charset that the html was sent in.
     *
     * @return bool
     * @throws ChildNotFoundException
     */
    protected function detectCharset(): bool
    {
        // set the default
        $encode = new Encode;
        $encode->from($this->defaultCharset);
        $encode->to($this->defaultCharset);

        $enforceEncoding = $this->options->enforceEncoding;
        if ( ! is_null($enforceEncoding)) {
            //  they want to enforce the given encoding
            $encode->from($enforceEncoding);
            $encode->to($enforceEncoding);

            return false;
        }

        /** @var AbstractNode $meta */
        $meta = $this->root->find('meta[http-equiv=Content-Type]', 0);
        if (is_null($meta)) {
            // could not find meta tag
            $this->root->propagateEncoding($encode);

            return false;
        }
        $content = $meta->getAttribute('content');
        if (is_null($content)) {
            // could not find content
            $this->root->propagateEncoding($encode);

            return false;
        }
        $matches = [];
        if (preg_match('/charset=(.+)/', $content, $matches)) {
            $encode->from(trim($matches[1]));
            $this->root->propagateEncoding($encode);

            return true;
        }

        // no charset found
        $this->root->propagateEncoding($encode);

        return false;
    }
}
