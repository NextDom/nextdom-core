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

use NextDom\Managers\PlanHeaderManager;

require_once __DIR__ . '/../../core/php/core.inc.php';

class planHeader extends \NextDom\Model\Entity\PlanHeader {
    public static function byId($_id) {
        return PlanHeaderManager::byId($_id);
    }
    
    public static function all() {
        return PlanHeaderManager::all();
    }

    public static function searchByUse($_type, $_id) {
        return PlanHeaderManager::searchByUse($_type, $_id);
    }

}
