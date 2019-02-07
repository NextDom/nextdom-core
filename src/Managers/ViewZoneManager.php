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

class ViewZoneManager
{
    const DB_CLASS_NAME = '`viewZone`';
    const CLASS_NAME = 'viewZone';


    public static function all() {
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME;
        return \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function byId($_id) {
        $value = array(
            'id' => $_id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE id=:id';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function byView($_view_id) {
        $value = array(
            'view_id' => $_view_id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE view_id=:view_id';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function removeByViewId($_view_id) {
        $value = array(
            'view_id' => $_view_id,
        );
        $sql = 'DELETE FROM ' . self::DB_CLASS_NAME . '
                WHERE view_id=:view_id';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ROW);
    }

}
