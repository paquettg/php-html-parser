<?php

declare(strict_types=1);

namespace PHPHtmlParser\DTO\Selector;

final class ParsedSelectorCollectionDTO
{
    /**
     * @var ParsedSelectorDTO[]
     */
    private $parsedSelectorDTO = [];

    public function __construct(array $values)
    {
        foreach ($values as $value) {
            if ($value instanceof ParsedSelectorDTO) {
                $this->parsedSelectorDTO[] = $value;
            }
        }
    }

    /**
     * @return ParsedSelectorDTO[]
     */
    public function getParsedSelectorDTO(): array
    {
        return $this->parsedSelectorDTO;
    }
}
