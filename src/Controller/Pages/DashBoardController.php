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

namespace NextDom\Controller\Pages;

use NextDom\Controller\BaseController;
use NextDom\Enums\AjaxParams;
use NextDom\Enums\Common;
use NextDom\Enums\NextDomObj;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\UserManager;

/**
 * Class DashboardController
 * @package NextDom\Controller
 */
class DashBoardController extends BaseController
{
    /**
     * Render dashboard
     *
     * @param array $pageData Page data
     *
     * @return string Content of Dashboard V2 page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $objectIdFromUrl = Utils::init(AjaxParams::OBJECT_ID, '');
        $defaultDashboardObjectId = '';
        $currentJeeObjectId = '';
        $pageData['JS_VARS']['nextdom_Welcome'] = ConfigManager::byKey('nextdom::Welcome');
        $pageData['JS_VARS']['SEL_CATEGORY'] = Utils::init(AjaxParams::CATEGORY, 'all');
        $pageData['JS_VARS']['SEL_TAG'] = Utils::init(AjaxParams::TAG, 'all');
        $pageData['JS_VARS']['SEL_SUMMARY'] = Utils::init(AjaxParams::SUMMARY);

        $defaultDashboardObjectName = UserManager::getStoredUser()->getOptions('defaultDashboardObject');
        $defaultDashboardObject = JeeObjectManager::byId($defaultDashboardObjectName);
        if (!empty($defaultDashboardObject)) {
            $defaultDashboardObjectId = $defaultDashboardObject->getId();
        }

        if ($objectIdFromUrl != '') {
            $currentJeeObject = JeeObjectManager::byId($objectIdFromUrl);
        } else {
            if ($defaultDashboardObjectId != "") {
                $currentJeeObject = JeeObjectManager::byId($defaultDashboardObjectId);
            } else {
                $currentJeeObject = JeeObjectManager::getRootObjects();
            }
        }
        if (!empty($currentJeeObject)) {
            $currentJeeObjectId = $currentJeeObject->getId();
        } else {
            throw new CoreException(__('Aucun objet racine trouvé. Pour en créer un, allez dans dashboard -> <a href="/index.php?v=d&p=object">Liste objets et résumés</a>'));
        }

        $pageData['JS_VARS']['SEL_OBJECT_ID'] = $currentJeeObjectId;
        $pageData['JS_VARS']['rootObjectId'] = $currentJeeObjectId;
        $pageData['JS_VARS']['serverTZoffsetMin'] = Utils::getTZoffsetMin();

        $pageData['dashboardCategory'] = Utils::init(AjaxParams::CATEGORY, Common::ALL);
        $pageData['dashboardSummary'] = Utils::init(AjaxParams::SUMMARY, Common::ALL);
        $pageData['dashboardCategories'] = NextDomHelper::getConfiguration('eqLogic:category', true);
        $pageData['dashboardDefaultObjectId'] = $defaultDashboardObjectId;
        $pageData['dashboardObjectId'] = $currentJeeObjectId;
        $pageData['dashboardObject'] = $currentJeeObject;
        $pageData['dashboardObjectParentNumber'] = $currentJeeObject->parentNumber();
        $pageData['dashboardObjectListMenu'] = self::getObjectsListMenu($currentJeeObjectId);
        $pageData['dashboardChildrenObjects'] = JeeObjectManager::buildTree($currentJeeObject);

        $pageData['JS_POOL'][] = '/public/js/desktop/pages/dashboard.js';
        $pageData['JS_END_POOL'][] = '/public/js/desktop/pages/dashboard_events.js';
        // A remettre une fois mise sous forme de thème
        $pageData['JS_POOL'][] = '/vendor/node_modules/isotope-layout/dist/isotope.pkgd.min.js';
        $pageData['JS_POOL'][] = '/assets/3rdparty/jquery.multi-column-select/multi-column-select.js';

        return Render::getInstance()->get('/desktop/pages/dashboard.html.twig', $pageData);
    }

    /**
     * Get layers
     * @param int $currentObjectId
     * @return array
     * @throws \Exception
     */
    private static function getObjectsListMenu($currentObjectId)
    {
        if ($currentObjectId === '') {
            return [];
        }
        $currentObject = JeeObjectManager::byId($currentObjectId);

        // Get parents
        $parentObjects = [];
        $parentObjects[] = $currentObject;
        $father = $currentObject->getFather();
        while ($father !== null) {
            $parentObjects[] = $father;
            $father = $father->getFather();
        }
        $parentObjects = array_reverse($parentObjects);

        // Get all layers before current object
        $result = [];
        foreach ($parentObjects as $selectedLayerObject) {
            $layerResult = [];
            if ($selectedLayerObject->getFather() === null) {
                $layer = JeeObjectManager::getRootObjects(true);
            } else {
                $layer = $selectedLayerObject->getFather()->getChild();
            }
            foreach ($layer as $item) {
                $itemData = [];
                $itemData[NextDomObj::JEE_OBJECT] = $item;
                $itemData[Common::ACTIVE] = false;
                if ($item->getId() == $selectedLayerObject->getId()) {
                    $itemData[Common::ACTIVE] = true;
                }
                $layerResult[] = $itemData;
            }
            $result[] = $layerResult;
        }
        // Add children layer
        $children = $currentObject->getChild(true);
        $childrenLayer = [];
        foreach ($children as $item) {
            $itemData = [];
            $itemData[NextDomObj::JEE_OBJECT] = $item;
            $itemData[Common::ACTIVE] = false;
            $childrenLayer[] = $itemData;
        }
        $result[] = $childrenLayer;
        return $result;
    }
}
