<?php

use NextDom\Helpers\AuthentificationHelper;

require_once(__DIR__ . '/../../../src/core.php');

abstract class BaseAjaxTest extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        exec('bash tests/load_fixtures.sh --reset');
    }

    /**
     * Remove all params in $_GET arrays
     */
    protected function cleanGetParams()
    {
        foreach (array_keys($_GET) as $getKey) {
            unset($_GET[$getKey]);
        }
    }

    protected function connectAsAdmin() {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
    }
}