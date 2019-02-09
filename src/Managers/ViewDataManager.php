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

use NextDom\Model\Entity\ViewData;

class ViewDataManager
{
    const DB_CLASS_NAME = '`viewData`';
    const CLASS_NAME = 'viewData';


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

    /**
     * @param $_type
     * @param $_link_id
     * @return ViewData[]|null
     * @throws \Exception
     */
    public static function byTypeLinkId($_type, $_link_id) {
        $value = array(
            'type' => $_type,
            'link_id' => $_link_id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE type=:type
        AND link_id=:link_id
        ORDER BY `order`';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function byViewZoneId($_viewZone_id) {
        $value = array(
            'viewZone_id' => $_viewZone_id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE viewZone_id=:viewZone_id
        ORDER BY `order`';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_search
     * @return ViewData[]|null
     * @throws \Exception
     */
    public static function searchByConfiguration($_search) {
        $value = array(
            'search' => '%' . $_search . '%',
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE configuration LIKE :search';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function removeByTypeLinkId($_type, $_link_id) {
        $viewDatas = self::byTypeLinkId($_type, $_link_id);
        foreach ($viewDatas as $viewData) {
            $viewData->remove();
        }
        return true;
    }
}
