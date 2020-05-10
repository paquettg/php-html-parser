<?php

declare(strict_types=1);

namespace PHPHtmlParser\DTO\Selector;

final class ParsedSelectorDTO
{
    /**
     * @var RuleDTO[]
     */
    private $rules = [];

    public function __construct(array $values)
    {
        foreach ($values as $value) {
            if ($value instanceof RuleDTO) {
                $this->rules[] = $value;
            }
        }
    }

    /**
     * @return RuleDTO[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
