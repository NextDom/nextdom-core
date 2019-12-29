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

namespace NextDom\Managers\Parents;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Model\Entity\Parents\BaseModel;

/**
 * Base planmanager with commons functions
 *
 * @package NextDom\Managers
 */
abstract class BasePlanManager extends BaseManager
{
    const PLANHEADER_ID = '';

    use CommonManager;

    /**
     * Get by plan header
     *
     * @param $planHeaderId
     *
     * @return mixed|null
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function byPlanHeaderId($planHeaderId)
    {
        return static::getMultipleByClauses([static::PLANHEADER_ID => $planHeaderId]);
    }

    /**
     * Get plan filtered by link type, link id and plan header
     * @param $linkType
     * @param $linkId
     * @param $planHeaderId
     * @return mixed|null
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function byLinkTypeLinkIdPlanHeaderId($linkType, $linkId, $planHeaderId)
    {
        return static::getMultipleByClauses([
            'link_type' => $linkType,
            'link_id' => $linkId,
            static::PLANHEADER_ID => $planHeaderId
        ]);
    }

    /**
     * @param $linkType
     * @param $linkId
     * @param $planHeaderId
     * @return mixed|null
     * @throws CoreException
     */
    public static function removeByLinkTypeLinkIdPlanHeaderId($linkType, $linkId, $planHeaderId)
    {
        $params = [
            'link_type' => $linkType,
            'link_id' => $linkId,
            'planHeader_id' => $planHeaderId
        ];
        $sql = 'DELETE FROM ' . self::DB_CLASS_NAME . '
                WHERE `link_type` = :link_type
                AND `link_id` = :link_id
                AND `' . static::PLANHEADER_ID . '` = :planHeader_id';
        return DBHelper::getOneObject($sql, $params, self::CLASS_NAME);
    }

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
     * @param $_link_type
     * @param $_link_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byLinkTypeLinkId($_link_type, $_link_id)
    {
        return static::getMultipleByClauses([
            'link_type' => $_link_type,
            'link_id' => $_link_id,
        ]);
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
        $sql = static::getBaseSQL() . '
                WHERE `display` LIKE :search';
        return DBHelper::getAllObjects($sql, $value, static::CLASS_NAME);
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
        $sql = static::getBaseSQL() . '
                WHERE `configuration` LIKE :search
                AND `link_type` != :not';
        return DBHelper::getAllObjects($sql, $value, static::CLASS_NAME);
    }
}