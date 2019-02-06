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

class user extends \NextDom\Model\Entity\User {

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
}
