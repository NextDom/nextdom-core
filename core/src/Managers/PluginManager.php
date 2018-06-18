<?php
/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Managers;

class PluginManager
{
    private static $_cache = array();
    private static $_enable = null;

    public static function byId($id)
    {
        if (is_string($id) && isset(self::$_cache[$id])) {
            return self::$_cache[$id];
        }
        if (!file_exists($id) || strpos($id, '/') === false) {
            $id = self::getPathById($id);
        }
        if (!file_exists($id)) {
            throw new \Exception('Plugin introuvable : ' . $id);
        }
        $data = json_decode(file_get_contents($id), true);
        if (!is_array($data)) {
            throw new \Exception('Plugin introuvable (json invalide) : ' . $id . ' => ' . print_r($data, true));
        }
        $plugin = new \plugin();
        $plugin->initPluginFromData($data);

        self::$_cache[$plugin->getId()] = $plugin;
        return $plugin;
    }

    private static function getPluginFromData($data)
    {

    }

    public static function getPathById($id)
    {
        return __DIR__ . '/../../plugins/' . $id . '/plugin_info/info.json';
    }
}