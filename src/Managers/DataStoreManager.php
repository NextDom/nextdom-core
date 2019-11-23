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
use NextDom\Model\Entity\DataStore;

/**
 * Class DataStoreManager
 * @package NextDom\Managers
 */
class DataStoreManager
{
    const CLASS_NAME = 'dataStore';
    const DB_CLASS_NAME = '`dataStore`';

    /**
     * Get stored data by his Id
     *
     * @param mixed $id Stored data Id
     * @return mixed Store data
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        $values = [
            'id' => $id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id=:id';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * Get stored data by type, linkId and key
     *
     * Ordered by $key
     *
     * @param mixed $dataType
     * @param mixed $linkId
     * @param mixed $key
     *
     * @return mixed
     * @throws \Exception
     */
    public static function byTypeLinkIdKey($dataType, $linkId, $key)
    {
        $values = [
            'type' => $dataType,
            'link_id' => $linkId,
            'key' => $key,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE `type` = :type
                    AND `link_id` = :link_id
                    AND `key` = :key
                ORDER BY `key`';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * Remove stored data by type and linkId
     *
     * @param $dataType
     * @param $linkId
     *
     * @return bool
     * @throws \Exception
     */
    public static function removeByTypeLinkId($dataType, $linkId)
    {
        $datastores = self::byTypeLinkId($dataType, $linkId);
        foreach ($datastores as $datastore) {
            $datastore->remove();
        }
        return true;
    }

    /**
     * Get stored data by type and linkId
     *
     * Ordered by $key
     *
     * @param mixed $dataType
     * @param mixed $linkId
     * @return DataStore[]|null
     * @throws \Exception
     */
    public static function byTypeLinkId($dataType, $linkId = '')
    {
        $values = [
            'type' => $dataType,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE type=:type';
        if ($linkId != '') {
            $values['link_id'] = $linkId;
            $sql .= ' AND link_id = :link_id';
        }
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }
}
