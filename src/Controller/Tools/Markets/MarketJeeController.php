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

namespace NextDom\Controller\Tools\Markets;

use NextDom\Controller\BaseController;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\UpdateManager;
use NextDom\Repo\RepoMarket;

/**
 * Class MarketJeeController
 * @package NextDom\Controller\Tools\Markets
 */
class MarketJeeController extends BaseController
{
    /**
     * Render market page
     *
     * @param array $pageData Page data
     *
     * @return string Content of market page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        global $NEXTDOM_INTERNAL_CONFIG;

        $searchLimit = 50;
        $repoId = Utils::init('repo', 'market');
        $repo = UpdateManager::repoById($repoId);
        if ($repo['enable'] == 0) {
            throw new CoreException(__('Le dépôt est inactif : ') . $repoId);
        }
        $type = Utils::init('type', null);
        $categorie = Utils::init('categorie', null);
        $name = Utils::init('name', null);
        $author = Utils::init('author', null);
        if ($name == 'false') {
            $name = null;
        }

        /* Lecture market */
        if ($author == null && $name === null && $categorie === null && Utils::init('certification', null) === null && Utils::init('cost', null) === null && $type == 'plugin') {
            $news = true;
            $markets = RepoMarket::byFilter(array(
                'status' => 'stable',
                'type' => 'plugin',
                'timeState' => 'popular',
            ));
            $markets2 = RepoMarket::byFilter(array(
                'status' => 'stable',
                'type' => 'plugin',
                'timeState' => 'newest',
            ));
            $markets = array_merge($markets, $markets2);
        } else {
            $news = false;
            $markets = RepoMarket::byFilter(array(
                'status' => null,
                'type' => $type,
                'categorie' => $categorie,
                'name' => $name,
                'author' => $author,
                'cost' => Utils::init('cost', null),
                'timeState' => Utils::init('timeState', null),
                'certification' => Utils::init('certification', null),
                'limit' => $searchLimit,
            ));
        }

        $pageData['marketObjectsByCategory'] = [];
        $categorieId = 0;
        $categorieObjet = '';
        foreach ($markets as $marketObject) {
            $categorieObjet = $marketObject->getCategorie();
            if ($categorieObjet == '') {
                $categorieObjet = 'Aucune';
            }
            if (!isset($pageData['marketObjectsByCategory'][$categorieObjet])) {
                $marketObjects = [];
                $marketObjects['key'] = $categorieObjet;
                $marketObjects['id'] = $categorieId;
                if (isset($NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categorieObjet])) {
                    $marketObjects['icon'] = $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categorieObjet]['icon'];
                    $marketObjects['name'] = ucfirst($NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categorieObjet]['name']);
                } else {
                    $marketObjects['icon'] = '';
                    $marketObjects['name'] = ucfirst($categorieObjet);
                }
                $marketObjects['objects'] = [];
                $pageData['marketObjectsByCategory'][$categorieObjet] = $marketObjects;
                $categorieId++;
            }
            $marketObjects2 = [];
            $marketObjects2['name'] = end(explode('.', $marketObject->getName()));
            $marketObjects2['author'] = $marketObject->getAuthor();
            $marketObjects2['category'] = $marketObject->getCategorie();
            $marketObjects2['id'] = $marketObject->getId();
            $marketObjects2['logicalId'] = $marketObject->getLogicalId();
            $update = \update::byLogicalId($marketObject->getLogicalId());
            if (!is_object($update)) {
                $marketObjects2['installed'] = 'install';
            } else {
                $marketObjects2['installed'] = 'notInstall';
            }
            $marketObjects2['cost'] = number_format($marketObject->getCost(), 2);
            $marketObjects2['realCost'] = number_format($marketObject->getRealCost(), 2);
            $marketObjects2['purchase'] = $marketObject->getPurchase();
            $marketObjects2['certification'] = $marketObject->getCertification();
            switch ($marketObjects2['certification']) {
                case 'Officiel':
                    $marketObjects2['certificationClass'] = 'official';
                    break;
                case 'Conseillé':
                    $marketObjects2['certificationClass'] = 'advised';
                    break;
                case 'Legacy':
                    $marketObjects2['certificationClass'] = 'legacy';
                    break;
                case 'Obsolète':
                    $marketObjects2['certificationClass'] = 'obsolete';
                    break;
                case 'Premium':
                    $marketObjects2['certificationClass'] = 'premium';
                    $marketObjects2['cost'] = -1;
                    break;
                case 'Partenaire':
                    $marketObjects2['certificationClass'] = 'partner';
                    break;
            }
            $marketObjects2['type'] = $marketObject->getType();
            if (strpos($marketObject->getName(), 'mobile.') !== false) {
                $marketObjects2['mobile'] = 'mobile';
            } else {
                $marketObjects2['mobile'] = 'desktop';
            }
            $marketObjects2['default_image'] = 'public/img/NextDom_NoPicture_Gray.png';
            switch ($marketObject->getType()) {
                case 'widget':
                    $marketObjects2['default_image'] = 'public/img/NextDom_Widget_Gray.png';
                    break;
                case 'plugin':
                    $marketObjects2['default_image'] = 'public/img/NextDom_Plugin_Gray.png';
                    break;
                case 'script':
                    $marketObjects2['default_image'] = 'public/img/NextDom_Script_Gray.png';
                    break;
            }
            $marketObjects2['urlPath'] = \config::byKey('market::address') . '/' . $marketObject->getImg('icon');
            $marketObjects2['note'] = $marketObject->getRating();
            $pageData['marketObjectsByCategory'][$categorieObjet]['objects'][$marketObjects2['name']] = $marketObjects2;
        }

        /* Memorisation recherche */
        $oldSearch = '';
        if ($name != '') {
            $oldSearch = $name;
        } else {
            if ($author != '') {
                $oldSearch = $author;
            }
        }

        /* Test user */
        try {
            RepoMarket::test();
            $userTest = true;
        } catch (\Exception $e) {
            $userTest = false;
        }

        /* Categories */
        $pageData['marketCategories'] = [];
        if ($type !== null && $type != 'plugin') {
            foreach (RepoMarket::distinctCategorie($type) as $id => $category) {
                if (trim($category) != '' && is_numeric($id)) {
                    $categories = [];
                    $categories['key'] = $category;
                    $categories['name'] = $category;
                    $pageData['marketCategories'][] = $categories;
                }
            }
        } else {
            foreach ($NEXTDOM_INTERNAL_CONFIG['plugin']['category'] as $key => $value) {
                $categories = [];
                $categories['key'] = $key;
                $categories['name'] = $value['name'];
                $pageData['marketCategories'][] = $categories;
            }
        }

        /* Types */
        $pageData['marketTypes'] = [];
        $types = [];
        $types['key'] = 'plugin';
        $types['name'] = 'Plugins';
        $pageData['marketTypes'][] = $types;
        $types = [];
        $types['key'] = 'widget';
        $types['name'] = 'Widgets';
        $pageData['marketTypes'][] = $types;
        $types = [];
        $types['key'] = 'script';
        $types['name'] = 'Scripts';
        $pageData['marketTypes'][] = $types;

        $pageData['marketNews'] = $news;
        $pageData['marketType'] = $type;
        $pageData['JS_VARS']['marketType'] = $type;
        $pageData['marketCategorie'] = $categorie;
        $pageData['marketName'] = $name;
        $pageData['marketAuthor'] = $author;
        $pageData['marketOldSearch'] = $oldSearch;
        $pageData['marketUser'] = \config::byKey('market::username');
        $pageData['marketUserTest'] = $userTest;
        $pageData['marketLimit'] = $searchLimit;
        $pageData['markets'] = $markets;

        $pageData['CSS_POOL'][] = '/public/css/pages/markets.css';
        $pageData['JS_END_POOL'][] = '/public/js/desktop/tools/markets/marketJee.js';

        return Render::getInstance()->get('/desktop/tools/markets/marketJee.html.twig', $pageData);
    }

}
