<?php

declare(strict_types=1);

use PHPHtmlParser\DTO\Selector\RuleDTO;
use PHPHtmlParser\Selector\Seeker;
use PHPUnit\Framework\TestCase;

class SeekerTest extends TestCase
{
    public function testSeekReturnEmptyArray()
    {
        $ruleDTO = new RuleDTO([
            'tag'       => 'tag',
            'key'       => 1,
            'value'     => null,
            'operator'  => null,
            'noKey'     => false,
            'alterNext' => false,
        ]);
        $seeker = new Seeker();
        $results = $seeker->seek([], $ruleDTO, []);
        $this->assertCount(0, $results);
    }
}
