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

use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;

/**
 * Class CmdHistory
 * @package NextDom\Controller\Modals
 */
class CmdHistory extends BaseAbstractModal
{
    /**
     * Render command history modal (scenario)
     *
     * @return string
     * @throws \Exception
     */
    public static function get(): string
    {
        $pageData = [];
        $pageData['dates'] = array(
            'start' => Utils::init('startDate', date('Y-m-d', strtotime(ConfigManager::byKey('history::defautShowPeriod') . ' ' . date('Y-m-d')))),
            'end' => Utils::init('endDate', date('Y-m-d')),
        );
        $pageData['derive'] = Utils::init('derive', 0);
        $pageData['step'] = Utils::init('step', 0);
        $pageData['id'] = Utils::init('id');
        Utils::sendVarsToJS(['historyId' => Utils::init('id')]);

        return Render::getInstance()->get('/modals/cmd.history.html.twig', $pageData);
    }
}
