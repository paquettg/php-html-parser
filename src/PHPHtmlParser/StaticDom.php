<?php declare(strict_types=1);
namespace PHPHtmlParser;

use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\CurlException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;

/**
 * Class StaticDom
 *
 * @package PHPHtmlParser
 */
final class StaticDom
{

    private static $dom = null;

    /**
     * Attempts to call the given method on the most recent created dom
     * from bellow.
     *
     * @param string $method
     * @param array $arguments
     * @throws NotLoadedException
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        if (self::$dom instanceof Dom) {
            return call_user_func_array([self::$dom, $method], $arguments);
        } else {
            throw new NotLoadedException('The dom is not loaded. Can not call a dom method.');
        }
    }

    /**
     * Call this to mount the static facade. The facade allows you to use
     * this object as a $className.
     *
     * @param string $className
     * @param Dom $dom
     * @return bool
     */
    public static function mount(string $className = 'Dom', Dom $dom = null): bool
    {
        if (class_exists($className)) {
            return false;
        }
        class_alias(__CLASS__, $className);
        if ($dom instanceof Dom) {
            self::$dom = $dom;
        }

        return true;
    }

    /**
     * Creates a new dom object and calls load() on the
     * new object.
     * @param string $str
     * @return Dom
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws StrictException
     */
    public static function load(string $str): Dom
    {
        $dom       = new Dom;
        self::$dom = $dom;

        return $dom->load($str);
    }

    /**
     * Creates a new dom object and calls loadFromFile() on the
     * new object.
     * @param string $file
     * @return Dom
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws StrictException
     */
    public static function loadFromFile(string $file): Dom
    {
        $dom       = new Dom;
        self::$dom = $dom;

        return $dom->loadFromFile($file);
    }

    /**
     * Creates a new dom object and calls loadFromUrl() on the
     * new object.
     * @param string                            $url
     * @param array                             $options
     * @param CurlInterface|null $curl
     * @return Dom
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws StrictException
     */
    public static function loadFromUrl(string $url, array $options = [], CurlInterface $curl = null): Dom
    {
        $dom       = new Dom;
        self::$dom = $dom;
        if (is_null($curl)) {
            // use the default curl interface
            $curl = new Curl;
        }

        return $dom->loadFromUrl($url, $options, $curl);
    }

    /**
     * Sets the $dom variable to null.
     */
    public static function unload(): void
    {
        self::$dom = null;
    }
}
