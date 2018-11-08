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

/* This file is part of NextDom Software.
 *
 * NextDom Software is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

require_once __DIR__ . '/../../core/php/core.inc.php';

use NextDom\Managers\EventManager;

/**
 * Class event
 *
 * Interface for jeedom plugins compatibility
 * @see NextDom\Managers\EventManager
 */
class event {
    /**
     * @see EventManager::add
     */
    public static function add($_event, $_option = array()) {
        EventManager::add($_event, $_option);
    }

    /**
     * @see EventManager::adds
     */
    public static function adds($_event, $_values = array()) {
        EventManager::adds($_event, $_values);
    }

    /**
     * @see EventManager::orderEvent
     */
    public static function orderEvent($a, $b) {
        return EventManager::orderEvent($a, $b);
    }

    /**
     * @see EventManager::changes
     */
    public static function changes($_datetime, $_longPolling = null, $_filter = null) {
        return EventManager::changes($_datetime, $_longPolling, $_filter);
    }
}
