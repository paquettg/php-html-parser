<?php
namespace PHPHtmlParser;

/**
 * Interface CurlInterface
 *
 * @package PHPHtmlParser
 */
interface CurlInterface
{

    /**
     * This method should return the content of the url in a string
     *
     * @param string $url
     * @return string
     */
    public function get($url);
}
