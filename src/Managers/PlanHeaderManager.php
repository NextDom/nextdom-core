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

use NextDom\Enums\NextDomFolder;
use NextDom\Enums\NextDomObj;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Managers\Parents\BaseManager;
use NextDom\Managers\Parents\CommonManager;
use NextDom\Model\Entity\PlanHeader;

/**
 * Class PlanHeaderManager
 * @package NextDom\Managers
 */
class PlanHeaderManager extends BaseManager
{
    use CommonManager;
    const CLASS_NAME = PlanHeader::class;
    const DB_CLASS_NAME = '`planHeader`';

    /**
     * @return PlanHeader[]|null
     * @throws \Exception
     */
    public static function all()
    {
        return static::getAll();
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

    /**
     * Clean plan images
     *
     * @param int $planHeaderId
     */
    public static function cleanPlanImageFolder(int $planHeaderId) {
        $filesToClean = FileSystemHelper::ls(NextDomFolder::PLAN_IMAGE, NextDomObj::PLAN_HEADER . $planHeaderId . '*');
        foreach ($filesToClean as $fileToClean) {
            unlink(NextDomFolder::PLAN_IMAGE . $fileToClean);
        }
    }

}
