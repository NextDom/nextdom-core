<?php

use NextDom\Helpers\AuthentificationHelper;

require_once(__DIR__ . '/../../../src/core.php');

abstract class BaseAjaxTest extends PHPUnit_Framework_TestCase
{
    /**
     * Remove all params in $_GET arrays
     */
    protected function cleanGetParams()
    {
        foreach (array_keys($_GET) as $getKey) {
            unset($_GET[$getKey]);
        }
    }

    protected function connectAdAdmin() {
        AuthentificationHelper::login('admin', 'nextdom-test');
        AuthentificationHelper::init();
    }
}