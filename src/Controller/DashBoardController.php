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

use NextDom\Helpers\Status;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\ScenarioManager;

/**
 * Description of toto
 *
 * @author luc
 */
class DashBoardController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }

    /**
     * Render dashboard
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of Dashboard V2 page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {

        $pageContent['JS_VARS']['nextdom_Welcome'] = \config::byKey('nextdom::Welcome');
        $pageContent['JS_VARS']['SEL_OBJECT_ID']   = Utils::init('object_id');
        $pageContent['JS_VARS']['SEL_CATEGORY']    = Utils::init('category', 'all');
        $pageContent['JS_VARS']['SEL_TAG']         = Utils::init('tag', 'all');
        $pageContent['JS_VARS']['SEL_SUMMARY']     = Utils::init('summary');

        if ($pageContent['JS_VARS']['SEL_OBJECT_ID'] == '') {
            $object = JeeObjectManager::byId($_SESSION['user']->getOptions('defaultDashboardObject'));
        } else {
            $object = JeeObjectManager::byId(Utils::init('object_id'));
        }

        if (!is_object($object)) {
            $object = JeeObjectManager::rootObject();
        }

        if (!is_object($object)) {
            throw new \Exception(\__('Aucun objet racine trouvé. Pour en créer un, allez dans dashboard -> <a href="/index.php?v=d&p=object">Liste objets et résumés</a>'));
        }
        $pageContent['JS_VARS']['rootObjectId'] = $object->getId();

        $pageContent['dashboardDisplayObjectByDefault']   = $_SESSION['user']->getOptions('displayObjetByDefault');
        $pageContent['dashboardDisplayScenarioByDefault'] = $_SESSION['user']->getOptions('displayScenarioByDefault');
        $pageContent['dashboardCategory']                 = $pageContent['JS_VARS']['SEL_CATEGORY'];
        $pageContent['dashboardTag']                      = $pageContent['JS_VARS']['SEL_TAG'];
        $pageContent['dashboardCategories']               = \nextdom::getConfiguration('eqLogic:category', true);
        $pageContent['dashboardTags']                     = EqLogicManager::getAllTags();
        $pageContent['dashboardObjectId']                 = $pageContent['JS_VARS']['SEL_OBJECT_ID'];
        $pageContent['dashboardObject']                   = $object;
        $pageContent['dashboardChildrenObjects']          = JeeObjectManager::buildTree($object);
        $pageContent['profilsUser']                       = $_SESSION['user'];

        if ($pageContent['dashboardDisplayScenarioByDefault'] == 1) {
            $pageContent['dashboardScenarios'] = ScenarioManager::all();
        }
        $pageContent['JS_POOL'][] = '/public/js/desktop/dashboard.js';
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/dashboard_events.js';
        // A remettre une fois mise sous forme de thème//
        $pageContent['JS_POOL'][]     = '/vendor/node_modules/isotope-layout/dist/isotope.pkgd.min.js';
        $pageContent['JS_POOL'][]     = '/assets/3rdparty/jquery.multi-column-select/multi-column-select.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/dashboard.html.twig', $pageContent);
    }

}
