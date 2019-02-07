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

use NextDom\Managers\Plan3dManager;

class plan3d extends \NextDom\Model\Entity\Plan3d {
    public static function byId($_id) {
        return Plan3dManager::byId($_id);
    }
    
    public static function byPlan3dHeaderId($_plan3dHeader_id) {
        return Plan3dManager::byPlan3dHeaderId($_plan3dHeader_id);
    }
    
    public static function byLinkTypeLinkId($_link_type, $_link_id) {
        return Plan3dManager::byLinkTypeLinkId($_link_type, $_link_id);
    }
    
    public static function byName3dHeaderId($_name, $_plan3dHeader_id) {
        return Plan3dManager::byName3dHeaderId($_name, $_plan3dHeader_id);
    }
    
    public static function byLinkTypeLinkId3dHeaderId($_link_type, $_link_id, $_plan3dHeader_id) {
        return Plan3dManager::byLinkTypeLinkId3dHeaderId($_link_type, $_link_id, $_plan3dHeader_id);
    }
    
    public static function removeByLinkTypeLinkId3dHeaderId($_link_type, $_link_id, $_plan3dHeader_id) {
        return Plan3dManager::removeByLinkTypeLinkId3dHeaderId($_link_type, $_link_id, $_plan3dHeader_id);
    }
    
    public static function all() {
        return Plan3dManager::all();
    }
    
    public static function searchByDisplay($_search) {
        return Plan3dManager::searchByDisplay($_search);
    }
    
    public static function searchByConfiguration($_search, $_not = '') {
        return Plan3dManager::searchByConfiguration($_search, $_not);
    }
}
