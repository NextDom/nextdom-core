<?php
/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

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

namespace NextDom\Managers;

use NextDom\Helpers\DBHelper;
use NextDom\Model\Entity\EqReal;

/**
 * Class EqRealManager
 * @package NextDom\Managers
 */
class EqRealManager
{
    const CLASS_NAME = EqReal::class;
    const DB_CLASS_NAME = '`eqReal`';

    /**
     * @param $_logicalId
     * @param $_cat
     * @return array
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byLogicalId($_logicalId, $_cat)
    {
        $values = [
            'logicalId' => $_logicalId,
            'cat' => $_cat,
        ];
        $sql = 'SELECT id
                FROM ' . self::DB_CLASS_NAME . '
                WHERE logicalId = :logicalId
                  AND cat= : cat';
        $results = DBHelper::getAll($sql, $values);
        $return = [];
        foreach ($results as $result) {
            $return[] = self::byId($result['id']);
        }
        return $return;
    }

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
        $className = self::getClass($_id);
        return DBHelper::getOneObject($sql, $values, $className);
    }

    /**
     * @param $_id
     * @return string
     * @throws \NextDom\Exceptions\CoreException
     */
    protected static function getClass($_id)
    {
        if (get_called_class() != self::CLASS_NAME) {
            return get_called_class();
        }
        $values = [
            'id' => $_id,
        ];
        $sql = 'SELECT plugin, isEnable
                FROM `eqLogic`
                WHERE eqReal_id = :id';
        $result = DBHelper::getOne($sql, $values);
        $eqTyme_name = $result['plugin'];
        if ($result['isEnable'] == 0) {
            try {
                $plugin = null;
                if ($eqTyme_name != '') {
                    $plugin = PluginManager::byId($eqTyme_name);
                }
                if (!is_object($plugin) || $plugin->isActive() == 0) {
                    return self::CLASS_NAME;
                }
            } catch (\Exception $e) {
                return self::CLASS_NAME;
            }
        }
        if (class_exists($eqTyme_name)) {
            if (method_exists($eqTyme_name, 'getClassCmd')) {
                return $eqTyme_name::getClassCmd();
            }
        }
        if (class_exists($eqTyme_name . 'Real')) {
            return $eqTyme_name . 'Real';
        }
        return self::CLASS_NAME;
    }
}
