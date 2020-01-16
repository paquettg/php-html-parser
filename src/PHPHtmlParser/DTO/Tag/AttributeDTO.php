<?php

declare(strict_types=1);

namespace PHPHtmlParser\DTO\Tag;

use stringEncode\Encode;
use stringEncode\Exception;

class AttributeDTO
{
    /**
     * @var ?string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $doubleQuote = true;

    public function __construct(array $values)
    {
        $this->value = $values['value'];
        $this->doubleQuote = $values['doubleQuote'];
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isDoubleQuote(): bool
    {
        return $this->doubleQuote;
    }

    public function htmlspecialcharsDecode(): void
    {
        $this->value = htmlspecialchars_decode($this->value);
    }

    /**
     * @param Encode $encode
     * @throws Exception
     */
    public function encodeValue(Encode $encode)
    {
        $this->value = $encode->convert($this->value);
    }
}
