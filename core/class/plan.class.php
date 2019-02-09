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

use NextDom\Managers\PlanManager;

require_once __DIR__ . '/../../core/php/core.inc.php';

class plan extends \NextDom\Model\Entity\Plan {

    public static function byId($_id) {
        return PlanManager::byId($_id);
    }

    public static function byPlanHeaderId($_planHeader_id) {
        return PlanManager::byPlanHeaderId($_planHeader_id);
    }
    
    public static function byLinkTypeLinkId($_link_type, $_link_id) {
        return PlanManager::byLinkTypeLinkId($_link_type, $_link_id);
    }
    
    public static function byLinkTypeLinkIdPlanHedaerId($_link_type, $_link_id, $_planHeader_id) {
        return PlanManager::byLinkTypeLinkIdPlanHedaerId($_link_type, $_link_id, $_planHeader_id);
    }
    
    public static function removeByLinkTypeLinkIdPlanHedaerId($_link_type, $_link_id, $_planHeader_id) {
        return PlanManager::removeByLinkTypeLinkIdPlanHedaerId($_link_type, $_link_id, $_planHeader_id);
    }

    public static function all() {
        return PlanManager::all();
    }

    public static function searchByDisplay($_search) {
        return PlanManager::searchByDisplay($_search);
    }

    public static function searchByConfiguration($_search, $_not = '') {
        return PlanManager::searchByConfiguration($_search, $_not);
    }
}
