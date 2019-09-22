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

namespace NextDom\Ajax;

use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SessionHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\UserManager;
use NextDom\Model\Entity\User;

/**
 * Class UserAjax
 * @package NextDom\Ajax
 */
class UserAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::NOTHING;
    protected $MUST_BE_CONNECTED = false;
    protected $CHECK_AJAX_TOKEN = false;

    public function useTwoFactorAuthentification()
    {
        $user = UserManager::byLogin(Utils::init('login'));
        if (!is_object($user)) {
            AjaxHelper::success(0);
        }
        if (NetworkHelper::getUserLocation() == 'internal') {
            AjaxHelper::success(0);
        }
        AjaxHelper::success($user->getOptions('twoFactorAuthentification', 0));
    }

    public function login()
    {
        if (!file_exists(session_save_path())) {
            mkdir(session_save_path());
        }
        if (!AuthentificationHelper::isConnected()) {
            if (ConfigManager::byKey('sso:allowRemoteUser') == 1) {
                $user = UserManager::byLogin($_SERVER['REMOTE_USER']);
                if (is_object($user) && $user->isEnabled()) {
                    @session_start();
                    $_SESSION['user'] = $user;
                    @session_write_close();
                    LogHelper::add('connection', 'info', __('Connexion de l\'utilisateur par REMOTE_USER : ') . $user->getLogin());
                }
            }
            if (!AuthentificationHelper::login(Utils::init('username'), Utils::init('password'), Utils::init('twoFactorCode'))) {
                throw new CoreException('Mot de passe ou nom d\'utilisateur incorrect');
            }
        }

        if (Utils::init('storeConnection') == 1) {
            $rdk = ConfigManager::genKey();
            $registerDevice = $_SESSION['user']->getOptions('registerDevice', array());
            if (!is_array($registerDevice)) {
                $registerDevice = array();
            }
            $registerDevice[sha512($rdk)] = array();
            $registerDevice[sha512($rdk)]['datetime'] = date('Y-m-d H:i:s');
            $registerDevice[sha512($rdk)]['ip'] = NetworkHelper::getClientIp();
            $registerDevice[sha512($rdk)]['session_id'] = session_id();
            setcookie('registerDevice', $_SESSION['user']->getHash() . '-' . $rdk, time() + 365 * 24 * 3600, "/", '', false, true);
            @session_start();
            $_SESSION['user']->setOptions('registerDevice', $registerDevice);
            $_SESSION['user']->save();
            @session_write_close();
            if (!isset($_COOKIE['nextdom_token'])) {
                setcookie('nextdom_token', AjaxHelper::getToken(), time() + 365 * 24 * 3600, "/", '', false, true);
            }
        }
        AjaxHelper::success();
    }

    public function getApikey()
    {
        if (!AuthentificationHelper::login(Utils::init('username'), Utils::init('password'), Utils::init('twoFactorCode'))) {
            throw new CoreException('Mot de passe ou nom d\'utilisateur incorrect');
        }
        AjaxHelper::success(UserManager::getStoredUser()->getHash());
    }

    public function validateTwoFactorCode()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedOrFail();
        AjaxHelper::init();
        $currentUser = UserManager::getStoredUser();
        if ($currentUser !== null) {
            @session_start();
            $currentUser->refresh();
            $result = $currentUser->validateTwoFactorCode(Utils::init('code'));
            if ($result && Utils::init('enableTwoFactorAuthentification') == 1) {
                $currentUser->setOptions('twoFactorAuthentification', 1);
                $currentUser->save();
            }
            @session_write_close();
            AjaxHelper::success($result);
        }
        AjaxHelper::error('Problème d\'utilisateur');
    }

    public function removeTwoFactorCode()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init();
        $user = UserManager::byId(Utils::init('id'));
        if (!is_object($user)) {
            throw new CoreException('User ID inconnu');
        }
        $user->setOptions('twoFactorAuthentification', 0);
        $user->save();
        AjaxHelper::success(true);
    }

    public function isConnect()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedOrFail();
        AjaxHelper::init();
        AjaxHelper::success();
    }

    public function refresh()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedOrFail();
        AjaxHelper::init();
        @session_start();
        UserManager::getStoredUser()->refresh();
        @session_write_close();
        AjaxHelper::success();
    }

    public function logout()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedOrFail();
        AjaxHelper::init();
        AuthentificationHelper::logout();
        AjaxHelper::success();
    }

    public function all()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init();
        $users = array();
        foreach (UserManager::all() as $user) {
            $user_info = Utils::o2a($user);
            $users[] = $user_info;
        }
        AjaxHelper::success($users);
    }

    public function save()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init();
        $users = json_decode(Utils::init('users'), true);
        $user = null;
        foreach ($users as &$user_json) {
            if (isset($user_json['id'])) {
                $user = UserManager::byId($user_json['id']);
            }
            if (!is_object($user)) {
                if (ConfigManager::byKey('ldap::enable') == '1') {
                    throw new CoreException(__('Vous devez désactiver l\'authentification LDAP pour pouvoir ajouter un utilisateur'));
                }
                $user = new User();
            }
            Utils::a2o($user, $user_json);
            $user->save();
        }
        @session_start();
        UserManager::getStoredUser()->refresh();
        @session_write_close();
        AjaxHelper::success();
    }

    public function remove()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init();
        if (ConfigManager::byKey('ldap::enable') == '1') {
            throw new CoreException(__('Vous devez désactiver l\'authentification LDAP pour pouvoir supprimer un utilisateur'));
        }
        if (Utils::init('id') == UserManager::getStoredUser()->getId()) {
            throw new CoreException(__('Vous ne pouvez pas supprimer le compte avec lequel vous êtes connecté'));
        }
        $user = UserManager::byId(Utils::init('id'));
        if (!is_object($user)) {
            throw new CoreException('User ID inconnu');
        }
        $user->remove();
        AjaxHelper::success();
    }

    public function saveProfils()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedOrFail();
        AjaxHelper::init();
        $currentUser = UserManager::getStoredUser();
        $user_json = NextDomHelper::fromHumanReadable(json_decode(Utils::init('profils'), true));
        if (isset($user_json['id']) && $user_json['id'] != $currentUser->getId()) {
            throw new CoreException('401 - Accès non autorisé');
        }
        @session_start();
        $currentUser->refresh();
        $login = $currentUser->getLogin();
        $rights = $currentUser->getRights();
        Utils::a2o($currentUser, $user_json);
        foreach ($rights as $right => $value) {
            $currentUser->setRights($right, $value);
        }
        $currentUser->setLogin($login);
        $currentUser->save();
        @session_write_close();
        EqLogicManager::clearCacheWidget();
        AjaxHelper::success();
    }

    public function get()
    {
        AjaxHelper::init();
        AjaxHelper::success(NextDomHelper::toHumanReadable(Utils::o2a($_SESSION['user'])));
    }

    public function removeRegisterDevice()
    {
        AuthentificationHelper::init();
        AjaxHelper::init();
        $user = null;
        if (Utils::init('key') == '' && Utils::init('user_id') == '') {
            AuthentificationHelper::isConnectedAsAdminOrFail();
            foreach (UserManager::all() as $user) {
                if ($user->getId() == UserManager::getStoredUser()->getId()) {
                    UserManager::getStoredUser()->setOptions('registerDevice', array());
                    UserManager::getStoredUser()->save();
                } else {
                    $user->setOptions('registerDevice', array());
                    $user->save();
                }
            }
            AjaxHelper::success();
        }
        if (Utils::init('user_id') != '') {
            AuthentificationHelper::isConnectedAsAdminOrFail();
            $user = UserManager::byId(Utils::init('user_id'));
            if (!is_object($user)) {
                throw new CoreException(__('Utilisateur non trouvé : ') . Utils::init('user_id'));
            }
            $registerDevice = $user->getOptions('registerDevice', array());
        } else {
            $registerDevice = UserManager::getStoredUser()->getOptions('registerDevice', array());
        }

        if (Utils::init('key') == '') {
            $registerDevice = array();
        } elseif (isset($registerDevice[init('key')])) {
            unset($registerDevice[init('key')]);
        }
        if (Utils::init('user_id') != '') {
            $user->setOptions('registerDevice', $registerDevice);
            $user->save();
        } else {
            @session_start();
            UserManager::getStoredUser()->setOptions('registerDevice', $registerDevice);
            UserManager::getStoredUser()->save();
            @session_write_close();
        }
        AjaxHelper::success();
    }

    public function deleteSession()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedOrFail();
        AjaxHelper::init();
        $sessions = SessionHelper::getSessionsList();
        if (isset($sessions[init('id')])) {
            $user = UserManager::byId($sessions[init('id')]['user_id']);
            if (is_object($user)) {
                $registerDevice = $user->getOptions('registerDevice', array());
                foreach ($user->getOptions('registerDevice', array()) as $key => $value) {
                    if ($value['session_id'] == Utils::init('id')) {
                        unset($registerDevice[$key]);
                    }
                }
                $user->setOptions('registerDevice', $registerDevice);
                $user->save();
            }
        }
        SessionHelper::deleteSession(Utils::init('id'));
        AjaxHelper::success();
    }

    public function testLdapConnection()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init();
        $connection = UserManager::connectToLDAP();
        if ($connection === false) {
            throw new CoreException();
        }
        AjaxHelper::success();
    }

    public function removeBanIp()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init();
        UserManager::removeBanIp();
        AjaxHelper::success();
    }

    public function supportAccess()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init();
        UserManager::supportAccess(Utils::init('enable'));
        AjaxHelper::success();
    }
}