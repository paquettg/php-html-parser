<?php declare(strict_types=1);
namespace PHPHtmlParser\Selector;

/**
 * This is the parser for the selector.
 *
 * 
 */
class Parser implements ParserInterface
{

    /**
     * Pattern of CSS selectors, modified from 'mootools'
     *
     * @var string
     */
    protected $pattern = "/([\w\-:\*>]*)(?:\#([\w\-]+)|\.([\w\.\-]+))?(?:\[@?(!?[\w\-:]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";

    /**
     * Parses the selector string
     *
     * @param string $selector
     *
     * @return array
     */
    public function parseSelectorString(string $selector): array
    {
        $selectors = [];

        $matches = [];
        preg_match_all($this->pattern, trim($selector).' ', $matches, PREG_SET_ORDER);

        // skip tbody
        $result = [];
        foreach ($matches as $match) {
            // default values
            $tag       = strtolower(trim($match[1]));
            $operator  = '=';
            $key       = null;
            $value     = null;
            $noKey     = false;
            $alterNext = false;

            // check for elements that alter the behavior of the next element
            if ($tag == '>') {
                $alterNext = true;
            }

            // check for id selector
            if ( ! empty($match[2])) {
                $key   = 'id';
                $value = $match[2];
            }

            // check for class selector
            if ( ! empty($match[3])) {
                $key   = 'class';
                $value = explode('.', $match[3]);
            }

            // and final attribute selector
            if ( ! empty($match[4])) {
                $key = strtolower($match[4]);
            }
            if ( ! empty($match[5])) {
                $operator = $match[5];
            }
            if ( ! empty($match[6])) {
                $value = $match[6];
            }

            // check for elements that do not have a specified attribute
            if (isset($key[0]) && $key[0] == '!') {
                $key   = substr($key, 1);
                $noKey = true;
            }

            $result[] = [
                'tag'       => $tag,
                'key'       => $key,
                'value'     => $value,
                'operator'  => $operator,
                'noKey'     => $noKey,
                'alterNext' => $alterNext,
            ];
            if (trim($match[7]) == ',') {
                $selectors[] = $result;
                $result      = [];
            }
        }

        // save last results
        if (count($result) > 0) {
            $selectors[] = $result;
        }

        return $selectors;
    }
}
