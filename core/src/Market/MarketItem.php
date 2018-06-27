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

namespace NextDom\Market;

use NextDom\Helpers\DataStorage;
use NextDom\Managers\UpdateManager;

class MarketItem
{
    /**
     * @var int Temps de rafraichissement d'un dépôt
     */
    private $REFRESH_TIME_LIMIT = 86400;

    /**
     * @var string Identifiant du plugin
     */
    private $id;
    /**
     * @var string Nom du plugin sur GitHub
     */
    private $gitName;
    /**
     * @var string Utilisateur GitHub
     */
    private $gitId;
    /**
     * @var string Nom complet de son dépôt
     */
    private $fullName;
    /**
     * @var string Description
     */
    private $description;
    /**
     * @var string URL Git
     */
    private $url;
    /**
     * @var string Nom du plug
     */
    private $name;
    /**
     * @var string Auteur du plugin
     */
    private $author;
    /**
     * @var string Catégorie du plugin
     */
    private $category;
    /**
     * @var DataStorage Gestionnaire de base de données
     */
    private $dataStorage;
    /**
     * @var string Chemin de l'icône
     */
    private $iconPath;
    /**
     * @var string Branche par défaut
     */
    private $defaultBranch;
    /**
     * @var array Liste des branches
     */
    private $branchesList;
    /**
     * @var string Licence
     */
    private $licence;
    /**
     * @var string Lien vers la documentation
     */
    private $documentationLink;
    /**
     * @var string Lien vers le changelog
     */
    private $changelogLink;
    /**
     * @var string Nom de la source
     */
    private $sourceName;
    /**
     * @var array Données de Jeedom sur le plugin
     */
    private $updateData;
    /**
     * @var array Liste des captures
     */
    private $screenshots;

    /**
     * Constructeur initialisant les informations de base
     *
     * @param string $sourceName Nom de la source de l'élément
     */
    public function __construct($sourceName)
    {
        $this->dataStorage = new DataStorage('market');
        // TODO: A supprimer
        if (!$this->dataStorage->isDataTableExists()) {
            $this->dataStorage->createDataTable();
        }

        $this->sourceName = $sourceName;
        $this->iconPath = false;
    }

    /**
     * Créer un élément à partir des données d'un dépôt GitHub
     *
     * @param string $sourceName Nom de la source
     * @param string[] $repositoryInformations Informations du dépôt
     *
     * @return MarketItem Elément créé
     */
    public static function createFromGit($sourceName, $repositoryInformations)
    {
        $result = new MarketItem($sourceName);
        $result->initWithGlobalInformations($repositoryInformations);
        return $result;
    }

    /**
     * Créer un élément depuis le cache
     *
     * @param string $sourceName Nom de la source
     * @param string $fullName Nom complet
     *
     * @return MarketItem Elément créé
     */
    public static function createFromCache($sourceName, $fullName)
    {
        $result = new MarketItem($sourceName);
        $result->setFullName($fullName);
        $result->readCache();
        return $result;
    }

    /**
     * Créer un élément à partir des données d'un JSON
     *
     * @param string $sourceName Nom de la source
     * @param string[] $jsonData Données de l'élément
     *
     * @return MarketItem Elément créé
     */
    public static function createFromJson($sourceName, $jsonData)
    {
        $result = new MarketItem($sourceName);
        $result->initWithJsonInformations($jsonData);
        return $result;
    }

    /**
     * Lire les informations obtenus par GitHub
     *
     * @param string[] $repositoryInformations Informations de GitHub
     */
    public function initWithGlobalInformations($repositoryInformations)
    {

        if (\array_key_exists('name', $repositoryInformations)) $this->gitName = $repositoryInformations['name'];
        if (\array_key_exists('full_name', $repositoryInformations)) $this->fullName = $repositoryInformations['full_name'];
        if (\array_key_exists('html_url', $repositoryInformations)) $this->url = $repositoryInformations['html_url'];
        if (\array_key_exists('git_id', $repositoryInformations)) $this->gitId = $repositoryInformations['git_id'];
        if (\array_key_exists('description', $repositoryInformations)) $this->description = $repositoryInformations['description'];
        if (\array_key_exists('default_branch', $repositoryInformations)) $this->defaultBranch = $repositoryInformations['default_branch'];
    }

    /**
     * Ajouter les informations contenu dans le fichier info.json du plugin
     *
     * @param string[] $pluginInfo Contenu du fichier info.json
     */
    public function addPluginInformations($pluginInfo)
    {
        if (\array_key_exists('id', $pluginInfo)) $this->id = $pluginInfo['id'];
        if (\array_key_exists('name', $pluginInfo)) $this->name = $pluginInfo['name'];
        if (\array_key_exists('author', $pluginInfo)) $this->author = $pluginInfo['author'];
        if (\array_key_exists('category', $pluginInfo)) $this->category = $pluginInfo['category'];
        if (\array_key_exists('licence', $pluginInfo)) $this->licence = $pluginInfo['licence'];
        if (\array_key_exists('changelog', $pluginInfo)) $this->changelogLink = $pluginInfo['changelog'];
        if (\array_key_exists('documentation', $pluginInfo)) $this->documentationLink = $pluginInfo['documentation'];
        if (\array_key_exists('description', $pluginInfo) && $pluginInfo['description'] !== null && $pluginInfo['description'] !== '') {
            $this->description = $pluginInfo['description'];
        }
    }

    public function initWithJsonInformations($jsonInformations)
    {
        if (\array_key_exists('id', $jsonInformations)) $this->id = $jsonInformations['id'];
        if (\array_key_exists('repository', $jsonInformations)) $this->gitName = $jsonInformations['repository'];
        if (\array_key_exists('gitId', $jsonInformations)) {
            $this->gitId = $jsonInformations['gitId'];
            $this->fullName = $this->gitId . '/' . $this->gitName;
            $this->url = 'https://github.com/' . $this->gitId . '/' . $this->fullName;
        }
        if (\array_key_exists('name', $jsonInformations)) $this->name = $jsonInformations['name'];
        if (\array_key_exists('licence', $jsonInformations)) $this->licence = $jsonInformations['licence'];
        if (\array_key_exists('category', $jsonInformations)) $this->category = $jsonInformations['category'];
        if (\array_key_exists('documentation', $jsonInformations)) $this->documentationLink = $jsonInformations['documentation'];
        if (\array_key_exists('changelog', $jsonInformations)) $this->changelogLink = $jsonInformations['changelog'];
        if (\array_key_exists('author', $jsonInformations)) $this->author = $jsonInformations['author'];
        if (\array_key_exists('description', $jsonInformations)) $this->description = $jsonInformations['description'];
        if (\array_key_exists('defaultBranch', $jsonInformations)) $this->defaultBranch = $jsonInformations['defaultBranch'];
        if (\array_key_exists('branches', $jsonInformations)) $this->branchesList = $jsonInformations['branches'];
        if (\array_key_exists('screenshots', $jsonInformations)) $this->screenshots = $jsonInformations['screenshots'];
    }

    /**
     * Test si une mise à jour est nécessaire
     *
     * @param array $repositoryInformations Informations de GitHub
     *
     * @return bool True si une mise à jour est nécessaire
     */
    public function isNeedUpdate($repositoryInformations)
    {
        $result = true;
        $lastUpdate = $this->dataStorage->getRawData('repo_last_update_' . \str_replace('/', '_', $repositoryInformations['full_name']));
        if ($lastUpdate !== null) {
            if (\time() - $lastUpdate < $this->REFRESH_TIME_LIMIT) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Obtenir l'ensemble des informations dans un tableau associatif
     *
     * @return array Tableau des données
     */
    public function getDataInArray()
    {
        $dataArray = array();
        $dataArray['name'] = $this->name;
        $dataArray['gitName'] = $this->gitName;
        $dataArray['gitId'] = $this->gitId;
        $dataArray['fullName'] = $this->fullName;
        $dataArray['description'] = $this->description;
        $dataArray['url'] = $this->url;
        $dataArray['id'] = $this->id;
        $dataArray['author'] = $this->author;
        $dataArray['category'] = $this->category;
        $dataArray['iconPath'] = $this->iconPath;
        $dataArray['defaultBranch'] = $this->defaultBranch;
        $dataArray['branchesList'] = $this->branchesList;
        $dataArray['licence'] = $this->licence;
        $dataArray['sourceName'] = $this->sourceName;
        $dataArray['changelogLink'] = $this->changelogLink;
        $dataArray['documentationLink'] = $this->documentationLink;
        $dataArray['screenshots'] = $this->screenshots;

        $this->initUpdateData();
        $dataArray['installed'] = $this->isInstalled();
        $dataArray['installedBranchData'] = false;
        if ($dataArray['installed']) {
            $dataArray['installedBranchData'] = $this->getInstalledBranchData();
        }
        return $dataArray;
    }

    /**
     * Ecrire le fichier de cache au format JSON
     */
    public function writeCache()
    {
        $dataArray = $this->getDataInArray();
        unset($dataArray['installed']);
        unset($dataArray['installedBranchData']);
        $this->dataStorage->storeJsonData('repo_data_' . str_replace('/', '_', $this->fullName), $dataArray);
        $this->dataStorage->storeRawData('repo_last_update_' . str_replace('/', '_', $this->fullName), \time());
    }

    /**
     * Lire le fichier de cache
     *
     * @return bool True si la lecture a réussi
     */
    public function readCache()
    {
        $result = false;
        $jsonContent = $this->dataStorage->getJsonData('repo_data_' . str_replace('/', '_', $this->fullName));
        if ($jsonContent !== null) {
            if (\array_key_exists('name', $jsonContent)) $this->name = $jsonContent['name'];
            if (\array_key_exists('gitName', $jsonContent)) $this->gitName = $jsonContent['gitName'];
            if (\array_key_exists('gitId', $jsonContent)) $this->gitId = $jsonContent['gitId'];
            if (\array_key_exists('fullName', $jsonContent)) $this->fullName = $jsonContent['fullName'];
            if (\array_key_exists('description', $jsonContent)) $this->description = $jsonContent['description'];
            if (\array_key_exists('url', $jsonContent)) $this->url = $jsonContent['url'];
            if (\array_key_exists('id', $jsonContent)) $this->id = $jsonContent['id'];
            if (\array_key_exists('author', $jsonContent)) $this->author = $jsonContent['author'];
            if (\array_key_exists('category', $jsonContent)) $this->category = $jsonContent['category'];
            if (\array_key_exists('iconPath', $jsonContent)) $this->iconPath = $jsonContent['iconPath'];
            if (\array_key_exists('defaultBranch', $jsonContent)) $this->defaultBranch = $jsonContent['defaultBranch'];
            if (\array_key_exists('branchesList', $jsonContent)) $this->branchesList = $jsonContent['branchesList'];
            if (\array_key_exists('licence', $jsonContent)) $this->licence = $jsonContent['licence'];
            if (\array_key_exists('changelogLink', $jsonContent)) $this->changelogLink = $jsonContent['changelogLink'];
            if (\array_key_exists('documentationLink', $jsonContent)) $this->documentationLink = $jsonContent['documentationLink'];
            if (\array_key_exists('screenshots', $jsonContent)) $this->screenshots = $jsonContent['screenshots'];
            $result = true;
        }
        return $result;
    }

    /**
     * Met à jour les données de l'élement
     *
     * @return bool True si la mise à jour a été effectuée.
     */
    public function refresh()
    {
        $result = false;
        $infoJsonUrl = 'https://raw.githubusercontent.com/' . $this->fullName . '/' . $this->defaultBranch . '/plugin_info/info.json';
        $infoJson = DownloadManager::downloadContent($infoJsonUrl);
        if (strpos($infoJson, '404: Not Found') === false) {
            $pluginData = \json_decode($infoJson, true);
            if (\is_array($pluginData) && \array_key_exists('id', $pluginData)) {
                $this->addPluginInformations($pluginData);
                $this->downloadIcon();
                $this->branchesList = [];
                $this->writeCache();
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Télécharge l'icône du plugin
     */
    public function downloadIcon()
    {
        $iconFilename = \str_replace('/', '_', $this->fullName) . '.png';
        $iconUrl = 'https://raw.githubusercontent.com/' . $this->fullName . '/' . $this->defaultBranch . '/plugin_info/' . $this->id . '_icon.png';
        $targetPath = NEXTDOM_ROOT.'/market_cache/' . $iconFilename;
        DownloadManager::downloadBinary($iconUrl, $targetPath);
        if (\filesize($targetPath) < 100) {
            \unlink($targetPath);
            $this->iconPath = '/core/img/unknown_icon.png';
        } else {
            $this->iconPath = '/market_cache/' . $iconFilename;
        }
        $this->writeCache();
    }

    /**
     * Met à jour les données des branches
     *
     * @return bool True si les données ont été trouvées
     */
    public function downloadBranchesInformations()
    {
        $result = false;
        $baseGitRepoUrl = 'https://api.github.com/repos/' . $this->fullName . '/branches';
        $branches = DownloadManager::downloadContent($baseGitRepoUrl);
        if ($branches !== false) {
            $branches = \json_decode($branches, true);
            $this->branchesList = [];
            foreach ($branches as $branch) {
                if (\is_array($branch) && \array_key_exists('name', $branch)) {
                    $branchData = [];
                    $branchData['name'] = $branch['name'];
                    $branchData['hash'] = $branch['commit']['sha'];
                    array_push($this->branchesList, $branchData);
                }
            }
            $result = true;
        }
        return $result;
    }

    /**
     * Initialise les données de Jeedom sur le plugin
     */
    private function initUpdateData()
    {
        $this->updateData = UpdateManager::byLogicalId($this->id);
    }

    /**
     * Test si le plugin est installée
     *
     * @return bool True si le plugin est installée
     */
    public function isInstalled()
    {
        $result = false;
        if ($this->updateData !== false) {
            $result = true;
        }
        return $result;
    }

    private function getInstalledBranchData()
    {
        $result = false;
        if ($this->updateData !== false && $this->updateData !== null) {
            $configuration = $this->updateData->getConfiguration();
            if (\is_array($configuration) && \array_key_exists('version', $configuration)) {
                $result = [];
                $result['branch'] = $configuration['version'];
                $result['hash'] = $this->updateData->getLocalVersion();
                $result['needUpdate'] = false;
                $result['id'] = $this->updateData->getId();
                if ($this->updateData->getSource() === 'github') {
                    if ($this->updateData->getStatus() === 'update') {
                        $result['needUpdate'] = true;
                    } else {
                        foreach ($this->branchesList as $branch) {
                            if ($branch['name'] === $result['branch'] &&
                                $branch['hash'] !== $result['hash']) {
                                $result['needUpdate'] = true;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function updateBranchDataFromInstalled()
    {
        $this->initUpdateData();
        $installedBranch = $this->getInstalledBranchData();
        $added = false;
        for ($branchIndex = 0; $branchIndex < count($this->branchesList); ++$branchIndex) {
            if ($this->branchesList[$branchIndex]['name'] == $installedBranch['branch']) {
                $this->branchesList[$branchIndex]['hash'] = $installedBranch['hash'];
                $added = true;
            }
        }
        if ($added === false) {
            $branch = array();
            $branch['name'] = $installedBranch['branch'];
            $branch['hash'] = $installedBranch['hash'];
            array_push($this->branchesList, $branch);
        }
        $this->writeCache();
    }

    /**
     * Obtenir le nom du dépot.
     *
     * @return string Nom du dépot
     */
    public function getGitName()
    {
        return $this->gitName;
    }

    /**
     * Obtenir le nom complet
     *
     * @return string Nom complet
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Obtenir la description du dépot
     *
     * @return string Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Obtenir le lien
     *
     * @return string Lien
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Obtenir l'identifiant
     *
     * @return string Identifiant
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Obtenir l'auteur
     *
     * @return string Auteur
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Obtenir la catégorie
     *
     * @return string Catégorie
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Obtenir le nom
     *
     * @return string Nom
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Obtenir l'utilisateur GitHub
     *
     * @return string Utilisateur GitHub
     */
    public function getGitId()
    {
        return $this->gitId;
    }

    /**
     * Obtenir la liste des branches du plugin
     *
     * @return array Liste des branches
     */
    public function getBranchesList()
    {
        return $this->branchesList;
    }

    /**
     * Définir le nom du complet du dépôt
     *
     * @param string $fullName Nom complet
     *
     * @return MarketItem Instance de l'objet
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * @return string
     */
    public function getIconPath()
    {
        return $this->iconPath;
    }
}
