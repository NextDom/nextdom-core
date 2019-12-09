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
use NextDom\Model\Entity\ViewZone;

/**
 * Class ViewZoneManager
 * @package NextDom\Managers
 */
class ViewZoneManager
{
    const DB_CLASS_NAME = '`viewZone`';
    const CLASS_NAME = 'viewZone';


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
     * @return ViewZone|null
     * @throws \Exception
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
     * @param $_view_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byView($_view_id)
    {
        $value = [
            'view_id' => $_view_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE view_id=:view_id';
        return DBHelper::getAllObjects($sql, $value, self::CLASS_NAME);
    }

    /**
     * @param $_view_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function removeByViewId($_view_id)
    {
        $value = [
            'view_id' => $_view_id,
        ];
        $sql = 'DELETE FROM ' . self::DB_CLASS_NAME . '
                WHERE view_id = :view_id';
        return DBHelper::getOne($sql, $value);
    }

}
