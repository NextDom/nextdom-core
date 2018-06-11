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

namespace NextDom\Market\Ajax;

use NextDom\Market\NextDomMarket;
use NextDom\Market\MarketItem;

/**
 * Analyseur des requêtes Ajax
 */
class MarketAjaxParser
{
    /**
     * Point d'entrée des requêtes Ajax
     *
     * @param string $action Action de la requête
     * @param string $params Paramètres de la requête
     * @param mixed $data Données de la requête
     *
     * @return array|bool Résultat
     * @throws \Exception
     */
    public static function parse($action, $params, $data)
    {
        switch ($action) {
            case 'refresh':
                $result = static::refresh($params, $data);
                break;
            case 'get':
                $result = static::get($params, $data);
                break;
            case 'save':
                $result = static::save($params, $data);
                break;
            case 'source':
                $result = static::source($params, $data);
                break;
            default :
                $result = false;
        }
        return $result;
    }

    /**
     * Actions de refraichissement
     *
     * @param string $params Type de rafraichissement
     * @param mixed $data Données en fonction du paramètre
     *
     * @return bool True si l'action a réussie
     *
     * @throws \Exception
     */
    public static function refresh($params, $data)
    {
        switch ($params) {
            case 'list':
                $result = static::refreshList($data, false);
                break;
            case 'list-force':
                $result = static::refreshList($data, true);
                break;
            case 'branch-hash':
                $result = static::refreshBranchHash($data);
                break;
            default :
                $result = false;
        }
        return $result;
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
    private static function refreshList($sources, $force)
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
    private static function refreshBranchHash(array $data)
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
     * @param string $params Identifiant de l'information
     * @param mixed $data Données en fonction du paramètre
     *
     * @return array|bool Information demandée
     */
    public static function get($params, $data)
    {
        $result = false;
        switch ($params) {
            case 'list':
                if (is_array($data)) {
                    $result = [];
                    $idList = [];
//                    $showDuplicates = \config::byKey('show-duplicates', 'AlternativeMarketForJeedom');
                    $showDuplicates = false;
                    ob_start();
                    var_dump($data);
                    error_log(strip_tags(ob_get_clean()));
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
                    MarketDownloadManager::init();
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
                    MarketDownloadManager::init();
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
        return $result;
    }

    /**
     * Gestion des utilisateurs GitHub
     *
     * @param string $params Type de modification
     * @param array $data Nom de l'utilisateur
     * @return bool True si une action a été effectuée
     */
    public static function source($params, array $data)
    {
        /*
        switch ($params) {
            case 'add':
                $source = new AlternativeMarketForJeedom();
                $source->setName($data['id']);
                $source->setLogicalId($data['id']);
                $source->setEqType_name('AlternativeMarketForJeedom');
                $source->setIsEnable(1);
                $source->setConfiguration('type', $data['type']);
                $source->setConfiguration('order', 888);
                $source->setConfiguration('data', $data['id']);
                $source->save();
                $result = true;
                break;
            case 'remove':
                $source = eqLogic::byLogicalId($data['id'], 'AlternativeMarketForJeedom');
                $source->remove();
                $sourceConfig = [];
                $sourceConfig['name'] = $data['id'];
                $market = new AmfjMarket($sourceConfig);
                $market->remove();
                $result = true;
                break;
            default :
                $result = false;
        }
        return $result;
        */
        return false;
    }

    /**
     * Sauvegarde de données
     *
     * @param string $params Type de modification
     * @param array $data Nom de l'utilisateur
     * @return bool True si une action a été effectuée
     */
    public static function save($params, array $data)
    {
        /*
        switch ($params) {
            case 'sources':
                foreach ($data as $source) {
                    $eqLogicSource = eqLogic::byId($source['id']);
                    $eqLogicSource->setIsEnable($source['enable']);
                    $eqLogicSource->save();
                }
                $result = true;
                break;
            default :
                $result = false;
        }
        return $result;
        */
        return true;
    }
}
