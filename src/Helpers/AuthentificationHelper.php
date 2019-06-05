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

/* This file is part of NextDom.
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

use NextDom\Exceptions\CoreException;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UserManager;

/**
 * Class AuthentificationHelper
 * @package NextDom\Helpers
 */
class AuthentificationHelper
{
    /**
     * @var bool Status of the user connection
     */
    private static $connectedState = false;

    /**
     * @var bool Status of the user login as administrator
     */
    private static $connectedAdminState = false;
    /**
     * @var bool Recovery mode status
     */
    private static $rescueMode = false;

    /**
     * @throws \Exception
     */
    public static function init()
    {
        $allowRemoteUser = ConfigManager::byKey('session_lifetime');
        SessionHelper::startSession();
        $_SESSION['ip'] = NetworkHelper::getClientIp();
        if (!headers_sent()) {
            setcookie('sess_id', session_id(), time() + 24 * 3600, "/", '', false, true);
        }
        session_write_close();
        if (UserManager::isBanned()) {
            header("Statut: 403 Forbidden");
            header('HTTP/1.1 403 Forbidden');
            $_SERVER['REDIRECT_STATUS'] = 403;
            echo '<p>' . __('403 Access Forbidden') . '</p>';
            echo '<p>' . __('Votre accès a été verrouillé pour votre sécurité ') . '</p>';
            die();
        }

        if (!self::isConnected() && isset($_COOKIE['registerDevice'])) {
            if (self::loginByHash($_COOKIE['registerDevice'])) {
                setcookie('registerDevice', $_COOKIE['registerDevice'], time() + 365 * 24 * 3600, "/", '', false, true);
                if (isset($_COOKIE['nextdom_token'])) {
                    @session_start();
                    $_SESSION['nextdom_token'] = $_COOKIE['nextdom_token'];
                    @session_write_close();
                }
            } else {
                setcookie('registerDevice', '', time() - 3600, "/", '', false, true);
            }
        }

        if (!self::isConnected() && $allowRemoteUser == 1) {
            $user = UserManager::byLogin($_SERVER['REMOTE_USER']);
            if (is_object($user) && $user->getEnable() == 1) {
                @session_start();
                UserManager::storeUserInSession($user);
                @session_write_close();
                LogHelper::add('connection', 'info', __('Connexion de l\'utilisateur par REMOTE_USER : ') . $user->getLogin());
            }
        }

        if (!self::isConnected() && Utils::init('auth') != '') {
            self::loginByHash(Utils::init('auth'));
        }

        if (Utils::init('logout') == 1) {
            self::logout();
            Utils::redirect('index.php');
            die();
        }

        self::$connectedState = AuthentificationHelper::isConnectedWithRights();
        self::$connectedAdminState = AuthentificationHelper::isConnectedWithRights('admin');
        if (Utils::init('rescue', 0) == 1) {
            self::$rescueMode = true;
        }
    }

    /**
     * Get the status of the user login
     * @return bool Status of the user connection
     */
    public static function isConnected(): bool
    {
        return self::$connectedState;
    }

    /**
     * @param $_key
     * @return bool
     * @throws \Exception
     */
    public static function loginByHash($_key)
    {
        $key = explode('-', $_key);
        $user = UserManager::byHash($key[0]);
        if (!is_object($user) || $user->getEnable() == 0) {
            UserManager::failedLogin();
            sleep(5);
            return false;
        }
        if ($user->getOptions('localOnly', 0) == 1 && NetworkHelper::getUserLocation() != 'internal') {
            UserManager::failedLogin();
            sleep(5);
            return false;
        }
        if (!isset($key[1])) {
            UserManager::failedLogin();
            sleep(5);
            return false;
        }
        $registerDevice = $user->getOptions('registerDevice', array());
        if (!isset($registerDevice[Utils::sha512($key[1])])) {
            UserManager::failedLogin();
            sleep(5);
            return false;
        }
        @session_start();
        UserManager::storeUserInSession($user);
        @session_write_close();
        $registerDevice = UserManager::getStoredUser()->getOptions('registerDevice', array());
        if (!is_array($registerDevice)) {
            $registerDevice = array();
        }
        $registerDevice[Utils::sha512($key[1])] = array();
        $registerDevice[Utils::sha512($key[1])]['datetime'] = date('Y-m-d H:i:s');
        $registerDevice[Utils::sha512($key[1])]['ip'] = NetworkHelper::getClientIp();
        $registerDevice[Utils::sha512($key[1])]['session_id'] = session_id();
        @session_start();
        UserManager::getStoredUser()->setOptions('registerDevice', $registerDevice);
        UserManager::getStoredUser()->save();
        @session_write_close();
        if (!isset($_COOKIE['nextdom_token'])) {
            setcookie('nextdom_token', AjaxHelper::getToken(), time() + 365 * 24 * 3600, "/", '', false, true);
        }
        LogHelper::add('connection', 'info', __('Connexion de l\'utilisateur par clef : ') . $user->getLogin());
        return true;
    }

    /**
     *
     */
    public static function logout()
    {
        @session_start();
        setcookie('sess_id', '', time() - 3600, "/", '', false, true);
        setcookie('PHPSESSID', '', time() - 3600, "/", '', false, true);
        setcookie('registerDevice', '', time() - 3600, "/", '', false, true);
        setcookie('nextdom_token', '', time() - 3600, "/", '', false, true);
        session_unset();
        session_destroy();
    }

    /**
     * Test si l'utilisateur est connecté avec certains droits
     *
     * @param string $rights Droits à tester (admin)
     *
     * @return boolean True si l'utilisateur est connecté avec les droits demandés
     */
    public static function isConnectedWithRights(string $rights = ''): bool
    {
        $rightsKey = 'isConnect::' . $rights;
        $isSetSessionUser = UserManager::getStoredUser() !== null;
        $result = false;

        if ($isSetSessionUser && isset($GLOBALS[$rightsKey]) && $GLOBALS[$rightsKey]) {
            $result = $GLOBALS[$rightsKey];
        } else {

            if (session_status() == PHP_SESSION_DISABLED || !$isSetSessionUser) {
                $result = false;
            } elseif ($isSetSessionUser && is_object(UserManager::getStoredUser()) && UserManager::getStoredUser()->is_Connected()) {

                if ($rights !== '') {
                    if (UserManager::getStoredUser()->getProfils() == $rights) {
                        $result = true;
                    }
                } else {
                    $result = true;
                }
            }
            $GLOBALS[$rightsKey] = $result;
        }
        return $result;
    }

    /**
     * @param      $_login
     * @param      $_password
     * @param null $_twoFactor
     * @return bool
     * @throws \Exception
     */
    public static function login($_login, $_password, $_twoFactor = null)
    {
        $user = UserManager::connect($_login, $_password);
        if (!is_object($user) || $user->getEnable() == 0) {
            UserManager::failedLogin();
            sleep(5);
            return false;
        }
        if ($user->getOptions('localOnly', 0) == 1 && NetworkHelper::getUserLocation() != 'internal') {
            UserManager::failedLogin();
            sleep(5);
            return false;
        }
        if (NetworkHelper::getUserLocation() != 'internal' && $user->getOptions('twoFactorAuthentification', 0) == 1 && $user->getOptions('twoFactorAuthentificationSecret') != '') {
            if (trim($_twoFactor) == '' || $_twoFactor === null || !$user->validateTwoFactorCode($_twoFactor)) {
                UserManager::failedLogin();
                sleep(5);
                return false;
            }
        }
        @session_start();
        UserManager::storeUserInSession($user);
        @session_write_close();
        LogHelper::add('connection', 'info', __('Connexion de l\'utilisateur : ') . $_login);
        return true;
    }

    /**
     * Test if the user is logged in and throws an exception if this is not the case.
     * @return bool
     * @throws CoreException
     */
    public static function isConnectedOrFail()
    {
        if (!self::$connectedState) {
            throw new CoreException(__('core.error-401'), 401);
        }
        return self::isConnected();
    }

    /**
     * @abstract Test if user is connected with admins right or throw CoreException if not.
     * @return bool
     * @throws CoreException
     */
    public static function isConnectedAsAdminOrFail()
    {
        if (!self::$connectedAdminState) {
            throw new CoreException(__('core.error-401'), 401);
        }
        return self::isConnectedAsAdmin();
    }

    /**
     * Get the login status of the user as an administrator
     * @return bool Status of the user login as administrator
     */
    public static function isConnectedAsAdmin(): bool
    {
        return self::$connectedAdminState;
    }

    /**
     * Get the status of the recovery mode
     * @return bool Recovery mode status
     */
    public static function isRescueMode(): bool
    {
        return self::$rescueMode;
    }

    /**
     * @return bool
     */
    public static function isInDeveloperMode(): bool
    {
        return ConfigManager::getDefaultConfiguration()['core']['developer::mode'] == '1';
    }
}
