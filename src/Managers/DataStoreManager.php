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

use NextDom\Managers\Parents\BaseManager;
use NextDom\Managers\Parents\CommonManager;
use NextDom\Model\Entity\DataStore;

/**
 * Class DataStoreManager
 * @package NextDom\Managers
 */
class DataStoreManager extends BaseManager
{
    use CommonManager;

    const CLASS_NAME = DataStore::class;
    const DB_CLASS_NAME = '`dataStore`';

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
        return static::getOneByClauses([
            'type' => $dataType,
            'link_id' => $linkId,
            'key' => $key
        ], 'key');
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
        $clauses = [
            'type' => $dataType,
        ];
        if ($linkId != '') {
            $clauses['link_id'] = $linkId;
        }
        return static::getMultipleByClauses($clauses);
    }
}
