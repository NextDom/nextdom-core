<?php
/**
 * Created by PhpStorm.
 * User: slobberbone
 * Date: 24/04/19
 * Time: 22:25
 */

namespace NextDom\Helpers;


class ConsoleHelper
{

    /**
     * Show title
     *
     * @param string $title Title to show
     */
    public static function title(string $title)
    {
        echo "[ $title ]\n";
    }

    /**
     * Show subtitle
     *
     * @param string $subTitle Subtitle to show
     */
    public static function subTitle(string $subTitle)
    {
        echo "*************** $subTitle ***************\n";
    }

    /**
     * Show step information
     * @param string $stepTitle Step title to show
     */
    public static function step(string $stepTitle)
    {
        echo "$stepTitle... ";
    }
    /**
     * Show process information
     * @param string $processTitle Process title to show
     */
    public static function process(string $processTitle)
    {
        echo "...$processTitle\n";
    }

    /**
     * Show ok message
     */
    public static function enter()
    {
        echo "\n";
    }
    /**
     * Show ok message
     */
    public static function ok()
    {
        echo " OK\n";
    }

    /**
     * Show not ok message
     */
    public static function nok()
    {
        echo " NOK\n";
    }

    /**
     * Show error message
     *
     * @param CoreException|Exception $exceptionData Data of the exception
     */
    public static function showError($exceptionData)
    {
        echo "*** ERROR *** " . $exceptionData->getMessage() . "\n";
    }
}