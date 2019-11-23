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
 */


namespace NextDom\Market;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DataStorage;

/**
 * Gestion des informations liées à GitHub
 */
class GitManager
{

    /**
     * @var string Utilisateur du dépot
     */
    private $gitId;

    /**
     * @var DataStorage Gestionnaire de base de données
     */
    private $dataStorage;

    /**
     *
     * @var string
     */
    private $githubApiDomain = 'https://api.github.com';

    /**
     * Constructeur du gestionnaire Git
     *
     * @param string $gitId Utilisateur du compte Git
     * @throws \Exception
     */
    public function __construct($gitId)
    {
        DownloadManager::init();
        $this->gitId = $gitId;
        $this->dataStorage = new DataStorage('nextdom_market');
    }

    /**
     * Met à jour la liste des dépôts
     *
     * @return bool True si l'opération a réussie
     *
     * @throws \Exception
     */
    public function updateRepositoriesList()
    {
        $result = false;
        $jsonList = $this->downloadRepositoriesList();
        if ($jsonList !== false) {
            $jsonAnswer = json_decode($jsonList, true);
            $dataToStore = [];
            foreach ($jsonAnswer as $repository) {
                $data = [];
                $data['name'] = $repository['name'];
                $data['full_name'] = $repository['full_name'];
                $data['description'] = $repository['description'];
                $data['html_url'] = $repository['html_url'];
                $data['git_id'] = $this->gitId;
                $data['default_branch'] = $repository['default_branch'];
                array_push($dataToStore, $data);
            }
            $this->dataStorage->storeRawData('repo_last_update_' . $this->gitId, time());
            $this->dataStorage->storeJsonData('repo_data_' . $this->gitId, $dataToStore);
            // Efface la liste des dépôts ignorés
            $this->saveIgnoreList([]);
            $result = true;
        }
        return $result;
    }

    /**
     * Télécharge la liste des dépôts au format JSON
     *
     * @return string|bool Données au format JSON ou False en cas d'échec
     * @throws \Exception
     */
    protected function downloadRepositoriesList()
    {
        $result = false;
        $content = DownloadManager::downloadContent($this->githubApiDomain . '/orgs/' . $this->gitId . '/repos?per_page=100');
        // Limite de l'API GitHub atteinte
        if (strstr($content, 'API rate limit exceeded')) {
            $content = DownloadManager::downloadContent($this->githubApiDomain . '/rate_limit');
            $gitHubLimitData = json_decode($content, true);
            $refreshDate = date('H:i', $gitHubLimitData['resources']['core']['reset']);
            throw new CoreException('Limite de l\'API GitHub atteinte. Le rafraichissement sera accessible à ' . $refreshDate);
        } elseif (strstr($content, 'Bad credentials')) {
            // Le token GitHub n'est pas bon
            throw new CoreException('Problème de Token GitHub');
        } else {
            // Test si c'est un dépôt d'organisation
            if (strstr($content, '"message":"Not Found"')) {
                // Test d'un téléchargement pour un utilisateur
                $content = DownloadManager::downloadContent($this->githubApiDomain . '/users/' . $this->gitId . '/repos?per_page=100');
                // Test si c'est un dépot d'utilisateur
                if (strstr($content, '"message":"Not Found"') || strlen($content) < 10) {
                    throw new CoreException('Le dépôt ' . $this->gitId . ' n\'existe pas.');
                } else {
                    $result = $content;
                }
            } else {
                $result = $content;
            }
        }
        return $result;
    }

    /**
     * Sauvegarder la liste des dépôts ignorés
     *
     * @param array $ignoreList Liste des dépôts ignorés
     */
    protected function saveIgnoreList($ignoreList)
    {
        $this->dataStorage->storeJsonData('repo_ignore_' . $this->gitId, $ignoreList);
    }

    /**
     * Mettre à jour les dépôts
     *
     * @param string $sourceName Nom de la source
     * @param array $repositoriesList Liste des dépots
     * @param bool $force Forcer les mises à jour
     * @throws \Exception
     */
    public function updateRepositories($sourceName, $repositoriesList, $force)
    {
        $ignoreList = $this->getIgnoreList();
        foreach ($repositoriesList as $repository) {
            $repositoryName = $repository['name'];
            $marketItem = MarketItem::createFromGit($sourceName, $repository);
            if (($force || $marketItem->isNeedUpdate($repository)) && !in_array($repositoryName, $ignoreList)) {
                if (!$marketItem->refresh()) {
                    array_push($ignoreList, $repositoryName);
                }
            }
        }
        $this->saveIgnoreList($ignoreList);
    }

    /**
     * Obtenir la liste des dépots ignorés
     *
     * @return array|mixed
     */
    protected function getIgnoreList()
    {
        $result = [];
        $jsonList = $this->dataStorage->getJsonData('repo_ignore_' . $this->gitId);
        if ($jsonList !== null) {
            $result = $jsonList;
        }
        return $result;
    }

    /**
     * Obtenir la liste des plugins
     *
     * @param string $sourceName Nom de la source
     *
     * @return array Liste des plugins
     */
    public function getItems($sourceName)
    {
        $result = [];
        $repositories = $this->getRepositoriesList();
        $ignoreList = $this->getIgnoreList();
        foreach ($repositories as $repository) {
            if (!in_array($repository['name'], $ignoreList)) {
                $marketItem = MarketItem::createFromCache($sourceName, $repository['full_name']);
                array_push($result, $marketItem);
            }
        }
        return $result;
    }

    /**
     * Lire le contenu du fichier contenant la liste des dépôts
     *
     * @return bool|array Tableau associatifs contenant les données ou false en cas d'échec
     */
    public function getRepositoriesList()
    {
        $result = false;
        $jsonStrList = $this->dataStorage->getJsonData('repo_data_' . $this->gitId);
        if ($jsonStrList !== null) {
            $result = $jsonStrList;
        }
        return $result;
    }

}
