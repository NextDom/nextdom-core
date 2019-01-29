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

use NextDom\Managers\CmdManager;

/* * ***************************Includes********************************* */
require_once NEXTDOM_ROOT.'/core/php/core.inc.php';

class cmd extends NextDom\Model\Entity\Cmd {
    public static function byId($_id) {
        return CmdManager::byId($_id);
    }

    public static function byIds($_ids) {
        return CmdManager::byIds($_ids);
    }

    public static function all() {
        return CmdManager::all();
    }

    public static function allHistoryCmd() {
        return CmdManager::allHistoryCmd();
    }

    public static function byEqLogicId($_eqLogic_id, $_type = null, $_visible = null, $_eqLogic = null, $_has_generic_type = null) {
        return CmdManager::byEqlogicId($_eqLogic_id, $_type, $_visible, $_eqLogic, $_has_generic_type);
    }

    public static function byLogicalId($_logical_id, $_type = null) {
        return CmdManager::byLogicalId($_logical_id, $_type);
    }

    public static function byGenericType($_generic_type, $_eqLogic_id = null, $_one = false) {
        return CmdManager::byGenericType($_generic_type, $_eqLogic_id, $_one);
    }

    public static function searchConfiguration($_configuration, $_eqType = null) {
        return CmdManager::searchConfiguration($_configuration, $_eqType);
    }

    public static function searchConfigurationEqLogic($_eqLogic_id, $_configuration, $_type = null) {
        return CmdManager::searchConfigurationEqLogic($_eqLogic_id, $_configuration, $_type);
    }

    public static function searchTemplate($_template, $_eqType = null, $_type = null, $_subtype = null) {
        return CmdManager::searchTemplate($_template, $_eqType, $_type, $_subtype);
    }

    public static function byEqLogicIdAndLogicalId($_eqLogic_id, $_logicalId, $_multiple = false, $_type = null) {
        return CmdManager::byEqLogicIdAndLogicalId($_eqLogic_id, $_logicalId, $_multiple, $_type);
    }

    public static function byEqLogicIdAndGenericType($_eqLogic_id, $_generic_type, $_multiple = false, $_type = null) {
        return CmdManager::byEqLogicIdAndGenericType($_eqLogic_id, $_generic_type, $_multiple, $_type);
    }

    public static function byValue($_value, $_type = null, $_onlyEnable = false) {
        return CmdManager::byValue($_value, $_type, $_onlyEnable);
    }

    public static function byTypeEqLogicNameCmdName($_eqType_name, $_eqLogic_name, $_cmd_name) {
        return CmdManager::byTypeEqLogicNameCmdName($_eqType_name, $_eqLogic_name, $_cmd_name);
    }

    public static function byEqLogicIdCmdName($_eqLogic_id, $_cmd_name) {
        return CmdManager::byEqLogicIdCmdName($_eqLogic_id, $_cmd_name);
    }

    public static function byObjectNameEqLogicNameCmdName($_object_name, $_eqLogic_name, $_cmd_name) {
        return CmdManager::byObjectNameEqLogicNameCmdName($_object_name, $_eqLogic_name, $_cmd_name);
    }

    public static function byObjectNameCmdName($_object_name, $_cmd_name) {
        return CmdManager::byObjectNameCmdName($_object_name, $_cmd_name);
    }

    public static function byTypeSubType($_type, $_subType = '') {
        return CmdManager::byTypeSubType($_type, $_subType);
    }

    public static function cmdToHumanReadable($_input) {
        return CmdManager::cmdToHumanReadable($_input);
    }

    public static function humanReadableToCmd($_input) {
        return CmdManager::humanReadableToCmd($_input);
    }

    public static function byString($_string) {
        return CmdManager::byString($_string);
    }

    public static function cmdToValue($_input, $_quote = false) {
        return CmdManager::cmdToValue($_input, $_quote);
    }

    public static function allType() {
        return CmdManager::allType();
    }

    public static function allSubType($_type = '') {
        return CmdManager::allSubType($_type);
    }

    public static function allUnite() {
        return CmdManager::allUnite();
    }

    public static function convertColor($_color) {
        return CmdManager::convertColor($_color);
    }

    public static function availableWidget($_version) {
        return CmdManager::availableWidget($_version);
    }

    public static function returnState($_options) {
        CmdManager::returnState($_options);
    }

    public static function deadCmd() {
        return CmdManager::deadCmd();
    }

    public static function cmdAlert($_options) {
        CmdManager::cmdAlert($_options);
    }

    public static function timelineDisplay($_event) {
        return CmdManager::timelineDisplay($_event);
    }
}
