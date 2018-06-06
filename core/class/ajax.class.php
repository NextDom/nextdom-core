<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once __DIR__ . '/../../core/php/core.inc.php';

class ajax
{

    /**
     * @param bool $checkToken
     */
    public static function init($checkToken = true)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        if ($checkToken && init('nextdom_token') != self::getToken()) {
            self::error(__('Token d\'accès invalide', __FILE__));
        }
    }

    /**
     * @return string
     */
    public static function getToken()
    {
        if (session_status() == PHP_SESSION_NONE) {
            @session_start();
            @session_write_close();
        }
        if (!isset($_SESSION['nextdom_token'])) {
            @session_start();
            $_SESSION['nextdom_token'] = config::genKey();
            @session_write_close();
        }
        return $_SESSION['nextdom_token'];
    }

    /**
     * @param string $data
     */
    public static function success($data = '')
    {
        echo self::getResponse($data);
        die();
    }

    /**
     * @param string $data
     * @param int $errorCode
     */
    public static function error($data = '', $_errorCode = 0)
    {
        echo self::getResponse($data, $_errorCode);
        die();
    }

    /**
     * @param string $data
     * @param null $errorCode
     * @return string
     */
    public static function getResponse($data = '', $errorCode = null)
    {
        $isError = !(null === $errorCode);
        $return = array(
            'state' => $isError ? 'error' : 'ok',
            'result' => $data,
        );
        if ($isError) {
            $return['code'] = $errorCode;
        }
        return json_encode($return, JSON_UNESCAPED_UNICODE);
    }
}
