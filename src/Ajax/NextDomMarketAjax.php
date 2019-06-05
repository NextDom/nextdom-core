<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Ajax;

use NextDom\Enums\UserRight;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\Utils;
use NextDom\Market\DownloadManager;
use NextDom\Market\MarketItem;
use NextDom\Market\NextDomMarket;

/**
 * Class NextDomMarketAjax
 * @package NextDom\Ajax
 */
class NextDomMarketAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    /**
     * Actions de refraichissement
     *
     * @throws \Exception
     */
    public function refresh()
    {
        $params = Utils::init('params');
        $data = Utils::init('data');

        switch ($params) {
            case 'list':
                $result = $this->refreshList($data, false);
                break;
            case 'list-force':
                $result = $this->refreshList($data, true);
                break;
            case 'branch-hash':
                $result = $this->refreshBranchHash($data);
                break;
            default :
                $result = false;
        }
        AjaxHelper::success($result);
    }

    /**
     * Rafraichir la liste des dépôts.
     *
     * @param array $sources Liste des utilisateur GitHub.
     * @param bool $force Force la mise à jour.
     *
     * @return bool True si une mise à jour a été réalisée ou que la mise à jour n'est pas nécessaire.
     * @throws \Exception
     */
    private function refreshList($sources, $force)
    {
        $result = false;
        if (is_array($sources)) {
            $result = true;
            foreach ($sources as $source) {
                $market = new NextDomMarket($source);
                $market->refresh($force);
            }
        } else {
            throw new \Exception('Aucune source configurée');
        }
        return $result;
    }

    /**
     * Rafraichir le hash de la branch à partir des données de Jeedom.
     *
     * @return bool True si le rafraichissement à été effectué
     */
    private function refreshBranchHash(array $data)
    {
        $result = false;
        if (count($data) == 2) {
            $marketItem = MarketItem::createFromCache($data[0], $data[1]);
            $marketItem->updateBranchDataFromInstalled();
            $result = true;
        }
        return $result;
    }

    /**
     * Obtenir une information
     *
     * @throws \Exception
     */
    public function get()
    {
        $params = Utils::init('params');
        $data = Utils::init('data');

        $result = false;
        switch ($params) {
            case 'list':
                if (is_array($data)) {
                    $result = [];
                    $idList = [];
                    $showDuplicates = false;
                    foreach ($data as $source) {
                        $market = new NextDomMarket($source);
                        // Obtenir la liste complète
                        $items = $market->getItems();
                        foreach ($items as $item) {
                            // Affiche les doublons
                            if ($showDuplicates) {
                                array_push($result, $item->getDataInArray());
                            } else {
                                if (!\in_array($item->getId(), $idList)) {
                                    $itemData = $item->getDataInArray();
                                    if ($itemData['installed'] === true && $itemData['installedBranchData']['needUpdate'] === true) {
                                        \message::add('NextDom Market', 'Mise à jour disponible pour ' . $itemData['name'], null, null);
                                    }
                                    array_push($result, $itemData);
                                    array_push($idList, $item->getId());
                                }
                            }
                        }
                    }
                    // Tri par ordre alphabétique
                    \usort($result, function ($item1, $item2) {
                        return $item1['name'] > $item2['name'];
                    });
                }
                break;
            case 'branches':
                if (is_array($data)) {
                    DownloadManager::init();
                    $marketItem = MarketItem::createFromCache($data['sourceName'], $data['fullName']);
                    if ($marketItem->downloadBranchesInformations()) {
                        $result = $marketItem->getBranchesList();
                        // Sauvegarde la liste des branches téléchargées
                        $marketItem->writeCache();
                    }
                }
                break;
            case 'icon':
                if (is_array($data)) {
                    DownloadManager::init();
                    $marketItem = MarketItem::createFromCache($data['sourceName'], $data['fullName']);
                    $path = $marketItem->getIconPath();
                    if ($path !== false) {
                        $result = $path;
                    } else {
                        $marketItem->downloadIcon();
                        $result = $marketItem->getIconPath();
                    }
                }
                break;
            default :
                $result = false;
        }
        AjaxHelper::success($result);
    }
}