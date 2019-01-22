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
use NextDom\Managers\UpdateManager;
use NextDom\Managers\AjaxManager;

class UpdateAdd extends BaseAbstractModal
{

    public function __construct()
    {
        parent::__construct();
        Status::isConnectedOrFail();
    }

    /**
     * Render update add modal
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

        $pageContent['repoListType'] = [];
        foreach (UpdateManager::listRepo() as $repoKey => $repoValue) {
            if ($repoValue['configuration'] === false) {
                continue;
            }
            if ($repoValue['scope']['plugin'] === false) {
                continue;
            }
            if (!isset($repoValue['configuration']['parameters_for_add'])) {
                continue;
            }
            if (ConfigManager::byKey($repoKey . '::enable') == 0) {
                continue;
            }
            $pageContent['repoListType'][$repoKey] = $repoValue['name'];
        }

        $pageContent['repoListConfiguration'] = [];
        
        foreach (UpdateManager::listRepo() as $repoKey => $repoValue) {
            if ($repoValue['configuration'] === false) {
                continue;
            }
            if ($repoValue['scope']['plugin'] === false) {
                continue;
            }
            if (!isset($repoValue['configuration']['parameters_for_add'])) {
                continue;
            }
            $pageContent['repoListConfiguration'][$repoKey] = $repoValue;
        }
        $pageContent['ajaxToken'] = AjaxManager::getToken();

        return $render->get('/modals/update.add.html.twig', $pageContent); 
    }

}
