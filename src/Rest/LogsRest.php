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

namespace NextDom\Rest;

use NextDom\Helpers\LogHelper;

/**
 * Class LogsRest
 *
 * @package NextDom\Rest
 */
class LogsRest
{
    /**
     * Get list of logs
     *
     * @return array List of logs file
     *
     * @throws \Exception
     */
    public static function getList()
    {
        return LogHelper::getAllLogFileList();
    }

    /**
     * Get the content of a log file
     * For subfolder, / must be replace by a triple underscore (___)
     *
     * @param string $logFile Target log file
     *
     * @return array|bool Log content of false
     *
     * @throws \Exception
     */
    public static function get(string $logFile)
    {
        $logFile = str_replace('___', '/', $logFile);
        return LogHelper::get($logFile);
    }
}
