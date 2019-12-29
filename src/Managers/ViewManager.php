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

use NextDom\Managers\Parents\BaseManager;
use NextDom\Managers\Parents\CommonManager;
use NextDom\Model\Entity\View;

/**
 * Class ViewManager
 * @package NextDom\Managers
 */
class ViewManager extends BaseManager
{
    use CommonManager;
    const DB_CLASS_NAME = '`view`';
    const CLASS_NAME = View::class;

    /**
     * @return View[]|null
     * @throws \Exception
     */
    public static function all()
    {
        return static::getAllOrdered('order');
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
