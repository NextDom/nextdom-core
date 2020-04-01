<?php

use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class BaseTest extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        exec('bash tests/load_fixtures.sh --reset');
    }
}
