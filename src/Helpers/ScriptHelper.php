<?php

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Helpers;

use Symfony\Component\HttpFoundation\Response;

/**
 * Temporary class to store different states.
 *
 * Class ScriptHelper
 * @package NextDom\Helpers
 */
class ScriptHelper
{
    /**
     * @throws \Exception
     */
    public static function cliOrCrash()
    {
        if (php_sapi_name() != 'cli' || isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['argc'])) {
            header("Statut: 404 Page non trouvée");
            header('HTTP/1.0 404 Not Found');
            $_SERVER['REDIRECT_STATUS'] = Response::HTTP_NOT_FOUND;
            echo '<h1>' . __('core.error-404') . '</h1>';
            exit();
        }
    }

    public static function parseArgumentsToGET()
    {
        global $argv;
        if (isset($argv)) {
            foreach ($argv as $arg) {
                $argList = explode('=', $arg);
                if (isset($argList[0]) && isset($argList[1])) {
                    $_GET[$argList[0]] = $argList[1];
                }
            }
        }
    }
}
