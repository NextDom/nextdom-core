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
use NextDom\Managers\Parents\BasePlanManager;
use NextDom\Model\Entity\Plan3d;

/**
 * Class Plan3dManager
 * @package NextDom\Managers
 */
class Plan3dManager extends BasePlanManager
{
    /** @var string  */
    const CLASS_NAME = Plan3d::class;
    /** @var string  */
    const DB_CLASS_NAME = '`plan3d`';
    /** @var string  */
    const PLANHEADER_ID = 'plan3dHeader_id';

    /**
     * @param $_plan3dHeader_id
     * @return Plan3d|null
     * @throws \Exception
     */
    public static function byPlan3dHeaderId($_plan3dHeader_id)
    {
        return static::byPlanHeaderId($_plan3dHeader_id);
    }

    /**
     * @param $_name
     * @param $_plan3dHeader_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byName3dHeaderId($_name, $_plan3dHeader_id)
    {
        return static::getMultipleByClauses([
            'name' => $_name,
            'plan3dHeader_id' => $_plan3dHeader_id,
        ]);
    }

    /**
     * @param $_link_type
     * @param $_link_id
     * @param $_plan3dHeader_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byLinkTypeLinkId3dHeaderId($_link_type, $_link_id, $_plan3dHeader_id)
    {
        return static::byLinkTypeLinkIdPlanHeaderId($_link_type, $_link_id, $_plan3dHeader_id);
    }

    /**
     * @param $_link_type
     * @param $_link_id
     * @param $_plan3dHeader_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function removeByLinkTypeLinkId3dHeaderId($_link_type, $_link_id, $_plan3dHeader_id)
    {
        return static::removeByLinkTypeLinkIdPlanHeaderId($_link_type, $_link_id, $_plan3dHeader_id);
    }
}
