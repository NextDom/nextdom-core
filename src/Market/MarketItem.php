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

use NextDom\Helpers\DataStorage;
use NextDom\Managers\UpdateManager;

/**
 * Class MarketItem
 * @package NextDom\Market
 */
class MarketItem
{
    /**
     * @var int Refresh time of a deposit
     */
    private $REFRESH_TIME_LIMIT = 86400;

    /**
     * @var string Plugin ID
     */
    private $id;
    /**
     * @var string Plugin name on GitHub
     */
    private $gitName;
    /**
     * @var string User GitHub
     */
    private $gitId;
    /**
     * @var string Full name of his deposit
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
     * @var string Name of plugin
     */
    private $name;
    /**
     * @var string Plugin author
     */
    private $author;
    /**
     * @var string Plugin category
     */
    private $category;
    /**
     * @var DataStorage Database Manager
     */
    private $dataStorage;
    /**
     * @var string Icon path
     */
    private $iconPath;
    /**
     * @var string Default branch
     */
    private $defaultBranch;
    /**
     * @var array List of branches
     */
    private $branchesList;
    /**
     * @var string Licence
     */
    private $licence;
    /**
     * @var string Link to the documentation
     */
    private $documentationLink;
    /**
     * @var string Link to the changelog
     */
    private $changelogLink;
    /**
     * @var string Name of the source
     */
    private $sourceName;
    /**
     * @var array Jeedom data on the plugin
     */
    private $updateData;
    /**
     * @var array Captures list
     */
    private $screenshots;

    /**
     * Builder initializing basic information
     *
     * @param string $sourceName Name of the source of the element
     */
    public function __construct($sourceName)
    {
        $this->dataStorage = new DataStorage('market');
        // @TODO: A supprimer
        if (!$this->dataStorage->isDataTableExists()) {
            $this->dataStorage->createDataTable();
        }

        $this->sourceName = $sourceName;
        $this->iconPath = false;
    }

    /**
     * Create an element from the data of a GitHub repository
     *
     * @param string $sourceName Create an element from the data of a GitHub repository
     * @param string[] $repositoryInformations Deposit Information
     *
     * @return MarketItem Element created
     */
    public static function createFromGit(string $sourceName, $repositoryInformations)
    {
        $result = new self($sourceName);
        $result->initWithGlobalInformations($repositoryInformations);
        return $result;
    }

    /**
     * Lire les informations obtenus par GitHub
     *
     * @param string[] $repositoryInformations Informations de GitHub
     */
    public function initWithGlobalInformations($repositoryInformations)
    {

        if (array_key_exists('name', $repositoryInformations)) $this->gitName = $repositoryInformations['name'];
        if (array_key_exists('full_name', $repositoryInformations)) $this->fullName = $repositoryInformations['full_name'];
        if (array_key_exists('html_url', $repositoryInformations)) $this->url = $repositoryInformations['html_url'];
        if (array_key_exists('git_id', $repositoryInformations)) $this->gitId = $repositoryInformations['git_id'];
        if (array_key_exists('description', $repositoryInformations)) $this->description = $repositoryInformations['description'];
        if (array_key_exists('default_branch', $repositoryInformations)) $this->defaultBranch = $repositoryInformations['default_branch'];
    }

    /**
     * Create an item from the cache
     *
     * @param string $sourceName Name of the source
     * @param string $fullName Full Name
     *
     * @return MarketItem Element created
     */
    public static function createFromCache(string $sourceName, string $fullName)
    {
        $result = (new self($sourceName))
            ->setFullName($fullName);
        $result->readCache();
        return $result;
    }

    /**
     * Read the cache file
     *
     * @return bool True if the reading was successful
     */
    public function readCache(): bool
    {
        $name = sprintf("repo_data_%s", str_replace("/", "_", $this->fullName));
        $json = $this->dataStorage->getJsonData($name);
        $attrs = ["name", "gitName", "gitId", "fullName",
            "description", "url", "id", "author",
            "category", "iconPath", "defaultBranch", "branchesList",
            "licence", "changelogLink", "documentationLink", "screenshots"];
        if ($json === null) {
            return false;
        }

        foreach ($attrs as $c_attr) {
            if (true === array_key_exists($c_attr, $json)) {
                $this->$c_attr = $json[$c_attr];
            }
        }

        if (false !== $this->iconPath) {
            $path = sprintf("%s/%s", NEXTDOM_ROOT, $this->iconPath);
            if (false === file_exists($path)) {
                $this->iconPath = false;
            }
        }
        return true;
    }

    /**
     * Create an element from data in a JSON
     *
     * @param string $sourceName Name of the source
     * @param string[] $jsonData Element data
     *
     * @return MarketItem Elément créé
     */
    public static function createFromJson(string $sourceName, $jsonData)
    {
        $result = new self($sourceName);
        $result->initWithJsonInformations($jsonData);
        return $result;
    }

    /**
     * @param $jsonInformations
     */
    public function initWithJsonInformations($jsonInformations)
    {
        if (array_key_exists('id', $jsonInformations)) $this->id = $jsonInformations['id'];
        if (array_key_exists('repository', $jsonInformations)) $this->gitName = $jsonInformations['repository'];
        if (array_key_exists('gitId', $jsonInformations)) {
            $this->gitId = $jsonInformations['gitId'];
            $this->fullName = $this->gitId . '/' . $this->gitName;
            $this->url = 'https://github.com/' . $this->gitId . '/' . $this->fullName;
        }
        if (array_key_exists('name', $jsonInformations)) $this->name = $jsonInformations['name'];
        if (array_key_exists('licence', $jsonInformations)) $this->licence = $jsonInformations['licence'];
        if (array_key_exists('category', $jsonInformations)) $this->category = $jsonInformations['category'];
        if (array_key_exists('documentation', $jsonInformations)) $this->documentationLink = $jsonInformations['documentation'];
        if (array_key_exists('changelog', $jsonInformations)) $this->changelogLink = $jsonInformations['changelog'];
        if (array_key_exists('author', $jsonInformations)) $this->author = $jsonInformations['author'];
        if (array_key_exists('description', $jsonInformations)) $this->description = $jsonInformations['description'];
        if (array_key_exists('defaultBranch', $jsonInformations)) $this->defaultBranch = $jsonInformations['defaultBranch'];
        if (array_key_exists('branches', $jsonInformations)) $this->branchesList = $jsonInformations['branches'];
        if (array_key_exists('screenshots', $jsonInformations)) $this->screenshots = $jsonInformations['screenshots'];
    }

    /**
     * Test if an update is needed
     *
     * @param array $repositoryInformations Informations from GitHub
     *
     * @return bool True if an update is needed
     */
    public function isNeedUpdate(array $repositoryInformations): bool
    {
        $result = true;
        $lastUpdate = $this->dataStorage->getRawData('repo_last_update_' . str_replace('/', '_', $repositoryInformations['full_name']));
        if ($lastUpdate !== null) {
            if (time() - $lastUpdate < $this->REFRESH_TIME_LIMIT) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Updates the data of the element
     *
     * @return bool True if the update was done.
     * @throws \Exception
     */
    public function refresh(): bool
    {
        $result = false;
        $infoJsonUrl = 'https://raw.githubusercontent.com/' . $this->fullName . '/' . $this->defaultBranch . '/plugin_info/info.json';
        $infoJson = DownloadManager::downloadContent($infoJsonUrl);
        if (strpos($infoJson, '404: Not Found') === false) {
            $pluginData = json_decode($infoJson, true);
            if (is_array($pluginData) && array_key_exists('id', $pluginData)) {
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
     * Add the information contained in the plugin's info.json file
     *
     * @param string[] $pluginInfo Contenu du fichier info.json
     */
    public function addPluginInformations($pluginInfo)
    {
        if (array_key_exists('id', $pluginInfo)) $this->id = $pluginInfo['id'];
        if (array_key_exists('name', $pluginInfo)) $this->name = $pluginInfo['name'];
        if (array_key_exists('author', $pluginInfo)) $this->author = $pluginInfo['author'];
        if (array_key_exists('category', $pluginInfo)) $this->category = $pluginInfo['category'];
        if (array_key_exists('licence', $pluginInfo)) $this->licence = $pluginInfo['licence'];
        if (array_key_exists('changelog', $pluginInfo)) $this->changelogLink = $pluginInfo['changelog'];
        if (array_key_exists('documentation', $pluginInfo)) $this->documentationLink = $pluginInfo['documentation'];
        if (array_key_exists('description', $pluginInfo) && $pluginInfo['description'] !== null && $pluginInfo['description'] !== '') {
            $this->description = $pluginInfo['description'];
        }
    }

    /**
     * Download the plugin icon
     */
    public function downloadIcon()
    {
        $iconFilename = str_replace('/', '_', $this->fullName) . '.png';
        $iconUrl = 'https://raw.githubusercontent.com/' . $this->fullName . '/' . $this->defaultBranch . '/plugin_info/' . $this->id . '_icon.png';
        $targetPath = NEXTDOM_DATA . '/public/img/market_cache/' . $iconFilename;

        DownloadManager::downloadBinary($iconUrl, $targetPath);
        if (filesize($targetPath) < 100) {
            unlink($targetPath);
            $this->iconPath = '/public/img/unknown_icon.png';
        } else {
            $this->iconPath = '/var/public/img/market_cache/' . $iconFilename;
        }
        $this->writeCache();
    }

    /**
     * Write the cache file in JSON format
     */
    public function writeCache()
    {
        $dataArray = $this->getDataInArray();
        unset($dataArray['installed']);
        unset($dataArray['installedBranchData']);
        $this->dataStorage->storeJsonData('repo_data_' . str_replace('/', '_', $this->fullName), $dataArray);
        $this->dataStorage->storeRawData('repo_last_update_' . str_replace('/', '_', $this->fullName), time());
    }

    /**
     * Get all the information in an associative array
     *
     * @return array Data array
     */
    public function getDataInArray()
    {
        $dataArray = [];
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
     * Initialize the Jeedom data on the plugin
     */
    private function initUpdateData()
    {
        $this->updateData = UpdateManager::byLogicalId($this->id);
    }

    /**
     * Test if the plugin is installed
     *
     * @return bool True if the plugin is installed
     */
    public function isInstalled(): bool
    {
        $result = false;
        if ($this->updateData !== false) {
            $result = true;
        }
        return $result;
    }

    /**
     * @return array|bool
     */
    private function getInstalledBranchData()
    {
        $result = false;
        if ($this->updateData !== false && $this->updateData !== null) {
            $configuration = $this->updateData->getConfiguration();
            if (is_array($configuration) && array_key_exists('version', $configuration)) {
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

    /**
     * Update branch data
     *
     * @return bool True if the data was found
     * @throws \Exception
     */
    public function downloadBranchesInformations(): bool
    {
        $result = false;
        $baseGitRepoUrl = 'https://api.github.com/repos/' . $this->fullName . '/branches';
        $branches = DownloadManager::downloadContent($baseGitRepoUrl);
        if ($branches !== false) {
            $branches = json_decode($branches, true);
            $this->branchesList = [];
            foreach ($branches as $branch) {
                if (is_array($branch) && array_key_exists('name', $branch)) {
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
            $branch = [];
            $branch['name'] = $installedBranch['branch'];
            $branch['hash'] = $installedBranch['hash'];
            array_push($this->branchesList, $branch);
        }
        $this->writeCache();
    }

    /**
     * Get the name of the depot.
     *
     * @return string name of the depot
     */
    public function getGitName()
    {
        return $this->gitName;
    }

    /**
     * Get the full name
     *
     * @return string full name
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Define the name of the deposit full
     *
     * @param string $fullName full name
     *
     * @return MarketItem Instance of the object
     */
    public function setFullName($fullName): MarketItem
    {
        $this->fullName = $fullName;
        return $this;
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
     * Get the list of branches of the plugin
     *
     * @return array List of branches
     */
    public function getBranchesList()
    {
        return $this->branchesList;
    }

    /**
     * @return string
     */
    public function getIconPath()
    {
        return $this->iconPath;
    }
}
