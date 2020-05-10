<?php

declare(strict_types=1);

namespace PHPHtmlParser\Selector;

use PHPHtmlParser\Contracts\Selector\ParserInterface;
use PHPHtmlParser\Contracts\Selector\SeekerInterface;
use PHPHtmlParser\Contracts\Selector\SelectorInterface;
use PHPHtmlParser\Discovery\ParserDiscovery;
use PHPHtmlParser\Discovery\SeekerDiscovery;
use PHPHtmlParser\Dom\AbstractNode;
use PHPHtmlParser\Dom\Collection;
use PHPHtmlParser\DTO\Selector\ParsedSelectorCollectionDTO;
use PHPHtmlParser\DTO\Selector\RuleDTO;
use PHPHtmlParser\Exceptions\ChildNotFoundException;

/**
 * Class Selector.
 */
class Selector implements SelectorInterface
{
    /**
     * @var ParsedSelectorCollectionDTO
     */
    private $ParsedSelectorCollectionDTO;

    /**
     * @var bool
     */
    private $depthFirst = false;

    /**
     * @var SeekerInterface
     */
    private $seeker;

    /**
     * Constructs with the selector string.
     */
    public function __construct(string $selector, ?ParserInterface $parser = null, ?SeekerInterface $seeker = null)
    {
        if ($parser == null) {
            $parser = ParserDiscovery::find();
        }
        if ($seeker == null) {
            $seeker = SeekerDiscovery::find();
        }

        $this->ParsedSelectorCollectionDTO = $parser->parseSelectorString($selector);
        $this->seeker = $seeker;
    }

    /**
     * Returns the selectors that where found in __construct.
     *
     * @return array
     */
    public function getParsedSelectorCollectionDTO(): ParsedSelectorCollectionDTO
    {
        return $this->ParsedSelectorCollectionDTO;
    }

    public function setDepthFirstFind(bool $status): void
    {
        $this->depthFirst = $status;
    }

    /**
     * Attempts to find the selectors starting from the given
     * node object.
     *
     * @throws ChildNotFoundException
     */
    public function find(AbstractNode $node): Collection
    {
        $results = new Collection();
        foreach ($this->ParsedSelectorCollectionDTO->getParsedSelectorDTO() as $selector) {
            $nodes = [$node];
            if (\count($selector->getRules()) == 0) {
                continue;
            }

            $options = [];
            foreach ($selector->getRules() as $rule) {
                if ($rule->isAlterNext()) {
                    $options[] = $this->alterNext($rule);
                    continue;
                }
                $nodes = $this->seeker->seek($nodes, $rule, $options, $this->depthFirst);
                // clear the options
                $options = [];
            }

            // this is the final set of nodes
            foreach ($nodes as $result) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Attempts to figure out what the alteration will be for
     * the next element.
     */
    private function alterNext(RuleDTO $rule): array
    {
        $options = [];
        if ($rule->getTag() == '>') {
            $options['checkGrandChildren'] = false;
        }

        return $options;
    }
}
