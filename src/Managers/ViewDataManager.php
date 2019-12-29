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
use NextDom\Model\Entity\ViewData;

/**
 * Class ViewDataManager
 * @package NextDom\Managers
 */
class ViewDataManager extends BaseManager
{
    use CommonManager;
    const DB_CLASS_NAME = '`viewData`';
    const CLASS_NAME = ViewData::class;

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
     * @param $_viewZone_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byViewZoneId($_viewZone_id)
    {
        return static::getMultipleByClauses([
            'viewZone_id' => $_viewZone_id
        ], 'order');
    }

    /**
     * @param $_search
     * @return ViewData[]|null
     * @throws \Exception
     */
    public static function searchByConfiguration($_search)
    {
        $clauses = [
            'search' => '%' . $_search . '%',
        ];
        return static::searchMultipleByClauses($clauses);
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
        return static::getMultipleByClauses([
            'type' => $_type,
            'link_id' => $_link_id
        ], 'order');
    }
}
