<?php

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 *
 * @Support <https://www.nextdom.org>
 * @Email   <admin@nextdom.org>
 * @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
 */

namespace NextDom\Controller\Modals;

use NextDom\Enums\AjaxParams;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PlanManager;
use NextDom\Managers\ViewManager;

/**
 * Class PlanConfigure
 * @package NextDom\Controller\Modals
 */
class PlanConfigure extends BaseAbstractModal
{
    /**
     * Render plan configure modal
     *
     * @return string
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function get(): string
    {

        $pageData = [];
        $pageData['planObject'] = PlanManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($pageData['planObject'])) {
            throw new CoreException('Impossible de trouver le design');
        }
        $pageData['planLink'] = $pageData['planObject']->getLink();
        $pageData['jeeObjects'] = JeeObjectManager::all();
        $pageData['views'] = ViewManager::all();
        $pageData['plans'] = PlanHeaderManager::all();
        Utils::sendVarToJS('id', $pageData['planObject']->getId());

        return Render::getInstance()->get('/modals/plan.configure.html.twig', $pageData);
    }
}
