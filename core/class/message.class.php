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

use NextDom\Managers\MessageManager;

require_once __DIR__ . '/../../core/php/core.inc.php';

class message extends \NextDom\Model\Entity\Message {
    public static function add($_type, $_message, $_action = '', $_logicalId = '', $_writeMessage = true) {
        MessageManager::add($_type, $_message, $_action, $_logicalId, $_writeMessage);
    }

    public static function removeAll($_plugin = '', $_logicalId = '', $_search = false) {
        MessageManager::removeAll($_plugin, $_logicalId, $_search);
    }

    public static function nbMessage() {
        return MessageManager::nbMessage();
    }

    public static function byId($_id) {
        return MessageManager::byId($_id);
    }
    
    public static function byPluginLogicalId($_plugin, $_logicalId) {
        return MessageManager::byPluginLogicalId($_plugin, $_logicalId);
    }
    
    public static function byPlugin($_plugin) {
        return MessageManager::byPlugin($_plugin);
    }
    
    public static function listPlugin() {
        return MessageManager::listPlugin();
    }
    
    public static function all() {
        return MessageManager::all();
    }
    
}
