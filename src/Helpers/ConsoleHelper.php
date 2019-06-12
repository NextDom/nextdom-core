<?php
/**
 * Created by PhpStorm.
 * User: slobberbone
 * Date: 24/04/19
 * Time: 22:25
 */

namespace NextDom\Helpers;


use phpDocumentor\Reflection\Types\Boolean;

class ConsoleHelper
{

    /**
     * Show title
     *
     * @param string $title Title to show
     * @param Boolean $ending true if end tag
     */
    public static function title(string $title, Boolean $ending)
    {
        if($ending){
            printf("[ / $title ]\n");
        } else {
            printf("[ $title ]\n");
        }
    }

    /**
     * Show subtitle
     *
     * @param string $subTitle Subtitle to show
     */
    public static function subTitle(string $subTitle)
    {
        printf("*************** $subTitle ***************\n");
    }

    /**
     * Show step information
     * @param string $stepTitle Step title to show
     */
    public static function step(string $stepTitle)
    {
        printf("$stepTitle... ");
    }
    /**
     * Show process information
     * @param string $processTitle Process title to show
     */
    public static function process(string $processTitle)
    {
        printf("...$processTitle\n");
    }

    /**
     * Show ok message
     */
    public static function enter()
    {
        printf("\n");
    }
    /**
     * Show ok message
     */
    public static function ok()
    {
        printf(" OK\n");
    }

    /**
     * Show not ok message
     */
    public static function nok()
    {
        printf(" Failure\n");
    }

    /**
     * Show error message
     *
     * @param CoreException|Exception $exceptionData Data of the exception
     */
    public static function error($exceptionData)
    {
        printf(">> *** ERROR *** " . Utils::br2nl($exceptionData->getMessage()) . "\n");
        printf(">> *** TRACE *** " . Utils::br2nl($exceptionData->getTrace() ) . "\n");
    }
}