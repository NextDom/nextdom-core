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

namespace NextDom\Controller;
 
use NextDom\Helpers\Utils;
use NextDom\Helpers\PagesController;
use NextDom\Helpers\Render;
use NextDom\Helpers\Status;

class ViewController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }
    
    /**
     * Render view page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of view page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {

        $pageContent['viewsList'] = \view::all();
        $pageContent['viewHideList'] = true;
        $pageContent['viewIsAdmin'] = Status::isConnectAdmin();
        $pageContent['viewDefault'] = $_SESSION['user']->getOptions('displayViewByDefault');
        $pageContent['viewNoControl'] = Utils::init('noControl');

        $currentView = null;
        if (Utils::init('view_id') == '') {

            if ($_SESSION['user']->getOptions('defaultDesktopView') != '') {
                $currentView = \view::byId($_SESSION['user']->getOptions('defaultDesktopView'));
            }

            if (!is_object($currentView)) {
                $currentView = $pageContent['viewsList'][0];
            }
        } else {
            $currentView = \view::byId(init('view_id'));

            if (!is_object($currentView)) {
                throw new \Exception('{{Vue inconnue. Vérifier l\'ID.}}');
            }
        }

        if (!is_object($currentView)) {
            throw new \Exception(__('Aucune vue n\'existe, cliquez <a href="index.php?v=d&p=view_edit">ici</a> pour en créer une.'));
        }
        $pageContent['viewCurrent'] = $currentView;

        if ($_SESSION['user']->getOptions('displayViewByDefault') == 1 && Utils::init('report') != 1) {
            $pageContent['viewHideList'] = false;
        }
        $pageContent['JS_VARS']['view_id'] = $currentView->getId();
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/view.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/view.html.twig', $pageContent);
    }
}
