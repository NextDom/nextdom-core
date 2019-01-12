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

namespace NextDom\Controller\Modale;

use NextDom\Helpers\Status;
use NextDom\Helpers\Render;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\ConfigManager;

class ReportBug extends BaseAbstractModale
{

    public function __construct()
    {
        parent::__construct();
        Status::isConnectedOrFail();
    }
    /**
     * Render report bug modal
     *
     * @param Render $render Render engine
     *
     * @throws CoreException
     */
    public function get(Render $render): string
    {
        if (ConfigManager::byKey('market::address') == '') {
            throw new CoreException(__('Aucune adresse pour le market n\'est renseignée'));
        }
        if (ConfigManager::byKey('market::apikey') == '' && ConfigManager::byKey('market::username') == '') {
            throw new CoreException(__('Aucun compte market n\'est renseigné. Veuillez vous enregistrer sur le market, puis renseignez vos identifiants dans') . ConfigManager::byKey('product_name') . __('avant d\'ouvrir un ticket'));
        }
        return $render->get('/modals/report.bug.html.twig');
    }
}
