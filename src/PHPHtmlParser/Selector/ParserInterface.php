<?php declare(strict_types=1);
namespace PHPHtmlParser\Selector;

interface ParserInterface
{
    public function parseSelectorString(string $selector): array;
}
