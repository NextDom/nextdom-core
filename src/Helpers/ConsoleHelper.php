<?php
/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace NextDom\Helpers;


use NextDom\Exceptions\CoreException;

class ConsoleHelper
{

    /**
     * Show title
     *
     * @param string $title Title to show
     * @param bool $ending true if end tag
     */
    public static function title(string $title, $ending)
    {
        if ($ending) {
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
     * Show step information
     * @param string $stepTitle Step title to show
     */
    public static function stepLine(string $stepTitle)
    {
        printf("$stepTitle...\n");
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
     * @param CoreException|\Exception $exceptionData Data of the exception
     */
    public static function error($exceptionData)
    {
        printf(">> *** ERROR *** " . Utils::br2nl($exceptionData->getMessage()) . "\n");
        printf(">> *** TRACE *** " . Utils::br2nl($exceptionData->getTrace()) . "\n");
    }
}
