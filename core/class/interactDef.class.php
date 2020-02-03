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

use NextDom\Managers\InteractDefManager;

require_once __DIR__ . '/../../core/php/core.inc.php';

class interactDef extends \NextDom\Model\Entity\InteractDef
{
    public function getFiltres($_key = '', $_default = '') {
        return $this->getFilters($_key, $_default);
    }

    public function setFiltres($_key = '', $_default = '') {
        return $this->setFilters($_key, $_default);
    }

    public static function byId($_id)
    {
        return InteractDefManager::byId($_id);
    }

    public static function all($_group = '')
    {
        return InteractDefManager::all($_group);
    }

    public static function listGroup($_group = null)
    {
        return InteractDefManager::listGroup($_group);
    }

    public static function generateTextVariant($_text)
    {
        return InteractDefManager::generateTextVariant($_text);
    }

    public static function searchByQuery($_query)
    {
        return InteractDefManager::searchByUse($_query);
    }

    public static function regenerateInteract()
    {
        InteractDefManager::regenerateInteract();
    }

    public static function getTagFromQuery($_def, $_query)
    {
        return InteractDefManager::getTagFromQuery($_def, $_query);
    }

    public static function sanitizeQuery($_query)
    {
        return InteractDefManager::sanitizeQuery($_query);
    }

    public static function deadCmd()
    {
        return InteractDefManager::deadCmd();
    }

    public static function cleanInteract()
    {
        return InteractDefManager::cleanInteract();
    }

    public static function searchByUse($_search)
    {
        return InteractDefManager::searchByUse($_search);
    }

    public static function generateSynonymeVariante($_text, $_synonymes, $_deep = 0)
    {
        return InteractDefManager::generateSynonymeVariante($_text, $_synonymes, $_deep);
    }
}
