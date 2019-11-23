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

/* This file is part of Nextdom Software.
 *
 * Nextdom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Nextdom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Nextdom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Market;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DataStorage;

/**
 * Class NextDomMarket
 * @package NextDom\Market
 */
class NextDomMarket
{
    /**
     * @var int Temps de rafraichissement de la liste des plugins
     */
    const REFRESH_TIME_LIMIT = 86400;

    /**
     * @var string Utilisateur Git des depôts
     */
    private $source;

    /**
     * @var DataStorage Gestionnaire de base de données
     */
    private $dataStorage;

    /**
     * Constructeur initialisant le gestionnaire de téléchargement
     *
     * @param array $source Nom de la source
     * @throws \Exception
     */
    public function __construct($source)
    {
        DownloadManager::init();
        $this->source = $source;
        $this->dataStorage = new DataStorage('market');
    }

    /**
     * Met à jour la liste des dépôts
     *
     * @param bool $force Forcer la mise à jour
     *
     * @return True si une mise à jour a été réalisée
     * @throws \Exception
     */
    public function refresh($force = false)
    {
        $result = false;
        if (DownloadManager::isConnected()) {
            if ($this->source['type'] == 'github') {
                $result = $this->refreshGitHub($force);
            } elseif ($this->source['type'] == 'json') {
                $result = $this->refreshJson($force);
            }
        } else {
            throw new CoreException('Pas de connection internet', 500);
        }
        return $result;
    }

    /**
     * Rafraichir une source GitHub
     *
     * @param bool $force Forcer la mise à jour
     * @return bool True si un rafraichissement a eu lieu
     * @throws \Exception
     */
    public function refreshGitHub(bool $force): bool
    {
        $result = false;
        $gitManager = new GitManager($this->source['data']);
        if ($force || $this->isUpdateNeeded($this->source['data'])) {
            $result = $gitManager->updateRepositoriesList();
        }
        $repositories = $gitManager->getRepositoriesList();
        if ($repositories !== false) {
            $gitManager->updateRepositories($this->source['name'], $repositories, $force);
            $result = true;
        }
        return $result;
    }

    /**
     * Test si une mise à jour de la liste des dépôts est nécessaire
     *
     * @param string $id Identifiant de la liste des dépôts
     *
     * @return bool True si une mise à jour est nécessaire
     */
    public function isUpdateNeeded($id): bool
    {
        $result = true;
        $lastUpdate = $this->dataStorage->getRawData('repo_last_update_' . $id);
        if ($lastUpdate !== null) {
            if (time() - $lastUpdate < self::REFRESH_TIME_LIMIT) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Rafraichier une source JSON
     *
     * @param bool $force Forcer la mise à jour
     *
     * @return bool True si un rafraichissement a eu lieu
     * @throws \Exception
     */
    public function refreshJson($force): bool
    {
        $result = false;
        $content = null;
        if ($force || $this->isUpdateNeeded($this->source['name'])) {
            $content = DownloadManager::downloadContent($this->source['data']);
            if ($content !== false) {
                $marketData = json_decode($content, true);
                $lastChange = $this->dataStorage->getRawData('repo_last_change_' . $this->source['name']);
                if ($force || $lastChange == null || $marketData['version'] > $lastChange) {
                    foreach ($marketData['plugins'] as $plugin) {
                        $marketItem = MarketItem::createFromJson($this->source['name'], $plugin);
                        $marketItem->writeCache();
                    }
                    $result = true;
                    $this->dataStorage->storeJsonData('repo_data_' . $this->source['name'], $marketData['plugins']);
                    $this->dataStorage->storeRawData('repo_last_change_' . $this->source['name'], $marketData['version']);
                }
                $this->dataStorage->storeRawData('repo_last_update_' . $this->source['name'], time());
            }
        }
        return $result;
    }

    /**
     * Obtenir la liste des éléments du dépot
     *
     * @return MarketItem[] Liste des éléments
     * @throws \Exception
     */
    public function getItems(): array
    {
        $result = [];
        if ($this->source['type'] == 'github') {
            $gitManager = new GitManager($this->source['data']);
            $result = $gitManager->getItems($this->source['name']);
        } elseif ($this->source['type'] == 'json') {
            $result = $this->getItemsFromJson();
        }
        return $result;
    }

    /**
     * Obtenir les éléments d'une source JSON
     *
     * @return MarketItem[] Liste des éléments
     */
    public function getItemsFromJson(): array
    {
        $result = [];
        $plugins = $this->dataStorage->getJsonData('repo_data_' . $this->source['name']);
        foreach ($plugins as $plugin) {
            if ($plugin['id'] !== 'AlternativeMarketForJeedom') {
                $marketItem = MarketItem::createFromCache($this->source['name'], $plugin['gitId'] . '/' . $plugin['repository']);
                array_push($result, $marketItem);
            }
        }
        return $result;
    }

    /**
     * Supprime les informations d'une source
     */
    public function remove()
    {
        $this->dataStorage->remove('repo_ignore_' . $this->source['name']);
        $this->dataStorage->remove('repo_last_change_' . $this->source['name'] . '%');
        $this->dataStorage->remove('repo_data_' . $this->source['name'] . '%');
        $this->dataStorage->remove('repo_last_update_' . $this->source['name'] . '%');
    }
}
