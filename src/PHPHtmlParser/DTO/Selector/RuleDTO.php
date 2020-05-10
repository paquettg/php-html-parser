<?php

declare(strict_types=1);

namespace PHPHtmlParser\DTO\Selector;

final class RuleDTO
{
    /**
     * @var string
     */
    private $tag;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var string|array|null
     */
    private $key;

    /**
     * @var string|array|null
     */
    private $value;

    /**
     * @var bool
     */
    private $noKey;

    /**
     * @var bool
     */
    private $alterNext;

    public function __construct(array $values)
    {
        $this->tag = $values['tag'];
        $this->operator = $values['operator'];
        $this->key = $values['key'];
        $this->value = $values['value'];
        $this->noKey = $values['noKey'];
        $this->alterNext = $values['alterNext'];
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return string|array|null
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string|array|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isNoKey(): bool
    {
        return $this->noKey;
    }

    /**
     * @return bool
     */
    public function isAlterNext(): bool
    {
        return $this->alterNext;
    }
}
