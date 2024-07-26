<?php

declare(strict_types=1);

namespace PHPHtmlParser\Selector;

use PHPHtmlParser\Contracts\Selector\ParserInterface;
use PHPHtmlParser\DTO\Selector\ParsedSelectorCollectionDTO;
use PHPHtmlParser\DTO\Selector\ParsedSelectorDTO;
use PHPHtmlParser\DTO\Selector\RuleDTO;

/**
 * This is the default parser for the selector.
 */
class Parser implements ParserInterface
{
    /**
     * Pattern of CSS selectors, modified from 'mootools'.
     *
     * @var string
     */
    private $pattern = "/([\w:*>+~-]*(?:\([\w\d]+\))?)(?:#([\w-]+)|\.([\w\.-]+))?(?:\[@?(!?[\w:-]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";

    /**
     * Parses the selector string.
     */
    public function parseSelectorString(string $selector): ParsedSelectorCollectionDTO
    {
        $selectors = [];
        $matches = [];
        $rules = [];
        \preg_match_all($this->pattern, \trim($selector) . ' ', $matches, PREG_SET_ORDER);

        // skip tbody
        foreach ($matches as $match) {
            // default values
            $tag = \strtolower(\trim($match[1]));
            $operator = '=';
            $key = null;
            $value = null;
            $noKey = false;
            $alterNext = false;
            $isNthOfType = false;

            // check for elements that alter the behavior of the next element
            if ($tag == '>' || $tag == '+' || $tag == '~') {
                $alterNext = true;
            }

            // check for id selector
            if (!empty($match[2])) {
                $key = 'id';
                $value = $match[2];
            }

            // check for class selector
            if (!empty($match[3])) {
                $key = 'class';
                $value = \explode('.', $match[3]);
            }

            // check for pseudoclass selector
            if (strpos($match[0], ':') !== false) {
                $pos = strpos($match[0], ':');
                $key = 'pseudoclass';
                $tag = $pos > 0 ? substr($match[0], 0, $pos) : '*';
                $value = \substr($match[0], $pos+1);

                if (\trim($value, ', ') == 'first-child') {
                    $value = 'nth-child(1)';
                }
                else if (\trim($value, ', ') == 'last-child') {
                    $value = 'nth-last-child(1)';
                }
                else if (\trim($value, ', ') == 'first-of-type') {
                    $value = 'nth-of-type(1)';
                }
                else if (\trim($value, ', ') == 'last-of-type') {
                    $value = 'nth-last-of-type(1)';
                }

                if (preg_match("/^(nth-child|nth-of-type)\(\d+\)$/", \trim($value, ', '))) {
                    preg_match_all("/^(nth-child|nth-of-type)\((\d+)\)$/", \trim($value, ', '), $matches, PREG_SET_ORDER);
                    $key = (int) $matches[0][2];
                } else if (preg_match("/^(nth-last-child|nth-last-of-type)\(\d+\)$/", \trim($value, ', '))) {
                    preg_match_all("/^(nth-last-child|nth-last-of-type)\((\d+)\)$/", \trim($value, ', '), $matches, PREG_SET_ORDER);
                    $key = - (int) $matches[0][2];
                }
                $isNthOfType = preg_match("/^nth(-last)?-of-type\(\d+\)$/", \trim($value, ', '));
            }

            // and final attribute selector
            else if (!empty($match[4])) {
                $key = \strtolower($match[4]);
            }
            else if (!empty($match[5])) {
                $operator = $match[5];
            }
            if (!empty($match[6])) {
                $value = $match[6];
                if (\strpos($value, '][') !== false) {
                    // we have multiple type selectors
                    $keys = [];
                    $keys[] = $key;
                    $key = $keys;
                    $parts = \explode('][', $value);
                    $value = [];
                    foreach ($parts as $part) {
                        if (\strpos($part, '=') !== false) {
                            list($first, $second) = \explode('=', $part);
                            $key[] = $first;
                            $value[] = $second;
                        } else {
                            $value[] = $part;
                        }
                    }
                }
            }

            // check for elements that do not have a specified attribute
            if (\is_string($key) && isset($key[0]) && $key[0] == '!') {
                $key = \substr($key, 1);
                $noKey = true;
            }

            $rules[] = RuleDTO::makeFromPrimitives(
                $tag,
                $operator,
                $key,
                $value,
                $noKey,
                $alterNext,
                $isNthOfType
            );
            if (isset($match[7]) && \is_string($match[7]) && \trim($match[7]) == ',') {
                $selectors[] = ParsedSelectorDTO::makeFromRules($rules);
                $rules = [];
            }
        }

        // save last results
        if (\count($rules) > 0) {
            $selectors[] = ParsedSelectorDTO::makeFromRules($rules);
        }

        return ParsedSelectorCollectionDTO::makeCollection($selectors);
    }
}
