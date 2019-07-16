<?php
/**
 * Created by PhpStorm.
 * User: slobberbone
 * Date: 15/04/19
 * Time: 10:08
 */

namespace NextDom\Singletons;


/**
 * Class PHPInformation
 * @package NextDom\Singletons
 */
class PHPInformation
{
    /**
     * @var instance this PHPInformation instance
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

    /**
     * @param bool $completeTrace true if you want the complete trace
     * @return string
     */
    public function getCallingFunctionName($completeTrace=false)
    {
        $trace=debug_backtrace();
        if($completeTrace)
        {
            $str = '';
            foreach($trace as $caller)
            {
                $str .= " -- Called by {$caller['function']}";
                if (isset($caller['class']))
                    $str .= " From Class {$caller['class']}";
            }
        }
        else
        {
            $caller=$trace[2];
            $str = "Called by {$caller['function']}";
            if (isset($caller['class']))
                $str .= " From Class {$caller['class']}";
        }
        return $str;
    }
}