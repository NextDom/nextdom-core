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

use NextDom\Managers\DataStoreManager;

class dataStore extends \NextDom\Model\Entity\DataStore {
    public static function byId($_id) {
        return DataStoreManager::byId($_id);
    }

    public static function byTypeLinkIdKey($_type, $_link_id, $_key) {
        return DataStoreManager::byTypeLinkIdKey($_type, $_link_id, $_key);
    }

    public static function byTypeLinkId($_type, $_link_id = '') {
        return DataStoreManager::byTypeLinkId($_type, $_link_id);
    }

    public static function removeByTypeLinkId($_type, $_link_id) {
        return DataStoreManager::removeByTypeLinkId($_type, $_link_id);
    }
}
