<?php
/**
 * Created by PhpStorm.
 * User: slobberbone
 * Date: 15/04/19
 * Time: 10:11
 */

namespace NextDom\Singletons;


class SingletonSample
{
    /**
     * @var instance this self instance
     */
    protected static $instance = null;

    /**
     * PHPInformation constructor.
     */
    protected function __construct()
    {
        //Nothing to construct
    }

    /**
     *
     */
    protected function __clone()
    {
        //Nothing to clone
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }
}