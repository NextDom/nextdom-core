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
 
use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Helpers\Utils;
use NextDom\Exceptions\CoreException;

class ViewConfigure extends BaseAbstractModale
{
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedOrFail();
    }
 
     /**
     * Render view configure modal
     *
     * @param Render $render Render engine
     *
     * @throws \NextDom\Exceptions\CoreException
     */
    public function get(\Render $render): string
    {
        $view = \view::byId(init('view_id'));
        if (!is_object($view)) {
            throw new CoreException('Impossible de trouver la vue');
        }
        Utils::sendVarsToJS(['id' => $view->getId(), 'view' => \utils::o2a($view)]);

        return $render->get('/modals/view.configure.html.twig');
    }

}