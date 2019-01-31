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

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../core/php/core.inc.php';

use NextDom\Managers\UserManager;
use PragmaRX\Google2FA\Google2FA;

class user {
    /*     * *************************Attributs****************************** */

    private $id;
    private $login;
    private $profils = 'admin';
    private $password;
    private $options;
    private $rights;
    private $enable = 1;
    private $hash;
    private $_changed = false;

    /*     * ***********************Méthodes statiques*************************** */

    public static function byId($_id) {
        return UserManager::byId($_id);
    }

    public static function connect($_login, $_mdp) {
        return UserManager::connect($_login, $_mdp);
    }

    public static function connectToLDAP() {
        return UserManager::connectToLDAP();
    }

    public static function byLogin($_login) {
        return UserManager::byLogin($_login);
    }

    public static function byHash($_hash) {
        return UserManager::byHash($_hash);
    }

    public static function byLoginAndHash($_login, $_hash) {
        return UserManager::byLoginAndHash($_login, $_hash);
    }

    public static function byLoginAndPassword($_login, $_password) {
        return UserManager::byLoginAndPassword($_login, $_password);
    }

    public static function all() {
        return UserManager::all();
    }

    public static function searchByRight($_rights) {
        return UserManager::searchByRight($_rights);
    }

    public static function byProfils($_profils, $_enable = false) {
        return UserManager::byProfils($_profils, $_enable);
    }

    public static function byEnable($_enable) {
        return UserManager::byEnable($_enable);
    }

    public static function failedLogin() {
        UserManager::failedLogin();
    }

    public static function removeBanIp() {
        UserManager::removeBanIp();
    }

    public static function isBan() {
        return UserManager::isBanned();
    }

    public static function getAccessKeyForReport() {
        return UserManager::getAccessKeyForReport();
    }

    public static function supportAccess($_enable = true) {
        UserManager::supportAccess($_enable);
    }

    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        if (is_object(self::byLogin($this->getLogin()))) {
            throw new Exception(__('Ce nom d\'utilisateur est déja pris', __FILE__));
        }
    }

    public function preSave() {
        if ($this->getLogin() == '') {
            throw new Exception(__('Le nom d\'utilisateur ne peut pas être vide', __FILE__));
        }
        $admins = user::byProfils('admin', true);
        if (count($admins) == 1 && $this->getProfils() == 'admin' && $this->getEnable() == 0) {
            throw new Exception(__('Vous ne pouvez désactiver le dernier utilisateur', __FILE__));
        }
        if (count($admins) == 1 && $admins[0]->getId() == $this->getid() && $this->getProfils() != 'admin') {
            throw new Exception(__('Vous ne pouvez changer le profil du dernier administrateur', __FILE__));
        }
    }

    public function save() {
        return DB::save($this);
    }

    public function preRemove() {
        if (count(user::byProfils('admin', true)) == 1 && $this->getProfils() == 'admin') {
            throw new Exception(__('Vous ne pouvez supprimer le dernier administrateur', __FILE__));
        }
    }

    public function remove() {
        nextdom::addRemoveHistory(array('id' => $this->getId(), 'name' => $this->getLogin(), 'date' => date('Y-m-d H:i:s'), 'type' => 'user'));
        return DB::remove($this);
    }

    public function refresh() {
        DB::refresh($this);
    }

    /**
     *
     * @return boolean vrai si l'utilisateur est valide
     */
    public function is_Connected() {
        return (is_numeric($this->id) && $this->login != '');
    }

    public function validateTwoFactorCode($_code) {
        $google2fa = new Google2FA();
        return $google2fa->verifyKey($this->getOptions('twoFactorAuthentificationSecret'), $_code);
    }

    /*     * **********************Getteur Setteur*************************** */


    public function getId() {
        return $this->id;
    }

    public function getLogin() {
        return $this->login;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setId($_id) {
        $this->_changed = utils::attrChanged($this->_changed,$this->id,$_id);
        $this->id = $_id;
        return $this;
    }

    public function setLogin($_login) {
        $this->_changed = utils::attrChanged($this->_changed,$this->login,$_login);
        $this->login = $_login;
        return $this;
    }

    public function setPassword($_password) {
        $_password = (!is_sha512($_password)) ? sha512($_password) : $_password;
        $this->_changed = utils::attrChanged($this->_changed,$this->password,$_password);
        $this->password = $_password;
        return $this;
    }

    public function getOptions($_key = '', $_default = '') {
        return utils::getJsonAttr($this->options, $_key, $_default);
    }

    public function setOptions($_key, $_value) {
        $options = utils::setJsonAttr($this->options, $_key, $_value);
        $this->_changed = utils::attrChanged($this->_changed,$this->options,$options);
        $this->options = $options;
        return $this;
    }

    public function getRights($_key = '', $_default = '') {
        return utils::getJsonAttr($this->rights, $_key, $_default);
    }

    public function setRights($_key, $_value) {
        $rights = utils::setJsonAttr($this->rights, $_key, $_value);
        $this->_changed = utils::attrChanged($this->_changed,$this->rights,$rights);
        $this->rights = $rights;
        return $this;
    }

    public function getEnable() {
        return $this->enable;
    }

    public function setEnable($_enable) {
        $this->_changed = utils::attrChanged($this->_changed,$this->enable,$_enable);
        $this->enable = $_enable;
        return $this;
    }

    public function getHash() {
        if ($this->hash == '' && $this->id != '') {
            $hash = config::genKey();
            while (is_object(self::byHash($hash))) {
                $hash = config::genKey();
            }
            $this->setHash($hash);
            $this->save();
        }
        return $this->hash;
    }

    public function setHash($_hash) {
        $this->_changed = utils::attrChanged($this->_changed,$this->hash,$_hash);
        $this->hash = $_hash;
        return $this;
    }

    public function getProfils() {
        return $this->profils;
    }

    public function setProfils($_profils) {
        $this->_changed = utils::attrChanged($this->_changed,$this->profils,$_profils);
        $this->profils = $_profils;
        return $this;
    }

    public function getChanged() {
        return $this->_changed;
    }

    public function setChanged($_changed) {
        $this->_changed = $_changed;
        return $this;
    }

}
