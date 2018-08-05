<?php
/* This file is part of NextDom.
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

namespace NextDom\Helpers;


use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\ScenarioManager;

class Controller
{
    const routesList = [
        'dashboard-v2' => 'dashboardV2Page'
    ];

    public static function getRoute(string $page) {
        $route = null;
        if (array_key_exists($page, self::routesList)) {
            $route = self::routesList[$page];
        }
        return $route;
    }

    /**
     * @param Render $
     * @param array $pageContent
     * @return string
     */
    public static function dashboardV2Page(Render $render, array &$pageContent): string {
        Status::isConnectedAdminOrFail();
        $pageContent['JS_VARS']['SEL_OBJECT_ID'] = Utils::init('object_id');
        $pageContent['JS_VARS']['SEL_CATEGORY'] = Utils::init('category', 'all');
        $pageContent['JS_VARS']['SEL_TAG'] = Utils::init('tag', 'all');
        $pageContent['JS_VARS']['SEL_SUMMARY'] = Utils::init('summary');

        if ($pageContent['JS_VARS']['SEL_OBJECT_ID'] == '') {
            $object = JeeObjectManager::byId($_SESSION['user']->getOptions('defaultDashboardObject'));
        } else {
            $object = JeeObjectManager::byId(Utils::init('object_id'));
        }
        if (!is_object($object)) {
            $object = JeeObjectManager::rootObject();
        }
        if (!is_object($object)) {
            throw new \Exception(__('Aucun objet racine trouvé. Pour en créer un, allez dans Outils -> Objets.<br/> Si vous ne savez pas quoi faire ou que c\'est la première fois que vous utilisez Jeedom, n\'hésitez pas à consulter cette <a href="https://jeedom.github.io/documentation/premiers-pas/fr_FR/index" target="_blank">page</a> et celle-là si vous avez un pack : <a href="https://jeedom.com/start" target="_blank">page</a>'));
        }
        $pageContent['JS_VARS']['rootObjectId'] = $object->getId();

        $pageContent['dashboardDisplayObjectByDefault'] = $_SESSION['user']->getOptions('displayObjetByDefault');
        $pageContent['dashboardDisplayScenarioByDefault'] = $_SESSION['user']->getOptions('displayScenarioByDefault');
        $pageContent['dashboardCategory'] = $pageContent['JS_VARS']['SEL_CATEGORY'];
        $pageContent['dashboardTag'] = $pageContent['JS_VARS']['SEL_TAG'];
        $pageContent['dashboardCategories'] = \nextdom::getConfiguration('eqLogic:category', true);
        $pageContent['dashboardTags'] = EqLogicManager::getAllTags();
        $pageContent['dashboardObjectId'] = $pageContent['JS_VARS']['SEL_OBJECT_ID'];
        $pageContent['dashboardObject'] = $object;
        $pageContent['dashboardChildrenObjects'] = JeeObjectManager::buildTree($object);
        if ($pageContent['dashboardDisplayScenarioByDefault'] == 1) {
            $pageContent['dashboardScenarios'] = ScenarioManager::all();
        }
        $pageContent['JS_POOL'][] = '/desktop/js/dashboard.js';
        $pageContent['JS_POOL'][] = '/desktop/js/dashboard-v2.js';
        $pageContent['JS_POOL'][] = '/3rdparty/jquery.isotope/isotope.pkgd.min.js';
        $pageContent['JS_POOL'][] = '/3rdparty/jquery.multi-column-select/multi-column-select.js';

        return $render->get('/desktop/dashboard-v2.html.twig', $pageContent);
    }
}