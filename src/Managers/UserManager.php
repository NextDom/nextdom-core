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
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Managers;

use NextDom\Helpers\DBHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Model\Entity\User;
use NextDom\Repo\RepoMarket;
use PragmaRX\Google2FA\Google2FA;

/**
 * Class UserManager
 * @package NextDom\Managers
 */
class UserManager
{
    const DB_CLASS_NAME = '`user`';
    const CLASS_NAME = 'user';

    /*     * ***********************Méthodes statiques*************************** */

    /**
     * Retourne un object utilisateur (si les information de connection sont valide)
     * @param string $_login nom d'utilisateur
     * @param string $_mdp motsz de passe en sha512
     * @return User|bool object user
     * @throws \Exception
     */
    public static function connect($_login, $_mdp)
    {
        $sMdp = (!Utils::isSha512($_mdp)) ? Utils::sha512($_mdp) : $_mdp;
        if (ConfigManager::byKey('ldap:enable') == '1' && function_exists('ldap_connect')) {
            LogHelper::add("connection", "debug", __('Authentification par LDAP'));
            $ad = self::connectToLDAP();
            if ($ad !== false) {
                LogHelper::add("connection", "debug", __('Connection au LDAP OK'));
                $ad = ldap_connect(ConfigManager::byKey('ldap:host'), ConfigManager::byKey('ldap:port'));
                ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);
                if (!ldap_bind($ad, 'uid=' . $_login . ',' . ConfigManager::byKey('ldap:basedn'), $_mdp)) {
                    LogHelper::add("connection", "info", __('Mot de passe erroné (') . $_login . ')');
                    return false;
                }
                LogHelper::add("connection", "debug", __('Bind user OK'));
                $result = ldap_search($ad, ConfigManager::byKey('ldap::usersearch') . '=' . $_login . ',' . ConfigManager::byKey('ldap:basedn'), ConfigManager::byKey('ldap:filter'));
                LogHelper::add("connection", "info", __('Recherche LDAP (') . $_login . ')');
                if ($result) {
                    $entries = ldap_get_entries($ad, $result);
                    if ($entries['count'] > 0) {
                        $user = self::byLogin($_login);
                        if (is_object($user)) {
                            $user->setPassword($sMdp)
                                ->setOptions('lastConnection', date('Y-m-d H:i:s'));
                            $user->save();
                            return $user;
                        }
                        $user = (new User())
                            ->setLogin($_login)
                            ->setPassword($sMdp)
                            ->setOptions('lastConnection', date('Y-m-d H:i:s'));
                        $user->save();
                        LogHelper::add("connection", "info", __('Utilisateur créé depuis le LDAP : ') . $_login);
                        NextDomHelper::event('user_connect');
                        LogHelper::add('event', 'info', __('Connexion de l\'utilisateur ') . $_login);
                        return $user;
                    } else {
                        $user = self::byLogin($_login);
                        if (is_object($user)) {
                            $user->remove();
                        }
                        LogHelper::add("connection", "info", __('Utilisateur non autorisé à accéder à NextDom (') . $_login . ')');
                        return false;
                    }
                } else {
                    $user = self::byLogin($_login);
                    if (is_object($user)) {
                        $user->remove();
                    }
                    LogHelper::add("connection", "info", __('Utilisateur non autorisé à accéder à NextDom (') . $_login . ')');
                    return false;
                }
            } else {
                LogHelper::add("connection", "info", __('Impossible de se connecter au LDAP'));
            }
        }
        $user = self::byLoginAndPassword($_login, $sMdp);
        if (!is_object($user)) {
            $user = self::byLoginAndPassword($_login, sha1($_mdp));
            if (is_object($user)) {
                $user->setPassword($sMdp);
            }
        }
        if (is_object($user)) {
            $user->setOptions('lastConnection', date('Y-m-d H:i:s'));
            $user->save();
            NextDomHelper::event('user_connect');
            LogHelper::add('event', 'info', __('Connexion de l\'utilisateur ') . $_login);
        }
        return $user;
    }

    /**
     * @return bool|resource
     * @throws \Exception
     */
    public static function connectToLDAP()
    {
        $ad = ldap_connect(ConfigManager::byKey('ldap:host'), ConfigManager::byKey('ldap:port'));
        ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);
        if (ldap_bind($ad, ConfigManager::byKey('ldap:username'), ConfigManager::byKey('ldap:password'))) {
            return $ad;
        }
        return false;
    }

    /**
     * @param $_login
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byLogin($_login)
    {
        $values = array(
            'login' => $_login,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE login = :login';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_login
     * @param $_password
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byLoginAndPassword($_login, $_password)
    {
        $values = array(
            'login' => $_login,
            'password' => $_password,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE login = :login
                AND password = :password';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_id
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function byId($_id)
    {
        $values = array(
            'id' => $_id,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_hash
     * @return User
     * @throws \Exception
     */
    public static function byHash($_hash)
    {
        $values = array(
            'hash' => $_hash,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE hash = :hash';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_login
     * @param $_hash
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byLoginAndHash($_login, $_hash)
    {
        $values = array(
            'login' => $_login,
            'hash' => $_hash,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE login = :login
                AND hash = :hash';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     *
     * @return User[] Array with all users
     * @throws \Exception
     */
    public static function all()
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME;
        return DBHelper::Prepare($sql, array(), DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_rights
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function searchByRight($_rights)
    {
        $values = array(
            'rights' => '%"' . $_rights . '":1%',
            'rights2' => '%"' . $_rights . '":"1"%',
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE rights LIKE :rights
                OR rights LIKE :rights2';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_profils
     * @param bool $_enable
     * @return User[]|null
     * @throws \Exception
     */
    public static function byProfils($_profils, $_enable = false)
    {
        $values = array(
            'profils' => $_profils,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE profils = :profils';
        if ($_enable) {
            $sql .= ' AND enable=1';
        }
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_enable
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byEnable($_enable)
    {
        $values = array(
            'enable' => $_enable,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE enable=:enable';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function failedLogin()
    {
        @session_start();
        $_SESSION['failed_count'] = (isset($_SESSION['failed_count'])) ? $_SESSION['failed_count'] + 1 : 1;
        $_SESSION['failed_datetime'] = strtotime('now');
        @session_write_close();
    }

    public static function removeBanIp()
    {
        $cache = CacheManager::byKey('security::banip');
        $cache->remove();
    }

    /**
     * @deprecated
     * @return bool
     */
    public static function isBan()
    {
        return self::isBanned();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public static function isBanned()
    {
        $ip = NetworkHelper::getClientIp();
        if ($ip == '') {
            return false;
        }
        $whiteIps = explode(';', ConfigManager::byKey('security::whiteips'));
        if (ConfigManager::byKey('security::whiteips') != '' && count($whiteIps) > 0) {
            foreach ($whiteIps as $whiteip) {
                if (NetworkHelper::netMatch($whiteip, $ip)) {
                    return false;
                }
            }
        }
        $cache = CacheManager::byKey('security::banip');
        $values = json_decode($cache->getValue('[]'), true);
        if (!is_array($values)) {
            $values = array();
        }
        $values_tmp = array();
        if (count($values) > 0) {
            foreach ($values as $value) {
                if (ConfigManager::byKey('security::bantime') >= 0 && $value['datetime'] + ConfigManager::byKey('security::bantime') < strtotime('now')) {
                    continue;
                }
                $values_tmp[] = $value;
            }
        }
        $values = $values_tmp;
        if (isset($_SESSION['failed_count']) && $_SESSION['failed_count'] >= ConfigManager::byKey('security::maxFailedLogin') && (strtotime('now') - ConfigManager::byKey('security::timeLoginFailed')) < $_SESSION['failed_datetime']) {
            $values_tmp = array();
            foreach ($values as $value) {
                if ($value['ip'] == $ip) {
                    continue;
                }
                $values_tmp[] = $value;
            }
            $values = $values_tmp;
            $values[] = array('datetime' => strtotime('now'), 'ip' => NetworkHelper::getClientIp());
            @session_start();
            $_SESSION['failed_count'] = 0;
            $_SESSION['failed_datetime'] = -1;
            @session_write_close();
        }
        CacheManager::set('security::banip', json_encode($values));
        if (!is_array($values)) {
            $values = array();
        }
        if (count($values) == 0) {
            return false;
        }
        foreach ($values as $value) {
            if ($value['ip'] != $ip) {
                continue;
            }
            if (ConfigManager::byKey('security::bantime') >= 0 && $value['datetime'] + ConfigManager::byKey('security::bantime') < strtotime('now')) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getAccessKeyForReport()
    {
        $user = self::byLogin('internal_report');
        if (!is_object($user)) {
            $user = new User();
            $user->setLogin('internal_report');
            $google2fa = new Google2FA();
            $user->setOptions('twoFactorAuthentificationSecret', $google2fa->generateSecretKey());
            $user->setOptions('twoFactorAuthentification', 1);
        }
        $user->setPassword(Utils::sha512(ConfigManager::genKey(255)));
        $user->setOptions('localOnly', 1);
        $user->setProfils('admin');
        $user->setEnable(1);
        $key = ConfigManager::genKey();
        $registerDevice = array(
            Utils::sha512($key) => array(
                'datetime' => date('Y-m-d H:i:s'),
                'ip' => '127.0.0.1',
                'session_id' => 'none',
            ),
        );
        $user->setOptions('registerDevice', $registerDevice);
        $user->save();
        return $user->getHash() . '-' . $key;
    }

    /**
     * @param bool $_enable
     * @throws \Exception
     */
    public static function supportAccess($_enable = true)
    {
        if ($_enable) {
            $user = self::byLogin('nextdom_support');
            if (!is_object($user)) {
                $user = new User();
                $user->setLogin('nextdom_support');
            }
            $user->setPassword(Utils::sha512(ConfigManager::genKey(255)));
            $user->setProfils('admin');
            $user->setEnable(1);
            $key = ConfigManager::genKey();
            $registerDevice = array(
                Utils::sha512($key) => array(
                    'datetime' => date('Y-m-d H:i:s'),
                    'ip' => '127.0.0.1',
                    'session_id' => 'none',
                ),
            );
            $user->setOptions('registerDevice', $registerDevice);
            $user->save();
            RepoMarket::supportAccess(true, $user->getHash() . '-' . $key);
        } else {
            $user = self::byLogin('nextdom_support');
            if (is_object($user)) {
                $user->remove();
            }
            RepoMarket::supportAccess(false);
        }
    }

    /**
     * @param $user
     */
    public static function storeUserInSession($user)
    {
        $_SESSION['user'] = $user;
    }

    /**
     * @return User|null
     */
    public static function getStoredUser()
    {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        }
        return null;
    }
}
