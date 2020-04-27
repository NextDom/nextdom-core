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

use NextDom\Managers\ListenerManager;

require_once __DIR__ . '/../../core/php/core.inc.php';

class listener extends NextDom\Model\Entity\Listener {
    
    public static function all() {
        return ListenerManager::all();
    }
    
    public static function byId($_id) {
        return ListenerManager::byId($_id);
    }
    
    public static function byClass($_class) {
        return ListenerManager::byClass($_class);
    }
    
    public static function byClassAndFunction($_class, $_function, $_option = '') {
        return ListenerManager::byClassAndFunction($_class, $_function, $_option);
    }
    
    public static function searchClassFunctionOption($_class, $_function, $_option = '') {
        return ListenerManager::searchClassFunctionOption($_class, $_function, $_option);
    }
    
    public static function byClassFunctionAndEvent($_class, $_function, $_event) {
        return ListenerManager::byClassFunctionAndEvent($_class, $_function, $_event);
    }
    
    public static function removeByClassFunctionAndEvent($_class, $_function, $_event, $_option = '') {
        ListenerManager::removeByClassFunctionAndEvent($_class, $_function, $_event, $_option);
    }
    
    public static function searchEvent($_event) {
        return ListenerManager::searchEvent($_event);
    }
    
    public static function check($_event, $_value, $_datetime) {
        ListenerManager::check($_event, $_value, $_datetime);
    }
    
    public static function backgroundCalculDependencyCmd($_event) {
        ListenerManager::backgroundCalculDependencyCmd($_event);
    }
    
    public static function clean() {
        ListenerManager::clean();
    }
}
