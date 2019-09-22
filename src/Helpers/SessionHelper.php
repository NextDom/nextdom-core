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

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Helpers;

use NextDom\Exceptions\CoreException;
use NextDom\Managers\ConfigManager;

/**
 * Class SessionHelper
 * @package NextDom\Helpers
 */
class SessionHelper
{
    /**
     * @return array
     * @throws \Exception
     */
    public static function getSessionsList()
    {
        $result = array();
        try {
            $sessions = explode("\n", \com_shell::execute(SystemHelper::getCmdSudo() . ' ls ' . session_save_path()));
            foreach ($sessions as $session) {
                $data = \com_shell::execute(SystemHelper::getCmdSudo() . ' cat ' . session_save_path() . '/' . $session);
                if ($data == '') {
                    continue;
                }
                $data_session = self::decodeSessionData($data);
                if (!isset($data_session['user']) || !is_object($data_session['user'])) {
                    continue;
                }
                $session_id = str_replace('sess_', '', $session);
                $result[$session_id] = array(
                    'datetime' => date('Y-m-d H:i:s', \com_shell::execute(SystemHelper::getCmdSudo() . ' stat -c "%Y" ' . session_save_path() . '/' . $session)),
                );
                $result[$session_id]['login'] = $data_session['user']->getLogin();
                $result[$session_id]['user_id'] = $data_session['user']->getId();
                $result[$session_id]['ip'] = (isset($data_session['ip'])) ? $data_session['ip'] : '';
            }
        } catch (\Exception $e) {

        }
        return $result;
    }

    /**
     * @param $srcData
     * @return array
     * @throws CoreException
     */
    public static function decodeSessionData($srcData)
    {
        $resultData = array();
        $offset = 0;
        while ($offset < strlen($srcData)) {
            if (!strstr(substr($srcData, $offset), "|")) {
                throw new CoreException("invalid data, remaining: " . substr($srcData, $offset));
            }
            $pos = strpos($srcData, "|", $offset);
            $num = $pos - $offset;
            $varName = substr($srcData, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($srcData, $offset));
            $resultData[$varName] = $data;
            $offset += strlen(serialize($data));
        }
        return $resultData;
    }

    /**
     * @param int $sessionId
     */
    public static function deleteSession($sessionId)
    {
        $currentSessionId = session_id();
        if (session_status() !== PHP_SESSION_NONE) {
            session_start();
            session_id($sessionId);
            session_unset();
            session_destroy();
            session_id($currentSessionId);
            session_write_close();
        }
    }

    /**
     * Configure and start session if not already started
     *
     * @throws \Exception
     */
    public static function startSession() {
        if(session_status() == PHP_SESSION_NONE) {
            $sessionLifetime = ConfigManager::byKey('session_lifetime');
            if (!is_numeric($sessionLifetime)) {
                $sessionLifetime = 24;
            }
            ini_set('session.gc_maxlifetime', $sessionLifetime * 3600);
            ini_set('session.use_cookies', 1);
            ini_set('session.cookie_httponly', 1);

            if (isset($_COOKIE['sess_id'])) {
                session_id($_COOKIE['sess_id']);
            }
            session_start();
        }
    }
}
