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
use NextDom\Model\Entity\PlanHeader;

/**
 * Class PlanHeaderManager
 * @package NextDom\Managers
 */
class PlanHeaderManager
{
    const CLASS_NAME = PlanHeader::class;
    const DB_CLASS_NAME = '`planHeader`';

    /**
     * @param $_id
     * @return PlanHeader|null
     * @throws \Exception
     */
    public static function byId($_id)
    {
        $values = [
            'id' => $_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE id=:id';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * @return PlanHeader[]|null
     * @throws \Exception
     */
    public static function all()
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME;
        return DBHelper::getAllObjects($sql, [], self::CLASS_NAME);
    }

    /**
     *
     * @param string $_type
     * @param string|int $_id
     * @return mixed
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function searchByUse($_type, $_id)
    {
        $return = [];
        $search = '#' . str_replace('cmd', '', $_type . $_id) . '#';
        $plans = array_merge(PlanManager::byLinkTypeLinkId($_type, $_id), PlanManager::searchByConfiguration($search, 'eqLogic'));
        foreach ($plans as $plan) {
            $planHeader = $plan->getPlanHeader();
            $return[$planHeader->getId()] = $planHeader;
        }
        return $return;
    }
}
