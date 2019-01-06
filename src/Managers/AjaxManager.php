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

namespace NextDom\Managers;

class AjaxManager {
    /**
     * Init ajax communication
     *
     * @param bool $checkToken
     */
    public static function init($checkToken = true) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        if ($checkToken && init('nextdom_token') != self::getToken()) {
            self::error(__('Token d\'accès invalide'));
        }
    }

    /**
     * Get current NextDom token stored in session
     *
     * @return string NextDom token
     */
    public static function getToken() {
        if (session_status() == PHP_SESSION_NONE) {
            @session_start();
            @session_write_close();
        }
        if (!isset($_SESSION['nextdom_token'])) {
            @session_start();
            $_SESSION['nextdom_token'] = ConfigManager::genKey();
            @session_write_close();
        }
        return $_SESSION['nextdom_token'];
    }

    /**
     * Send answer
     *
     * @param string $answer Answer to send
     */
    public static function success($answer = '') {
        echo self::getResponse($answer);
        die();
    }

    /**
     * Send error
     *
     * @param string $errorData Error description
     * @param int $errorCode Error code
     */
    public static function error($errorData = '', $errorCode = 0) {
        echo self::getResponse($errorData, $errorCode);
        die();
    }

    /**
     * Convert data to JSON response
     *
     * @param string $data Data to convert
     * @param null $errorCode Error code
     * @return mixed
     */
    public static function getResponse($data = '', $errorCode = null) {
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
