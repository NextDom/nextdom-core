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

namespace NextDom\Controller\Modal;

use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Managers\ConfigManager;
use NextDom\Helpers\Utils;
 
class CmdHistory extends BaseAbstractModal
{
    
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedOrFail();
    }


    /**
     * Render command history modal (scenario)
     *
     * @param Render $render Render engine
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render): string
    {

        $pageContent = [];
        $pageContent['dates'] = array(
            'start' => Utils::init('startDate', date('Y-m-d', strtotime(ConfigManager::byKey('history::defautShowPeriod') . ' ' . date('Y-m-d')))),
            'end' => Utils::init('endDate', date('Y-m-d')),
        );
        $pageContent['derive'] = Utils::init('derive', 0);
        $pageContent['step']   = Utils::init('step', 0);
        $pageContent['id']     = Utils::init('id');

        return $render->get('/modals/cmd.history.html.twig', $pageContent);
    }
}
