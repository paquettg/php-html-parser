<?php

declare(strict_types=1);

namespace PHPHtmlParser;

use GuzzleHttp\Psr7\Request;
use Http\Adapter\Guzzle6\Client;
use PHPHtmlParser\Dom\AbstractNode;
use PHPHtmlParser\Dom\Collection;
use PHPHtmlParser\Dom\HtmlNode;
use PHPHtmlParser\Dom\TextNode;
use PHPHtmlParser\Enum\StringToken;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ContentLengthException;
use PHPHtmlParser\Exceptions\LogicalException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;
use PHPHtmlParser\Exceptions\UnknownChildTypeException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use stringEncode\Encode;

/**
 * Class Dom.
 */
class Dom
{
    /**
     * Contains the root node of this dom tree.
     *
     * @var HtmlNode
     */
    public $root;

    /**
     * The charset we would like the output to be in.
     *
     * @var string
     */
    private $defaultCharset = 'UTF-8';

    /**
     * The raw version of the document string.
     *
     * @var string
     */
    private $raw;

    /**
     * The document string.
     *
     * @var Content
     */
    private $content;

    /**
     * The original file size of the document.
     *
     * @var int
     */
    private $rawSize;

    /**
     * The size of the document after it is cleaned.
     *
     * @var int
     */
    private $size;

    /**
     * A global options array to be used by all load calls.
     *
     * @var ?Options
     */
    private $globalOptions;

    /**
     * A persistent option object to be used for all options in the
     * parsing of the file.
     *
     * @var Options
     */
    private $options;

    /**
     * A list of tags which will always be self closing.
     *
     * @var array
     */
    private $selfClosing = [
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
        'wbr',
    ];

    /**
     * A list of tags where there should be no /> at the end (html5 style).
     *
     * @var array
     */
    private $noSlash = [];

    /**
     * Returns the inner html of the root node.
     *
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
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->root->$name;
    }

    /**
     * Loads the dom from a document file/url.
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws StrictException
     * @throws LogicalException
     */
    public function loadFromFile(string $file, ?Options $options = null): Dom
    {
        $content = @\file_get_contents($file);
        if ($content === false) {
            throw new LogicalException('file_get_contents failed and returned false when trying to read "' . $file . '".');
        }

        return $this->loadStr($content, $options);
    }

    /**
     * Use a curl interface implementation to attempt to load
     * the content from a url.
     *
     * @param ClientInterface $client
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws StrictException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function loadFromUrl(string $url, ?Options $options, ?ClientInterface $client = null, ?RequestInterface $request = null): Dom
    {
        if ($client === null) {
            $client = new Client();
        }
        if ($request === null) {
            $request = new Request('GET', $url);
        }

        $response = $client->sendRequest($request);
        $content = $response->getBody()->getContents();

        return $this->loadStr($content, $options);
    }

    /**
     * Parsers the html of the given string. Used for load(), loadFromFile(),
     * and loadFromUrl().
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws StrictException
     */
    public function loadStr(string $str, ?Options $options = null): Dom
    {
        $this->options = new Options();
        if ($this->globalOptions !== null) {
            $this->options->setFromOptions($this->globalOptions);
        }
        if ($options !== null) {
            $this->options->setFromOptions($options);
        }

        $this->rawSize = \strlen($str);
        $this->raw = $str;

        $html = $this->clean($str);

        $this->size = \strlen($str);
        $this->content = new Content($html);

        $this->parse();
        $this->detectCharset();

        return $this;
    }

    /**
     * Sets a global options array to be used by all load calls.
     *
     * @chainable
     */
    public function setOptions(Options $options): Dom
    {
        $this->globalOptions = $options;

        return $this;
    }

    /**
     * Find elements by css selector on the root node.
     *
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     *
     * @return mixed|Collection|null
     */
    public function find(string $selector, int $nth = null)
    {
        $this->isLoaded();

        return $this->root->find($selector, $nth);
    }

    /**
     * Adds the tag (or tags in an array) to the list of tags that will always
     * be self closing.
     *
     * @param string|array $tag
     * @chainable
     */
    public function addSelfClosingTag($tag): Dom
    {
        if (!\is_array($tag)) {
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
     * @chainable
     */
    public function removeSelfClosingTag($tag): Dom
    {
        if (!\is_array($tag)) {
            $tag = [$tag];
        }
        $this->selfClosing = \array_diff($this->selfClosing, $tag);

        return $this;
    }

    /**
     * Sets the list of self closing tags to empty.
     *
     * @chainable
     */
    public function clearSelfClosingTags(): Dom
    {
        $this->selfClosing = [];

        return $this;
    }

    /**
     * Adds a tag to the list of self closing tags that should not have a trailing slash.
     *
     * @param $tag
     * @chainable
     */
    public function addNoSlashTag($tag): Dom
    {
        if (!\is_array($tag)) {
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
     * @chainable
     */
    public function removeNoSlashTag($tag): Dom
    {
        if (!\is_array($tag)) {
            $tag = [$tag];
        }
        $this->noSlash = \array_diff($this->noSlash, $tag);

        return $this;
    }

    /**
     * Empties the list of no-slash tags.
     *
     * @chainable
     */
    public function clearNoSlashTags(): Dom
    {
        $this->noSlash = [];

        return $this;
    }

    /**
     * Simple wrapper function that returns the first child.
     *
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
     *
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     */
    public function lastChild(): AbstractNode
    {
        $this->isLoaded();

        return $this->root->lastChild();
    }

    /**
     * Simple wrapper function that returns count of child elements.
     *
     * @throws NotLoadedException
     */
    public function countChildren(): int
    {
        $this->isLoaded();

        return $this->root->countChildren();
    }

    /**
     * Get array of children.
     *
     * @throws NotLoadedException
     */
    public function getChildren(): array
    {
        $this->isLoaded();

        return $this->root->getChildren();
    }

    /**
     * Check if node have children nodes.
     *
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
     *
     * @param $id
     *
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     *
     * @return mixed|Collection|null
     */
    public function getElementById($id)
    {
        $this->isLoaded();

        return $this->find('#' . $id, 0);
    }

    /**
     * Simple wrapper function that returns all elements by
     * tag name.
     *
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     *
     * @return mixed|Collection|null
     */
    public function getElementsByTag(string $name)
    {
        $this->isLoaded();

        return $this->find($name);
    }

    /**
     * Simple wrapper function that returns all elements by
     * class name.
     *
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     *
     * @return mixed|Collection|null
     */
    public function getElementsByClass(string $class)
    {
        $this->isLoaded();

        return $this->find('.' . $class);
    }

    /**
     * Checks if the load methods have been called.
     *
     * @throws NotLoadedException
     */
    private function isLoaded(): void
    {
        if (\is_null($this->content)) {
            throw new NotLoadedException('Content is not loaded!');
        }
    }

    /**
     * Cleans the html of any none-html information.
     */
    private function clean(string $str): string
    {
        if ($this->options->isCleanupInput() != true) {
            // skip entire cleanup step
            return $str;
        }

        $is_gzip = 0 === \mb_strpos($str, "\x1f" . "\x8b" . "\x08", 0, 'US-ASCII');
        if ($is_gzip) {
            $str = \gzdecode($str);
            if ($str === false) {
                throw new LogicalException('gzdecode returned false. Error when trying to decode the string.');
            }
        }

        // remove white space before closing tags
        $str = \mb_eregi_replace("'\s+>", "'>", $str);
        if ($str === false) {
            throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to clean single quotes.');
        }
        $str = \mb_eregi_replace('"\s+>', '">', $str);
        if ($str === false) {
            throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to clean double quotes.');
        }

        // clean out the \n\r
        $replace = ' ';
        if ($this->options->isPreserveLineBreaks()) {
            $replace = '&#10;';
        }
        $str = \str_replace(["\r\n", "\r", "\n"], $replace, $str);
        if ($str === false) {
            throw new LogicalException('str_replace returned false instead of a string. Error when attempting to clean input string.');
        }

        // strip the doctype
        $str = \mb_eregi_replace('<!doctype(.*?)>', '', $str);
        if ($str === false) {
            throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to strip the doctype.');
        }

        // strip out comments
        $str = \mb_eregi_replace('<!--(.*?)-->', '', $str);
        if ($str === false) {
            throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to strip comments.');
        }

        // strip out cdata
        $str = \mb_eregi_replace("<!\[CDATA\[(.*?)\]\]>", '', $str);
        if ($str === false) {
            throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to strip out cdata.');
        }

        // strip out <script> tags
        if ($this->options->isRemoveScripts()) {
            $str = \mb_eregi_replace("<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>", '', $str);
            if ($str === false) {
                throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to remove scripts 1.');
            }
            $str = \mb_eregi_replace("<\s*script\s*>(.*?)<\s*/\s*script\s*>", '', $str);
            if ($str === false) {
                throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to remove scripts 2.');
            }
        }

        // strip out <style> tags
        if ($this->options->isRemoveStyles()) {
            $str = \mb_eregi_replace("<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>", '', $str);
            if ($str === false) {
                throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to strip out style tags 1.');
            }
            $str = \mb_eregi_replace("<\s*style\s*>(.*?)<\s*/\s*style\s*>", '', $str);
            if ($str === false) {
                throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to strip out style tags 2.');
            }
        }

        // strip smarty scripts
        if ($this->options->isRemoveSmartyScripts()) {
            $str = \mb_eregi_replace("(\{\w)(.*?)(\})", '', $str);
            if ($str === false) {
                throw new LogicalException('mb_eregi_replace returned false instead of a string. Error when attempting to remove smarty scripts.');
            }
        }

        return $str;
    }

    /**
     * Attempts to parse the html in content.
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws StrictException
     * @throws LogicalException
     */
    private function parse(): void
    {
        // add the root node
        $this->root = new HtmlNode('root');
        $this->root->setHtmlSpecialCharsDecode($this->options->isHtmlSpecialCharsDecode());
        $activeNode = $this->root;
        while ($activeNode !== null) {
            if ($activeNode && $activeNode->tag->name() === 'script'
                && $this->options->isCleanupInput() != true
            ) {
                $str = $this->content->copyUntil('</');
            } else {
                $str = $this->content->copyUntil('<');
            }
            if ($str == '') {
                $info = $this->parseTag();
                if (!$info['status']) {
                    // we are done here
                    $activeNode = null;
                    continue;
                }

                // check if it was a closing tag
                if ($info['closing']) {
                    $foundOpeningTag = true;
                    $originalNode = $activeNode;
                    while ($activeNode->getTag()->name() != $info['tag']) {
                        $activeNode = $activeNode->getParent();
                        if ($activeNode === null) {
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

                if (!isset($info['node'])) {
                    continue;
                }

                /** @var AbstractNode $node */
                $node = $info['node'];
                $activeNode->addChild($node);

                // check if node is self closing
                if (!$node->getTag()->isSelfClosing()) {
                    $activeNode = $node;
                }
            } elseif ($this->options->isWhitespaceTextNode() ||
                \trim($str) != ''
            ) {
                // we found text we care about
                $textNode = new TextNode($str, $this->options->isRemoveDoubleSpace());
                $textNode->setHtmlSpecialCharsDecode($this->options->isHtmlSpecialCharsDecode());
                $activeNode->addChild($textNode);
            }
        }
    }

    /**
     * Attempt to parse a tag out of the content.
     *
     * @throws StrictException
     */
    private function parseTag(): array
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
        try {
            $this->content->fastForward(1);
        } catch (ContentLengthException $exception) {
            // we are at the end of the file
            return $return;
        }
        if ($this->content->char() == '/') {
            // end tag
            $tag = $this->content->fastForward(1)
                ->copyByToken(StringToken::SLASH(), true);
            // move to end of tag
            $this->content->copyUntil('>');
            $this->content->fastForward(1);

            // check if this closing tag counts
            $tag = \strtolower($tag);
            if (\in_array($tag, $this->selfClosing, true)) {
                $return['status'] = true;

                return $return;
            }
            $return['status'] = true;
            $return['closing'] = true;
            $return['tag'] = \strtolower($tag);

            return $return;
        }

        $tag = \strtolower($this->content->copyByToken(StringToken::SLASH(), true));
        if (\trim($tag) == '') {
            // no tag found, invalid < found
            return $return;
        }
        $node = new HtmlNode($tag);
        $node->setHtmlSpecialCharsDecode($this->options->isHtmlSpecialCharsDecode());

        // attributes
        while (
            $this->content->char() != '>' &&
            $this->content->char() != '/'
        ) {
            $space = $this->content->skipByToken(StringToken::BLANK(), true);
            if (empty($space)) {
                try {
                    $this->content->fastForward(1);
                } catch (ContentLengthException $exception) {
                    // reached the end of the content
                    break;
                }
                continue;
            }

            $name = $this->content->copyByToken(StringToken::EQUAL(), true);
            if ($name == '/') {
                break;
            }

            if (empty($name)) {
                $this->content->skipByToken(StringToken::BLANK());
                continue;
            }

            $this->content->skipByToken(StringToken::BLANK());
            if ($this->content->char() == '=') {
                $this->content->fastForward(1)
                    ->skipByToken(StringToken::BLANK());
                switch ($this->content->char()) {
                    case '"':
                        $this->content->fastForward(1);
                        $string = $this->content->copyUntil('"', true);
                        do {
                            $moreString = $this->content->copyUntilUnless('"', '=>');
                            $string .= $moreString;
                        } while (\strlen($moreString) > 0 && $this->content->getPosition() < $this->size);
                        $attr['value'] = $string;
                        $this->content->fastForward(1);
                        $node->getTag()->setAttribute($name, $string);
                        break;
                    case "'":
                        $this->content->fastForward(1);
                        $string = $this->content->copyUntil("'", true);
                        do {
                            $moreString = $this->content->copyUntilUnless("'", '=>');
                            $string .= $moreString;
                        } while (\strlen($moreString) > 0 && $this->content->getPosition() < $this->size);
                        $attr['value'] = $string;
                        $this->content->fastForward(1);
                        $node->getTag()->setAttribute($name, $string, false);
                        break;
                    default:
                        $node->getTag()->setAttribute($name, $this->content->copyByToken(StringToken::ATTR(), true));
                        break;
                }
            } else {
                // no value attribute
                if ($this->options->isStrict()) {
                    // can't have this in strict html
                    $character = $this->content->getPosition();
                    throw new StrictException("Tag '$tag' has an attribute '$name' with out a value! (character #$character)");
                }
                $node->getTag()->setAttribute($name, null);
                if ($this->content->char() != '>') {
                    $this->content->rewind(1);
                }
            }
        }

        $this->content->skipByToken(StringToken::BLANK());
        $tag = \strtolower($tag);
        if ($this->content->char() == '/') {
            // self closing tag
            $node->getTag()->selfClosing();
            $this->content->fastForward(1);
        } elseif (\in_array($tag, $this->selfClosing, true)) {
            // Should be a self closing tag, check if we are strict
            if ($this->options->isStrict()) {
                $character = $this->content->getPosition();
                throw new StrictException("Tag '$tag' is not self closing! (character #$character)");
            }

            // We force self closing on this tag.
            $node->getTag()->selfClosing();

            // Should this tag use a trailing slash?
            if (\in_array($tag, $this->noSlash, true)) {
                $node->getTag()->noTrailingSlash();
            }
        }

        if ($this->content->canFastForward(1)) {
            $this->content->fastForward(1);
        }

        $return['status'] = true;
        $return['node'] = $node;

        return $return;
    }

    /**
     * Attempts to detect the charset that the html was sent in.
     *
     * @throws ChildNotFoundException
     */
    private function detectCharset(): bool
    {
        // set the default
        $encode = new Encode();
        $encode->from($this->defaultCharset);
        $encode->to($this->defaultCharset);

        $enforceEncoding = $this->options->getEnforceEncoding();
        if ($enforceEncoding !== null) {
            //  they want to enforce the given encoding
            $encode->from($enforceEncoding);
            $encode->to($enforceEncoding);

            return false;
        }

        /** @var AbstractNode $meta */
        $meta = $this->root->find('meta[http-equiv=Content-Type]', 0);
        if ($meta == null) {
            if (!$this->detectHTML5Charset($encode)) {
                // could not find meta tag
                $this->root->propagateEncoding($encode);

                return false;
            }

            return true;
        }
        $content = $meta->getAttribute('content');
        if (\is_null($content)) {
            // could not find content
            $this->root->propagateEncoding($encode);

            return false;
        }
        $matches = [];
        if (\preg_match('/charset=([^;]+)/', $content, $matches)) {
            $encode->from(\trim($matches[1]));
            $this->root->propagateEncoding($encode);

            return true;
        }

        // no charset found
        $this->root->propagateEncoding($encode);

        return false;
    }

    private function detectHTML5Charset(Encode $encode): bool
    {
        /** @var AbstractNode|null $meta */
        $meta = $this->root->find('meta[charset]', 0);
        if ($meta == null) {
            return false;
        }

        $encode->from(\trim($meta->getAttribute('charset')));
        $this->root->propagateEncoding($encode);

        return true;
    }
}
