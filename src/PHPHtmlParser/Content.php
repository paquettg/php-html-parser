<?php 

declare(strict_types=1);

namespace PHPHtmlParser;


use PHPHtmlParser\Exceptions\LogicalException;

/**
 * Class Content
 *
 * @package PHPHtmlParser
 */
class Content
{

    /**
     * The content string.
     *
     * @var string
     */
    protected $content;

    /**
     * The size of the content.
     *
     * @var integer
     */
    protected $size;

    /**
     * The current position we are in the content.
     *
     * @var integer
     */
    protected $pos;

    /**
     * The following 4 strings are tags that are important to us.
     *
     * @var string
     */
    protected $blank = " \t\r\n";
    protected $equal = ' =/>';
    protected $slash = " />\r\n\t";
    protected $attr = ' >';

    /**
     * Content constructor.
     *
     * @param string $content
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;
        $this->size    = strlen($content);
        $this->pos     = 0;
    }

    /**
     * Returns the current position of the content.
     *
     * @return int
     */
    public function getPosition(): int
    {
        return $this->pos;
    }

    /**
     * Gets the current character we are at.
     *
     * @param ?int $char
     * @return string
     */
    public function char(?int $char = null): string
    {
        $pos = $this->pos;
        if ( ! is_null($char)) {
            $pos = $char;
        }

        if ( ! isset($this->content[$pos])) {
            return '';
        }

        return $this->content[$pos];
    }

    /**
     * Moves the current position forward.
     *
     * @param int $count
     * @return Content
     * @chainable
     */
    public function fastForward(int $count): Content
    {
        $this->pos += $count;

        return $this;
    }

    /**
     * Moves the current position backward.
     *
     * @param int $count
     * @return Content
     * @chainable
     */
    public function rewind(int $count): Content
    {
        $this->pos -= $count;
        if ($this->pos < 0) {
            $this->pos = 0;
        }

        return $this;
    }

    /**
     * Copy the content until we find the given string.
     *
     * @param string $string
     * @param bool $char
     * @param bool $escape
     * @return string
     */
    public function copyUntil(string $string, bool $char = false, bool $escape = false): string
    {
        if ($this->pos >= $this->size) {
            // nothing left
            return '';
        }

        if ($escape) {
            $position = $this->pos;
            $found    = false;
            while ( ! $found) {
                $position = strpos($this->content, $string, $position);
                if ($position === false) {
                    // reached the end
                    break;
                }

                if ($this->char($position - 1) == '\\') {
                    // this character is escaped
                    ++$position;
                    continue;
                }

                $found = true;
            }
        } elseif ($char) {
            $position = strcspn($this->content, $string, $this->pos);
            $position += $this->pos;
        } else {
            $position = strpos($this->content, $string, $this->pos);
        }

        if ($position === false) {
            // could not find character, just return the remaining of the content
            $return    = substr($this->content, $this->pos, $this->size - $this->pos);
            if ($return === false) {
                throw new LogicalException('Substr returned false with position '.$this->pos.'.');
            }
            $this->pos = $this->size;

            return $return;
        }

        if ($position == $this->pos) {
            // we are at the right place
            return '';
        }

        $return = substr($this->content, $this->pos, $position - $this->pos);
        if ($return === false) {
            throw new LogicalException('Substr returned false with position '.$this->pos.'.');
        }
        // set the new position
        $this->pos = $position;

        return $return;
    }

    /**
     * Copies the content until the string is found and return it
     * unless the 'unless' is found in the substring.
     *
     * @param string $string
     * @param string $unless
     * @return string
     */
    public function copyUntilUnless(string $string, string $unless)
    {
        $lastPos = $this->pos;
        $this->fastForward(1);
        $foundString = $this->copyUntil($string, true, true);

        $position = strcspn($foundString, $unless);
        if ($position == strlen($foundString)) {
            return $string.$foundString;
        }
        // rewind changes and return nothing
        $this->pos = $lastPos;

        return '';
    }

    /**
     * Copies the content until it reaches the token string.,
     *
     * @param string $token
     * @param bool $char
     * @param bool $escape
     * @return string
     * @uses $this->copyUntil()
     */
    public function copyByToken(string $token, bool $char = false, bool $escape = false)
    {
        $string = $this->$token;

        return $this->copyUntil($string, $char, $escape);
    }

    /**
     * Skip a given set of characters.
     *
     * @param string $string
     * @param bool $copy
     * @return Content|string
     */
    public function skip(string $string, bool $copy = false)
    {
        $len = strspn($this->content, $string, $this->pos);

        // make it chainable if they don't want a copy
        $return = $this;
        if ($copy) {
            $return = substr($this->content, $this->pos, $len);
            if ($return === false) {
                throw new LogicalException('Substr returned false with position '.$this->pos.'.');
            }
        }

        // update the position
        $this->pos += $len;

        return $return;
    }

    /**
     * Skip a given token of pre-defined characters.
     *
     * @param string $token
     * @param bool $copy
     * @return Content|string
     * @uses $this->skip()
     */
    public function skipByToken(string $token, bool $copy = false)
    {
        $string = $this->$token;

        return $this->skip($string, $copy);
    }
}
