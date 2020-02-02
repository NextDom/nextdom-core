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

namespace NextDom\Helpers;

use NextDom\Managers\ConfigManager;

/**
 * Class AjaxHelper
 * @package NextDom\Helpers
 */
class AjaxHelper
{
    /**
     * @var bool Answer state
     */
    private $answerSended = false;

    /**
     * Init ajax communication
     *
     * @throws \Exception
     */
    public function __construct()
    {
        // Prepare ajax response
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
    }

    /**
     * Check ajax token validity
     *
     * @throws \Exception
     */
    public function checkToken()
    {
        if (Utils::init('nextdom_token') != self::getToken()) {
            self::error(__('Token d\'accès invalide'));
        }
    }

    /**
     * Get current NextDom token stored in session
     *
     * @return string NextDom token
     * @throws \Exception
     */
    public static function getToken()
    {
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
     * Send error
     *
     * @param string $errorData Error description
     * @param int $errorCode Error code
     */
    public function error($errorData = '', $errorCode = 0)
    {
        if (!$this->answerSended) {
            echo $this->getResponse($errorData, $errorCode);
            $this->answerSended = true;
        }
    }

    /**
     * Convert data to JSON response
     *
     * @param string $data Data to convert
     * @param null $errorCode Error code
     * @return mixed
     */
    public function getResponse($data = '', $errorCode = null)
    {
        // @TODO: Tester l'incidence de l'ordre des résultat si result est inséré en dernier
        // et donc éviter la ligne en double
        $response = [];
        if ($errorCode === null) {
            $response['state'] = 'ok';
            $response['result'] = $data;
        } else {
            $response['state'] = 'error';
            $response['result'] = $data;
            $response['code'] = $errorCode;
        }
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Send answer
     *
     * @param string $answer Answer to send
     */
    public function success($answer = '')
    {
        if (!$this->answerSended) {
            echo $this->getResponse($answer);
            $this->answerSended = true;
        }
    }
}
