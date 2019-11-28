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
use NextDom\Model\Entity\View;

/**
 * Class ViewManager
 * @package NextDom\Managers
 */
class ViewManager
{
    const DB_CLASS_NAME = '`view`';
    const CLASS_NAME = 'view';

    /**
     * @return View[]|null
     * @throws \Exception
     */
    public static function all()
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        ORDER BY `order`';
        return DBHelper::getAllObjects($sql, [], self::CLASS_NAME);
    }

    /**
     * @param $_id
     * @return View|null
     * @throws \Exception
     */
    public static function byId($_id)
    {
        $value = [
            'id' => $_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE id = :id';
        return DBHelper::getOneObject($sql, $value, self::CLASS_NAME);
    }

    /**
     * @param $_type
     * @param $_id
     * @return array
     * @throws \Exception
     */
    public static function searchByUse($_type, $_id)
    {
        $return = [];
        $viewDatas = ViewDataManager::byTypeLinkId($_type, $_id);
        $search = '#' . str_replace('cmd', '', $_type . $_id) . '#';
        $viewDatas = array_merge($viewDatas, ViewDataManager::searchByConfiguration($search));
        foreach ($viewDatas as $viewData) {
            $viewZone = $viewData->getviewZone();
            $view = $viewZone->getView();
            $return[$view->getId()] = $view;
        }
        return $return;
    }
}
