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

use NextDom\Helpers\DBHelper;
use NextDom\Model\Entity\ViewData;

/**
 * Class ViewDataManager
 * @package NextDom\Managers
 */
class ViewDataManager
{
    const DB_CLASS_NAME = '`viewData`';
    const CLASS_NAME = 'viewData';


    /**
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function all()
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME;
        return DBHelper::getAllObjects($sql, [], self::CLASS_NAME);
    }

    /**
     * @param $_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byId($_id)
    {
        $value = [
            'id' => $_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE id=:id';
        return DBHelper::getOneObject($sql, $value, self::CLASS_NAME);
    }

    /**
     * @param $_viewZone_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byViewZoneId($_viewZone_id)
    {
        $value = [
            'viewZone_id' => $_viewZone_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE viewZone_id=:viewZone_id
        ORDER BY `order`';
        return DBHelper::getAllObjects($sql, $value, self::CLASS_NAME);
    }

    /**
     * @param $_search
     * @return ViewData[]|null
     * @throws \Exception
     */
    public static function searchByConfiguration($_search)
    {
        $value = [
            'search' => '%' . $_search . '%',
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE configuration LIKE :search';
        return DBHelper::getAllObjects($sql, $value, self::CLASS_NAME);
    }

    /**
     * @param $_type
     * @param $_link_id
     * @return bool
     * @throws \Exception
     */
    public static function removeByTypeLinkId($_type, $_link_id)
    {
        $viewDatas = self::byTypeLinkId($_type, $_link_id);
        foreach ($viewDatas as $viewData) {
            $viewData->remove();
        }
        return true;
    }

    /**
     * @param $_type
     * @param $_link_id
     * @return ViewData[]|null
     * @throws \Exception
     */
    public static function byTypeLinkId($_type, $_link_id)
    {
        $value = [
            'type' => $_type,
            'link_id' => $_link_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE type=:type
        AND link_id=:link_id
        ORDER BY `order`';
        return DBHelper::getAllObjects($sql, $value, self::CLASS_NAME);
    }
}
