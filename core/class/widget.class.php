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

use NextDom\Managers\WidgetManager;

class widgets extends \NextDom\Model\Entity\Widget {

    public static function all() {
        return WidgetManager::all();
    }

    public static function byId($_id) {
      return WidgetManager::byId($_id);
    }

    public static function byTypeSubtypeAndName($_type, $_subtype, $_name) {
      return WidgetManager::byTypeSubtypeAndName($_type, $_subtype, $_name);
    }

    public static function listTemplate(){
        return WidgetManager::listTemplate();
    }

    public static function getTemplateConfiguration($_template){
        return WidgetManager::loadConfig($_template);
    }
  
    public static function replacement($_version,$_replace,$_by){
        return WidgetManager::replacement($_version,$_replace,$_by);
    }
}