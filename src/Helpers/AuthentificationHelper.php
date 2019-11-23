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

use NextDom\Enums\DateFormat;
use NextDom\Enums\LogTarget;
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
     * @var array Checked rights cache
     */
    private static $rightsCache = [];

    /**
     * @throws \Exception
     */
    public static function init()
    {
        // Init session
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
            require(NEXTDOM_ROOT . '/public/403.html');
            die();
        }

        // Autologin on register device
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

        // Login with user/password
        if (!self::isConnected() && $allowRemoteUser == 1 && isset($_SERVER['REMOTE_USER'])) {
            $user = UserManager::byLogin($_SERVER['REMOTE_USER']);
            if (is_object($user) && $user->getEnable() == 1) {
                @session_start();
                UserManager::storeUserInSession($user);
                @session_write_close();
                LogHelper::addInfo(LogTarget::CONNECTION, __('Connexion de l\'utilisateur par REMOTE_USER : ') . $user->getLogin());
            }
        }

        // Login with hash
        if (!self::isConnected() && Utils::init('auth') != '') {
            self::loginByHash(Utils::init('auth'));
        }

        // Logout
        if (Utils::init('logout') == 1) {
            self::logout();
            Utils::redirect('index.php');
            die();
        }

        self::$connectedState = AuthentificationHelper::isConnectedWithRights();
        self::$connectedAdminState = AuthentificationHelper::isConnectedWithRights('admin');
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
     * @param $rawHashs
     * @return bool
     * @throws \Exception
     */
    public static function loginByHash($rawHashs)
    {
        $hashs = explode('-', $rawHashs);
        // Malformed hashs
        if (count($hashs) < 2) {
            return false;
        }
        // Get user by hash
        $user = UserManager::byHash($hashs[0]);
        if (!is_object($user) || $user->getEnable() == 0) {
            UserManager::failedLogin();
            return false;
        }
        // Check if user is limited
        if ($user->getOptions('localOnly', 0) == 1 && NetworkHelper::getUserLocation() != 'internal') {
            UserManager::failedLogin();
            return false;
        }
        // Bad hashs
        if (!isset($hashs[1])) {
            UserManager::failedLogin();
            return false;
        }
        // Test registered device
        $registeredDevices = $user->getOptions('registerDevice', []);
        $currentDeviceHash = Utils::sha512($hashs[1]);
        if (!isset($registeredDevices[$currentDeviceHash])) {
            UserManager::failedLogin();
            return false;
        }
        @session_start();
        UserManager::storeUserInSession($user);
        @session_write_close();
        if (!is_array($registeredDevices)) {
            $registeredDevices = [];
        }
        $registeredDevices[$currentDeviceHash] = [];
        $registeredDevices[$currentDeviceHash]['datetime'] = date(DateFormat::FULL);
        $registeredDevices[$currentDeviceHash]['ip'] = NetworkHelper::getClientIp();
        $registeredDevices[$currentDeviceHash]['session_id'] = session_id();
        @session_start();
        UserManager::getStoredUser()->setOptions('registerDevice', $registeredDevices);
        UserManager::getStoredUser()->save();
        @session_write_close();
        if (!isset($_COOKIE['nextdom_token'])) {
            setcookie('nextdom_token', AjaxHelper::getToken(), time() + 365 * 24 * 3600, "/", '', false, true);
        }
        LogHelper::addInfo(LogTarget::CONNECTION, __('Connexion de l\'utilisateur par clé : ') . $user->getLogin());
        return true;
    }

    /**
     * Disconnect user
     */
    public static function logout()
    {
        $expirationTime = time() - 3600;
        @session_start();
        setcookie('sess_id', '', $expirationTime, '/', '', false, true);
        setcookie('PHPSESSID', '', $expirationTime, '/', '', false, true);
        setcookie('registerDevice', '', $expirationTime, '/', '', false, true);
        setcookie('nextdom_token', '', $expirationTime, '/', '', false, true);
        session_unset();
        session_destroy();
    }

    /**
     * Test if user have some rights
     *
     * @param string $rights Droits à tester (admin)
     *
     * @return boolean True si l'utilisateur est connecté avec les droits demandés
     */
    public static function isConnectedWithRights(string $rights = ''): bool
    {
        $rightsKey = 'isConnect::' . $rights;
        $user = UserManager::getStoredUser();

        $result = false;

        if (!is_object($user) || session_status() == PHP_SESSION_DISABLED) {
            $result = false;
        } else {
            // Check cache
            if (isset(self::$rightsCache[$rightsKey]) && self::$rightsCache[$rightsKey]) {
                $result = self::$rightsCache[$rightsKey];
            } elseif ($user->isConnected()) {
                // Check specific rights
                if (!empty($rights)) {
                    if (UserManager::getStoredUser()->getProfils() == $rights) {
                        $result = true;
                    }
                } else {
                    // No specific rights
                    $result = true;
                }
            }
            // Store in cache
            self::$rightsCache[$rightsKey] = $result;
        }
        return $result;
    }

    /**
     * @param string $login
     * @param string $password
     * @param null $twoFactorCode
     * @return bool
     * @throws \Exception
     */
    public static function login($login, $password, $twoFactorCode = null): bool
    {
        $user = UserManager::connect($login, $password);
        if (!is_object($user) || !$user->isEnabled()) {
            UserManager::failedLogin();
            return false;
        }
        if ($user->getOptions('localOnly', 0) == 1 && NetworkHelper::getUserLocation() != 'internal') {
            UserManager::failedLogin();
            return false;
        }
        if (NetworkHelper::getUserLocation() != 'internal' && $user->getOptions('twoFactorAuthentification', 0) == 1 && $user->getOptions('twoFactorAuthentificationSecret') != '') {
            if (trim($twoFactorCode) == '' || $twoFactorCode === null || !$user->validateTwoFactorCode($twoFactorCode)) {
                UserManager::failedLogin();
                return false;
            }
        }
        @session_start();
        UserManager::storeUserInSession($user);
        @session_write_close();
        LogHelper::addInfo(LogTarget::CONNECTION, __('Connexion de l\'utilisateur : ') . $login);
        return true;
    }

    /**
     * Test if the user is logged in and throws an exception if this is not the case.
     * @return bool
     * @throws CoreException
     */
    public static function isConnectedOrFail(): bool
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
    public static function isConnectedAsAdminOrFail(): bool
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
     * @return bool
     */
    public static function isInDeveloperMode(): bool
    {
        return ConfigManager::getDefaultConfiguration()['core']['developer::mode'] == '1';
    }
}
