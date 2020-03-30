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

namespace NextDom\Model\Entity;

use NextDom\Com\ComHttp;
use NextDom\Enums\Common;
use NextDom\Enums\LogTarget;
use NextDom\Enums\NextDomObj;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\EventManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\ConfigurationEntity;
use NextDom\Model\Entity\Parents\LogicalIdEntity;
use NextDom\Model\Entity\Parents\NameEntity;
use NextDom\Model\Entity\Parents\RefreshEntity;
use NextDom\Model\Entity\Parents\TypeEntity;
use ZipArchive;

/**
 * Update
 *
 * @ORM\Table(name="update", indexes={@ORM\Index(name="status", columns={"status"})})
 * @ORM\Entity
 */
class Update extends BaseEntity
{
    const TABLE_NAME = NextDomObj::UPDATE;

    use ConfigurationEntity, LogicalIdEntity, NameEntity, RefreshEntity, TypeEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="localVersion", type="string", length=127, nullable=true)
     */
    protected $localVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="remoteVersion", type="string", length=127, nullable=true)
     */
    protected $remoteVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=127, nullable=true)
     */
    protected $source = Common::MARKET;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=127, nullable=true)
     */
    protected $status;

    protected $_changeUpdate = false;

    public function __construct()
    {
        if ($this->type === null) {
            $this->type = NextDomObj::PLUGIN;
        }
    }

    /**
     * Obtenir les informations de la mise à jour
     *
     * @return array
     * @throws \Exception
     */
    public function getInfo()
    {
        $result = [];
        if (!$this->isType(Common::CORE)) {
            $repoClass = 'Repo' . $this->getSource();
            if (class_exists($repoClass) && method_exists($repoClass, 'objectInfo') && ConfigManager::byKey($this->getSource() . '::enable') == 1) {
                $result = $repoClass::objectInfo($this);
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param $_source
     * @return $this
     */
    public function setSource($_source)
    {
        $this->updateChangeState($this->source, $_source);
        $this->source = $_source;
        return $this;
    }

    /**
     * Start update
     *
     * @throws CoreException
     * @throws \Throwable
     */
    public function doUpdate()
    {
        if ($this->getConfiguration('doNotUpdate') == 1 && !$this->isType(Common::CORE)) {
            LogHelper::addAlert(LogTarget::UPDATE, __('Vérification des mises à jour, mise à jour et réinstallation désactivées sur ') . $this->getLogicalId());
            return;
        }
        if ($this->isType(Common::CORE)) {
            NextDomHelper::update(['core' => 1]);
        } else {
            $class = UpdateManager::getRepoDataFromName($this->getSource())['phpClass'];
            if (class_exists($class) && method_exists($class, 'downloadObject') && ConfigManager::byKey($this->getSource() . '::enable') == 1) {
                $this->preInstallUpdate();
                PluginManager::backupPluginBeforeUpdate($this);
                $cibDir = NextDomHelper::getTmpFolder(Common::MARKET) . '/' . $this->getLogicalId();
                if (file_exists($cibDir)) {
                    rrmdir($cibDir);
                }
                mkdir($cibDir);
                if (!file_exists($cibDir) && !mkdir($cibDir, 0775, true)) {
                    throw new CoreException(__('Impossible de créer le dossier  : ' . $cibDir . '. Problème de droits ?'));
                }
                LogHelper::addAlert(LogTarget::UPDATE, __('Téléchargement du plugin...'));
                $info = $class::downloadObject($this);
                if ($info['path'] !== false) {
                    $tmp = $info['path'];
                    LogHelper::addAlert(LogTarget::UPDATE, __("OK\n"));

                    if (!file_exists($tmp)) {
                        throw new CoreException(__('Impossible de trouver le fichier zip : ') . $this->getConfiguration('path'));
                    }
                    if (filesize($tmp) < 100) {
                        throw new CoreException(__('Échec lors du téléchargement du fichier. Veuillez réessayer plus tard (taille inférieure à 100 octets). Cela peut être lié à un manque de place, une version minimale requise non consistente avec votre version de NextDom, un soucis du plugin sur le market, etc.'));
                    }
                    $extension = strtolower(strrchr($tmp, '.'));
                    if (!in_array($extension, ['.zip'])) {
                        throw new CoreException('Extension du fichier non valide (autorisé .zip) : ' . $extension);
                    }
                    LogHelper::addAlert(LogTarget::UPDATE, __('Décompression du zip...'));
                    $zip = new ZipArchive;
                    $res = $zip->open($tmp);
                    if ($res) {
                        if (!$zip->extractTo($cibDir . '/')) {
                            $content = file_get_contents($tmp);
                            throw new CoreException(__('Impossible d\'installer le plugin. Les fichiers n\'ont pas pu être décompressés : ') . substr($content, 255));
                        }
                        $zip->close();
                        unlink($tmp);
                        try {
                            if (file_exists(NEXTDOM_ROOT . '/plugins/' . $this->getLogicalId() . '/doc')) {
                                shell_exec('sudo rm -rf ' . NEXTDOM_ROOT . '/plugins/' . $this->getLogicalId() . '/doc');
                            }
                            if (file_exists(NEXTDOM_ROOT . '/plugins/' . $this->getLogicalId() . '/docs')) {
                                shell_exec('sudo rm -rf ' . NEXTDOM_ROOT . '/plugins/' . $this->getLogicalId() . '/docs');
                            }
                        } catch (\Exception $e) {

                        }
                        if (!file_exists($cibDir . '/plugin_info')) {
                            $files = FileSystemHelper::ls($cibDir, '*');
                            if (count($files) == 1 && file_exists($cibDir . '/' . $files[0] . 'plugin_info')) {
                                $cibDir = $cibDir . '/' . $files[0];
                            }
                        }
                        rmove($cibDir, NEXTDOM_ROOT . '/plugins/' . $this->getLogicalId(), false, [], true);
                        rrmdir($cibDir);
                        $cibDir = NextDomHelper::getTmpFolder(Common::MARKET) . '/' . $this->getLogicalId();
                        if (file_exists($cibDir)) {
                            rrmdir($cibDir);
                        }
                        LogHelper::addAlert(LogTarget::UPDATE, __("OK\n"));
                    } else {
                        throw new CoreException(__('Impossible de décompresser l\'archive zip : ') . $tmp . ' => ' . Utils::getZipErrorMessage($res));
                    }
                }
                $this->postInstallUpdate($info);
            }
        }
        $this->refresh();
        $this->checkUpdate();
    }

    /**
     * Lance la procédure de préinstallation d'un objet
     *
     * @throws \Exception
     */
    public function preInstallUpdate()
    {
        if (!file_exists(NEXTDOM_ROOT . '/plugins')) {
            mkdir(NEXTDOM_ROOT . '/plugins');
            @chown(NEXTDOM_ROOT . '/plugins', SystemHelper::getWWWUid());
            @chgrp(NEXTDOM_ROOT . '/plugins', SystemHelper::getWWWGid());
            @chmod(NEXTDOM_ROOT . '/plugins', 0775);
        }
        LogHelper::addAlert(LogTarget::UPDATE, __('Début de la mise à jour de : ') . $this->getLogicalId() . "\n");
        if ($this->isType(NextDomObj::PLUGIN)) {
            $targetDir = NEXTDOM_ROOT . '/plugins/' . $this->getLogicalId();
            if (!file_exists($targetDir) && !mkdir($targetDir, 0775, true)) {
                throw new CoreException(__('Impossible de créer le dossier  : ' . $targetDir . '. Problème de droits ?'));
            }
            try {
                $plugin = PluginManager::byId($this->getLogicalId());
                if (is_object($plugin)) {
                    LogHelper::addAlert(LogTarget::UPDATE, __('Action de pré-update...'));
                    $plugin->callInstallFunction('pre_update');
                    LogHelper::addAlert(LogTarget::UPDATE, __("OK\n"));
                }
            } catch (\Exception $e) {

            }
        }
    }

    /**
     * Lancer la procédure post installation
     *
     * @param $informations
     * @throws CoreException
     * @throws \Throwable
     */
    public function postInstallUpdate($informations)
    {
        LogHelper::addAlert(LogTarget::UPDATE, __('Post-installation de ') . $this->getLogicalId() . '...');
        if ($this->isType(NextDomObj::PLUGIN)) {
            try {
                $plugin = PluginManager::byId($this->getLogicalId());
            } catch (\Exception $e) {
                $this->remove();
                throw new CoreException(__('Impossible d\'installer le plugin. Le nom du plugin est différent de l\'ID ou le plugin n\'est pas correctement formé. Veuillez contacter l\'auteur.'));
            }
            if (is_object($plugin) && $plugin->isActive()) {
                $plugin->setIsEnable(1);
            }
        }
        if (isset($informations['localVersion'])) {
            $this->setLocalVersion($informations['localVersion']);
        }
        $this->save();
        LogHelper::addAlert(LogTarget::UPDATE, __("OK\n"));
    }

    /**
     * Vérifier si une mise à jour est disponible
     *
     * @return bool
     * @throws \Exception
     */
    public function checkUpdate()
    {
        if ($this->getConfiguration('doNotUpdate') == 1 && !$this->isType(Common::CORE)) {
            LogHelper::addAlert(LogTarget::UPDATE, __('Vérification des mises à jour, mise à jour et réinstallation désactivées sur ') . $this->getLogicalId());
            return false;
        }
        if ($this->isType(Common::CORE)) {
            if (ConfigManager::byKey('update::allowCore', Common::CORE, 1) != 1) {
                return false;
            }
            if (ConfigManager::byKey('core::repo::provider') == 'default') {
                $this->setRemoteVersion(self::getLastAvailableVersion());
            } else {
                $class = 'Repo' . ConfigManager::byKey('core::repo::provider');
                if (!method_exists($class, 'versionCore') || ConfigManager::byKey(ConfigManager::byKey('core::repo::provider') . '::enable') != 1) {
                    $version = $this->getLocalVersion();
                } else {
                    $version = $class::versionCore();
                    if ($version === null) {
                        $version = $this->getLocalVersion();
                    }
                }
                $this->setRemoteVersion($version);
            }
            if (version_compare($this->getRemoteVersion(), $this->getLocalVersion(), '>')) {
                $this->setStatus('update');
            } else {
                $this->setStatus('ok');
            }
            $this->save();
        } else {
            try {
                $class = UpdateManager::getRepoDataFromName($this->getSource())['phpClass'];
                if (class_exists($class) && method_exists($class, 'checkUpdate') && ConfigManager::byKey($this->getSource() . '::enable') == 1) {
                    $class::checkUpdate($this);
                }
            } catch (\Exception $ex) {

            }
        }
        return true;
    }

    /**
     * Obtenir la dernière version disponible
     *
     * @return null|string
     */
    public static function getLastAvailableVersion()
    {
        try {
            $url = 'https://raw.githubusercontent.com/NextDom/nextdom-core/' . ConfigManager::byKey('core::branch', Common::CORE, 'master') . '/assets/config/Nextdom_version';
            $request_http = new ComHttp($url);
            return trim($request_http->exec());
        } catch (\Exception $e) {

        }
        return null;
    }

    /**
     * @return string
     */
    public function getLocalVersion()
    {
        return $this->localVersion;
    }

    /**
     * @param $_localVersion
     * @return $this
     */
    public function setLocalVersion($_localVersion)
    {
        $this->updateChangeState($this->localVersion, $_localVersion);
        $this->localVersion = $_localVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteVersion()
    {
        return $this->remoteVersion;
    }

    /**
     * @param $_remoteVersion
     * @return $this
     */
    public function setRemoteVersion($_remoteVersion)
    {
        $this->updateChangeState($this->remoteVersion, $_remoteVersion);
        $this->remoteVersion = $_remoteVersion;
        return $this;
    }

    /**
     * Supprime une information de mise à jour
     *
     * @throws \Throwable
     */
    public function deleteObjet()
    {
        if ($this->isType(Common::CORE)) {
            throw new CoreException(__('Vous ne pouvez pas supprimer le core de NextDom'));
        } else {
            if ($this->isType(NextDomObj::PLUGIN)) {
                try {
                    $plugin = PluginManager::byId($this->getLogicalId());
                    if (is_object($plugin)) {
                        try {
                            $plugin->setIsEnable(0);
                        } catch (\Exception $e) {

                        }
                        foreach (EqLogicManager::byType($this->getLogicalId()) as $eqLogic) {
                            try {
                                $eqLogic->remove();
                            } catch (\Exception $e) {

                            }
                        }
                    }
                    ConfigManager::remove('*', $this->getLogicalId());
                } catch (\Exception $e) {

                }
            }
            try {
                $class = 'Repo' . $this->getSource();
                if (class_exists($class) && method_exists($class, 'deleteObjet') && ConfigManager::byKey($this->getSource() . '::enable') == 1) {
                    $class::deleteObjet($this);
                }
            } catch (\Exception $e) {

            }
            if ($this->isType(NextDomObj::PLUGIN)) {
                $cibDir = NEXTDOM_ROOT . '/plugins/' . $this->getLogicalId();
                if (file_exists($cibDir)) {
                    rrmdir($cibDir);
                }
            }
            $this->remove();
        }
    }

    /**
     * Prépare l'objet avant la sauvegarde
     * @TODO: Bizarre, en gros le nom = logicialId
     * @throws CoreException
     */
    public function preSave()
    {
        if ($this->getLogicalId() == '') {
            throw new CoreException(__('Le logical ID ne peut pas être vide'));
        }
        if ($this->getName() == '') {
            $this->setName($this->getLogicalId());
        }
    }

    /**
     * Envoi un évènement
     */
    public function postSave()
    {
        if ($this->_changeUpdate) {
            EventManager::add('update::refreshUpdateNumber');
        }
    }

    public function postRemove()
    {
        EventManager::add('update::refreshUpdateNumber');
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $_status
     * @return $this
     */
    public function setStatus($_status)
    {
        if ($_status != $this->status) {
            $this->_changeUpdate = true;
            $this->_changed = true;
        }
        $this->status = $_status;
        return $this;
    }
}
