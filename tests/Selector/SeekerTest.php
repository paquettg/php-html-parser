<?php

declare(strict_types=1);

use PHPHtmlParser\DTO\Selector\RuleDTO;
use PHPHtmlParser\Selector\Seeker;
use PHPUnit\Framework\TestCase;

class SeekerTest extends TestCase
{
    public function testSeekReturnEmptyArray()
    {
        $ruleDTO = RuleDTO::makeFromPrimitives(
            'tag',
            '=',
            null,
            null,
            false,
            false
        );
        $seeker = new Seeker();
        $results = $seeker->seek([], $ruleDTO, []);
        $this->assertCount(0, $results);
    }
}
