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
use NextDom\Managers\Parents\BaseManager;
use NextDom\Managers\Parents\CommonManager;
use NextDom\Model\Entity\ViewZone;

/**
 * Class ViewZoneManager
 * @package NextDom\Managers
 */
class ViewZoneManager extends BaseManager
{
    use CommonManager;
    const DB_CLASS_NAME = '`viewZone`';
    const CLASS_NAME = 'viewZone';


    /**
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function all()
    {
        return static::getAll();
    }

    /**
     * @param $viewId
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byView($viewId)
    {
        return static::getMultipleByClauses(['view_id' => $viewId]);
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
