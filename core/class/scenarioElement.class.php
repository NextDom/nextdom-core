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
require_once NEXTDOM_ROOT . '/core/php/core.inc.php';

use NextDom\Managers\ScenarioElementManager;
use NextDom\Managers\ScenarioSubElementManager;
use NextDom\Managers\ScenarioExpressionManager;

class scenarioElement extends \NextDom\Model\Entity\ScenarioElement {
    public static function byId($_id) {
        return ScenarioElementManager::byId($_id);
    }

    public static function saveAjaxElement($element_ajax) {
        return ScenarioElementManager::saveAjaxElement($element_ajax);
    }
}
