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

use NextDom\Com\ComShell;
use NextDom\Enums\AjaxParams;
use NextDom\Enums\DateFormat;
use NextDom\Enums\LogTarget;
use NextDom\Enums\UserOption;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SessionHelper;
use NextDom\Helpers\SystemHelper;
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
            $this->ajax->success(0);
        }
        if (NetworkHelper::getUserLocation() == 'internal') {
            $this->ajax->success(0);
        }
        $this->ajax->success($user->getOptions(UserOption::TWO_FACTOR_AUTH, 0));
    }

    public function login()
    {
        if (!file_exists(session_save_path())) {
            try {
                ComShell::execute(SystemHelper::getCmdSudo() . ' mkdir ' . session_save_path() . ';' . SystemHelper::getCmdSudo() . ' chmod 777 -R ' . session_save_path());
            } catch (CoreException $e) {

            }
        }
        try {
            if (ComShell::execute(SystemHelper::getCmdSudo() . ' ls ' . session_save_path() . ' | wc -l') > 500) {
                ComShell::execute(SystemHelper::getCmdSudo() . '/usr/lib/php/sessionclean');
            }
        } catch (CoreException $e) {
        }
        if (!AuthentificationHelper::isConnected()) {
            if (ConfigManager::byKey('sso:allowRemoteUser') == 1) {
                $user = UserManager::byLogin($_SERVER['REMOTE_USER']);
                if (is_object($user) && $user->isEnabled()) {
                    @session_start();
                    $_SESSION['user'] = $user;
                    @session_write_close();
                    LogHelper::addInfo(LogTarget::CONNECTION, __('Connexion de l\'utilisateur par REMOTE_USER : ') . $user->getLogin());
                }
            }
            if (!AuthentificationHelper::login(Utils::init('username'), Utils::init('password'), Utils::init('twoFactorCode'))) {
                throw new CoreException('Mot de passe ou nom d\'utilisateur incorrect');
            }
        }

        if (Utils::init('storeConnection') == 1) {
            $rdk = ConfigManager::genKey();
            $registerDevice = $_SESSION['user']->getOptions(UserOption::REGISTER_DEVICE, []);
            if (!is_array($registerDevice)) {
                $registerDevice = [];
            }
            $registerDevice[sha512($rdk)] = [];
            $registerDevice[sha512($rdk)]['datetime'] = date(DateFormat::FULL);
            $registerDevice[sha512($rdk)]['ip'] = NetworkHelper::getClientIp();
            $registerDevice[sha512($rdk)]['session_id'] = session_id();
            setcookie(UserOption::REGISTER_DEVICE, $_SESSION['user']->getHash() . '-' . $rdk, time() + 365 * 24 * 3600, "/", '', false, true);
            @session_start();
            $_SESSION['user']->setOptions(UserOption::REGISTER_DEVICE, $registerDevice);
            $_SESSION['user']->save();
            @session_write_close();
            if (!isset($_COOKIE['nextdom_token'])) {
                setcookie('nextdom_token', AjaxHelper::getToken(), time() + 365 * 24 * 3600, "/", '', false, true);
            }
        }
        $this->ajax->success();
    }

    public function getApikey()
    {
        if (!AuthentificationHelper::login(Utils::init('username'), Utils::init('password'), Utils::init('twoFactorCode'))) {
            throw new CoreException('Mot de passe ou nom d\'utilisateur incorrect');
        }
        $this->ajax->success(UserManager::getStoredUser()->getHash());
    }

    public function validateTwoFactorCode()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedOrFail();
        $currentUser = UserManager::getStoredUser();
        if ($currentUser !== null) {
            @session_start();
            $currentUser->refresh();
            $result = $currentUser->validateTwoFactorCode(Utils::init(AjaxParams::CODE));
            if ($result && Utils::init('enableTwoFactorAuthentification') == 1) {
                $currentUser->setOptions(UserOption::TWO_FACTOR_AUTH, 1);
                $currentUser->save();
            }
            @session_write_close();
            $this->ajax->success($result);
        }
        $this->ajax->error('Problème d\'utilisateur');
    }

    public function removeTwoFactorCode()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $user = UserManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($user)) {
            throw new CoreException('User ID inconnu');
        }
        $user->setOptions(UserOption::TWO_FACTOR_AUTH, 0);
        $user->save();
        $this->ajax->success(true);
    }

    public function isConnect()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedOrFail();
        $this->ajax->success();
    }

    public function refresh()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedOrFail();
        @session_start();
        UserManager::getStoredUser()->refresh();
        @session_write_close();
        $this->ajax->success();
    }

    public function logout()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedOrFail();
        AuthentificationHelper::logout();
        $this->ajax->success();
    }

    public function all()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $users = [];
        foreach (UserManager::all() as $user) {
            $user_info = Utils::o2a($user);
            $users[] = $user_info;
        }
        $this->ajax->success($users);
    }

    public function save()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
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
        $this->ajax->success();
    }

    public function remove()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        if (ConfigManager::byKey('ldap::enable') == '1') {
            throw new CoreException(__('Vous devez désactiver l\'authentification LDAP pour pouvoir supprimer un utilisateur'));
        }
        if (Utils::initInt(AjaxParams::ID) == UserManager::getStoredUser()->getId()) {
            throw new CoreException(__('Vous ne pouvez pas supprimer le compte avec lequel vous êtes connecté'));
        }
        $user = UserManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($user)) {
            throw new CoreException('User ID inconnu');
        }
        $user->remove();
        $this->ajax->success();
    }

    public function saveProfils()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedOrFail();
        $currentUser = UserManager::getStoredUser();
        $user_json = NextDomHelper::fromHumanReadable(json_decode(Utils::init(AjaxParams::PROFILS), true));
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
        $this->ajax->success();
    }

    public function get()
    {
        AuthentificationHelper::init();
        $this->ajax->success(NextDomHelper::toHumanReadable(Utils::o2a(UserManager::getStoredUser())));
    }

    public function removeRegisterDevice()
    {
        AuthentificationHelper::init();
        $user = null;
        if (Utils::init(AjaxParams::KEY) == '' && Utils::init(AjaxParams::USER_ID) == '') {
            AuthentificationHelper::isConnectedAsAdminOrFail();
            foreach (UserManager::all() as $user) {
                if ($user->getId() == UserManager::getStoredUser()->getId()) {
                    UserManager::getStoredUser()->setOptions(UserOption::REGISTER_DEVICE, []);
                    UserManager::getStoredUser()->save();
                } else {
                    $user->setOptions(UserOption::REGISTER_DEVICE, []);
                    $user->save();
                }
            }
            $this->ajax->success();
        }
        if (Utils::init(AjaxParams::USER_ID) != '') {
            AuthentificationHelper::isConnectedAsAdminOrFail();
            $user = UserManager::byId(Utils::init(AjaxParams::USER_ID));
            if (!is_object($user)) {
                throw new CoreException(__('Utilisateur non trouvé : ') . Utils::init(AjaxParams::USER_ID));
            }
            $registerDevice = $user->getOptions(UserOption::REGISTER_DEVICE, []);
        } else {
            $registerDevice = UserManager::getStoredUser()->getOptions(UserOption::REGISTER_DEVICE, []);
        }

        if (Utils::init(AjaxParams::KEY) == '') {
            $registerDevice = [];
        } elseif (isset($registerDevice[Utils::init(AjaxParams::KEY)])) {
            unset($registerDevice[Utils::init(AjaxParams::KEY)]);
        }
        if (Utils::init(AjaxParams::USER_ID) != '') {
            $user->setOptions(UserOption::REGISTER_DEVICE, $registerDevice);
            $user->save();
        } else {
            @session_start();
            UserManager::getStoredUser()->setOptions(UserOption::REGISTER_DEVICE, $registerDevice);
            UserManager::getStoredUser()->save();
            @session_write_close();
        }
        $this->ajax->success();
    }

    public function deleteSession()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedOrFail();
        $sessions = SessionHelper::getSessionsList();
        if (isset($sessions[Utils::init(AjaxParams::ID)])) {
            $user = UserManager::byId($sessions[Utils::init(AjaxParams::ID)]['user_id']);
            if (is_object($user)) {
                $registerDevice = $user->getOptions(UserOption::REGISTER_DEVICE, []);
                foreach ($user->getOptions(UserOption::REGISTER_DEVICE, []) as $key => $value) {
                    if ($value['session_id'] == Utils::init(AjaxParams::ID)) {
                        unset($registerDevice[$key]);
                    }
                }
                $user->setOptions(UserOption::REGISTER_DEVICE, $registerDevice);
                $user->save();
            }
        }
        SessionHelper::deleteSession(Utils::init(AjaxParams::ID));
        $this->ajax->success();
    }

    public function testLdapConnection()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $connection = UserManager::connectToLDAP();
        if ($connection === false) {
            throw new CoreException();
        }
        $this->ajax->success();
    }

    public function removeBanIp()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        UserManager::removeBanIp();
        $this->ajax->success();
    }

    public function supportAccess()
    {
        AuthentificationHelper::init();
        AuthentificationHelper::isConnectedAsAdminOrFail();
        UserManager::supportAccess(Utils::init(AjaxParams::ENABLE));
        $this->ajax->success();
    }
}