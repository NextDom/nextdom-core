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

use NextDom\Managers\InteractQueryManager;

require_once __DIR__ . '/../../core/php/core.inc.php';

class interactQuery extends \NextDom\Model\Entity\InteractQuery {

    public static function byId($_id) {
        return InteractqueryManager::byId($_id);
    }

    public static function byQuery($_query, $_interactDef_id = null) {
        return InteractQueryManager::byQuery($_query, $_interactDef_id);
    }

    public static function byInteractDefId($_interactDef_id) {
        return InteractQueryManager::byInteractDefId($_interactDef_id);
    }

    public static function searchActions($_action) {
        return InteractQueryManager::searchActions($_action);
    }

    public static function all() {
        return InteractQueryManager::all();
    }

    public static function removeByInteractDefId($_interactDef_id) {
        return InteractQueryManager::removeByInteractDefId($_interactDef_id);
    }

    public static function recognize($_query) {
        return InteractQueryManager::recognize($_query);
    }

    public static function getQuerySynonym($_query, $_for) {
        return InteractQueryManager::getQuerySynonym($_query, $_for);
    }

    public static function findInQuery($_type, $_query, $_data = null) {
        return InteractQueryManager::findInQuery($_type, $_query, $_data);
    }

    public static function cmp_objectName($a, $b) {
        return InteractQueryManager::cmp_objectName($a, $b);
    }

    public static function autoInteract($_query, $_parameters = array()) {
        return InteractQueryManager::autoInteract($_query, $_parameters);
    }

    public static function autoInteractWordFind($_string, $_word) {
        return InteractQueryManager::autoInteractWordFind($_string, $_word);
    }

    public static function pluginReply($_query, $_parameters = array()) {
        return InteractQueryManager::pluginReply($_query, $_parameters);
    }

    public static function warnMe($_query, $_parameters = array()) {
        return InteractQueryManager::warnMe($_query, $_parameters);
    }

    public static function warnMeExecute($_options) {
        InteractQueryManager::warnMeExecute($_options);
    }

    public static function tryToReply($_query, $_parameters = array()) {
        return InteractQueryManager::tryToReply($_query, $_parameters);
    }

    public static function addLastInteract($_lastCmd, $_identifier = 'unknown') {
        InteractQueryManager::addLastInteract($_lastCmd, $_identifier);
    }

    public static function contextualReply($_query, $_parameters = array(), $_lastCmd = null) {
        return InteractQueryManager::contextualReply($_query, $_parameters, $_lastCmd);
    }

    public static function brainReply($_query, $_parameters) {
        return InteractQueryManager::brainReply($_query, $_parameters);
    }

    public static function dontUnderstand($_parameters) {
        return InteractQueryManager::dontUnderstand($_parameters);
    }

    public static function replyOk() {
        return InteractQueryManager::replyOk();
    }

    public static function doIn($_params) {
        InteractQueryManager::doIn($_params);
    }
}
