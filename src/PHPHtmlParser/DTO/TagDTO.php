<?php

declare(strict_types=1);

namespace PHPHtmlParser\DTO;

use PHPHtmlParser\Dom\Node\HtmlNode;

final class TagDTO
{
    /**
     * @var bool
     */
    private $status;

    /**
     * @var bool
     */
    private $closing;

    /**
     * @var ?HtmlNode
     */
    private $node;

    /**
     * @var ?string
     */
    private $tag;

    public function __construct(array $values = [])
    {
        $this->status = $values['status'] ?? false;
        $this->closing = $values['closing'] ?? false;
        $this->node = $values['node'] ?? null;
        $this->tag = $values['tag'] ?? null;
    }

    /**
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isClosing(): bool
    {
        return $this->closing;
    }

    /**
     * @return mixed
     */
    public function getNode(): ?HtmlNode
    {
        return $this->node;
    }

    /**
     * @return mixed
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }
}
