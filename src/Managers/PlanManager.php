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
use NextDom\Model\Entity\Plan;

/**
 * Class PlanManager
 * @package NextDom\Managers
 */
class PlanManager
{
    /**
     *
     */
    const CLASS_NAME = Plan::class;
    /**
     *
     */
    const DB_CLASS_NAME = '`plan`';

    /**
     * @param $_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byId($_id)
    {
        $values = [
            'id' => $_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE id = :id';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param $_planHeader_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byPlanHeaderId($_planHeader_id)
    {
        $values = [
            'planHeader_id' => $_planHeader_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE planHeader_id = :planHeader_id';
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param $_link_type
     * @param $_link_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byLinkTypeLinkId($_link_type, $_link_id)
    {
        $values = [
            'link_type' => $_link_type,
            'link_id' => $_link_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE link_type = :link_type
        AND link_id = :link_id';
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param $_link_type
     * @param $_link_id
     * @param $_planHeader_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byLinkTypeLinkIdPlanHedaerId($_link_type, $_link_id, $_planHeader_id)
    {
        $values = [
            'link_type' => $_link_type,
            'link_id' => $_link_id,
            'planHeader_id' => $_planHeader_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE link_type = :link_type
        AND link_id = :link_id
        AND planHeader_id = :planHeader_id';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param $_link_type
     * @param $_link_id
     * @param $_planHeader_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function removeByLinkTypeLinkIdPlanHedaerId($_link_type, $_link_id, $_planHeader_id)
    {
        $values = [
            'link_type' => $_link_type,
            'link_id' => $_link_id,
            'planHeader_id' => $_planHeader_id,
        ];
        $sql = 'DELETE FROM ' . self::DB_CLASS_NAME . '
        WHERE link_type = :link_type
        AND link_id = :link_id
        AND planHeader_id = :planHeader_id';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

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
     * @param $_search
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function searchByDisplay($_search)
    {
        $value = [
            'search' => '%' . $_search . '%',
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE display LIKE :search';
        return DBHelper::getAllObjects($sql, $value, self::CLASS_NAME);
    }

    /**
     * @param $_search
     * @param string $_not
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function searchByConfiguration($_search, $_not = '')
    {
        $value = [
            'search' => '%' . $_search . '%',
            'not' => $_not,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE configuration LIKE :search
        AND link_type != :not';
        return DBHelper::getAllObjects($sql, $value, self::CLASS_NAME);
    }
}
