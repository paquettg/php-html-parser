<?php
namespace PHPHtmlParser\Selector;

interface ParserInterface
{
    public function parseSelectorString(string $selector): array;
}
