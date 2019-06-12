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

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
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
use ZipArchive;

/**
 * Update
 *
 * @ORM\Table(name="update", indexes={@ORM\Index(name="status", columns={"status"})})
 * @ORM\Entity
 */
class Update implements EntityInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=true)
     */
    protected $type = 'plugin';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="logicalId", type="string", length=127, nullable=true)
     */
    protected $logicalId;

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
    protected $source = 'market';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=127, nullable=true)
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    protected $configuration;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    protected $_changeUpdate = false;
    protected $_changed = false;

    /**
     * Obtenir les informations de la mise à jour
     *
     * @return array
     * @throws \Exception
     */
    public function getInfo()
    {
        $result = [];
        if ($this->getType() != 'core') {
            $class = 'Repo' . $this->getSource();
            if (class_exists($class) && method_exists($class, 'objectInfo') && ConfigManager::byKey($this->getSource() . '::enable') == 1) {
                $result = $class::objectInfo($this);
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $_type
     * @return $this
     */
    public function setType($_type)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->type, $_type);
        $this->type = $_type;
        return $this;
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
        $this->_changed = Utils::attrChanged($this->_changed, $this->source, $_source);
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
        if ($this->getConfiguration('doNotUpdate') == 1 && $this->getType() != 'core') {
            LogHelper::add('update', 'alert', __('Vérification des mises à jour, mise à jour et réinstallation désactivées sur ') . $this->getLogicalId());
            return;
        }
        if ($this->getType() == 'core') {
            NextDomHelper::update(['core' => 1]);
        } else {
            $class = UpdateManager::getRepoDataFromName($this->getSource())['phpClass'];
            if (class_exists($class) && method_exists($class, 'downloadObject') && ConfigManager::byKey($this->getSource() . '::enable') == 1) {
                $this->preInstallUpdate();
                $cibDir = NextDomHelper::getTmpFolder('market') . '/' . $this->getLogicalId();
                if (file_exists($cibDir)) {
                    rrmdir($cibDir);
                }
                mkdir($cibDir);
                if (!file_exists($cibDir) && !mkdir($cibDir, 0775, true)) {
                    throw new \Exception(__('Impossible de créer le dossier  : ' . $cibDir . '. Problème de droits ?'));
                }
                LogHelper::add('update', 'alert', __('Téléchargement du plugin...'));
                $info = $class::downloadObject($this);
                if ($info['path'] !== false) {
                    $tmp = $info['path'];
                    LogHelper::add('update', 'alert', __("OK\n"));

                    if (!file_exists($tmp)) {
                        throw new \Exception(__('Impossible de trouver le fichier zip : ') . $this->getConfiguration('path'));
                    }
                    if (filesize($tmp) < 100) {
                        throw new \Exception(__('Echec lors du téléchargement du fichier. Veuillez réessayer plus tard (taille inférieure à 100 octets). Cela peut être lié à un manque de place, une version minimale requise non consistente avec votre version de NextDom, un soucis du plugin sur le market, etc.'));
                    }
                    $extension = strtolower(strrchr($tmp, '.'));
                    if (!in_array($extension, array('.zip'))) {
                        throw new \Exception('Extension du fichier non valide (autorisé .zip) : ' . $extension);
                    }
                    LogHelper::add('update', 'alert', __('Décompression du zip...'));
                    $zip = new ZipArchive;
                    $res = $zip->open($tmp);
                    if ($res === TRUE) {
                        if (!$zip->extractTo($cibDir . '/')) {
                            $content = file_get_contents($tmp);
                            throw new \Exception(__('Impossible d\'installer le plugin. Les fichiers n\'ont pas pu être décompressés : ') . substr($content, 255));
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
                        rmove($cibDir, NEXTDOM_ROOT . '/plugins/' . $this->getLogicalId(), false, array(), true);
                        rrmdir($cibDir);
                        $cibDir = NextDomHelper::getTmpFolder('market') . '/' . $this->getLogicalId();
                        if (file_exists($cibDir)) {
                            rrmdir($cibDir);
                        }
                        LogHelper::add('update', 'alert', __("OK\n"));
                    } else {
                        throw new \Exception(__('Impossible de décompresser l\'archive zip : ') . $tmp . ' => ' . Utils::getZipErrorMessage($res));
                    }
                }
                $this->postInstallUpdate($info);
            }
        }
        $this->refresh();
        $this->checkUpdate();
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getConfiguration($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setConfiguration($_key, $_value)
    {
        $configuration = Utils::setJsonAttr($this->configuration, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->configuration, $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalId()
    {
        return $this->logicalId;
    }

    /**
     * @param $_logicalId
     * @return $this
     */
    public function setLogicalId($_logicalId)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->logicalId, $_logicalId);
        $this->logicalId = $_logicalId;
        return $this;
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
        LogHelper::add('update', 'alert', __('Début de la mise à jour de : ') . $this->getLogicalId() . "\n");
        switch ($this->getType()) {
            case 'plugin':
                $cibDir = NEXTDOM_ROOT . '/plugins/' . $this->getLogicalId();
                if (!file_exists($cibDir) && !mkdir($cibDir, 0775, true)) {
                    throw new \Exception(__('Impossible de créer le dossier  : ' . $cibDir . '. Problème de droits ?'));
                }
                try {
                    $plugin = PluginManager::byId($this->getLogicalId());
                    if (is_object($plugin)) {
                        LogHelper::add('update', 'alert', __('Action de pré-update...'));
                        $plugin->callInstallFunction('pre_update');
                        LogHelper::add('update', 'alert', __("OK\n"));
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
        LogHelper::add('update', 'alert', __('Post-installation de ') . $this->getLogicalId() . '...');
        switch ($this->getType()) {
            case 'plugin':
                try {
                    $plugin = PluginManager::byId($this->getLogicalId());
                } catch (\Exception $e) {
                    $this->remove();
                    throw new CoreException(__('Impossible d\'installer le plugin. Le nom du plugin est différent de l\'ID ou le plugin n\'est pas correctement formé. Veuillez contacter l\'auteur.'));
                }
                if (is_object($plugin) && $plugin->isActive()) {
                    $plugin->setIsEnable(1);
                }
                break;
        }
        if (isset($informations['localVersion'])) {
            $this->setLocalVersion($informations['localVersion']);
        }
        $this->save();
        LogHelper::add('update', 'alert', __("OK\n"));
    }

    /**
     * Supprime l'objet de la base de données
     *
     * @return bool
     */
    public function remove()
    {
        return DBHelper::remove($this);
    }

    /**
     * Sauvegarde l'objet dans la base de données
     *
     * @return bool
     */
    public function save()
    {
        return DBHelper::save($this);
    }

    /**
     * Rafraichit les informations à partir de la base de données
     *
     * @throws \Exception
     */
    public function refresh()
    {
        DBHelper::refresh($this);
    }

    /**
     * Vérifier si une mise à jour est disponible
     *
     * @return void
     * @throws \Exception
     */
    public function checkUpdate()
    {
        if ($this->getConfiguration('doNotUpdate') == 1 && $this->getType() != 'core') {
            LogHelper::add('update', 'alert', __('Vérification des mises à jour, mise à jour et réinstallation désactivées sur ') . $this->getLogicalId());
            return;
        }
        if ($this->getType() == 'core') {
            if (ConfigManager::byKey('update::allowCore', 'core', 1) != 1) {
                return;
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
                $class = 'Repo' . $this->getSource();
                if (class_exists($class) && method_exists($class, 'checkUpdate') && ConfigManager::byKey($this->getSource() . '::enable') == 1) {
                    $class::checkUpdate($this);
                }
            } catch (\Exception $ex) {

            }
        }
    }

    /**
     * Obtenir la dernière version disponible
     *
     * @return null|string
     */
    public static function getLastAvailableVersion()
    {
        try {
            $url = 'https://raw.githubusercontent.com/nextdom/core/' . ConfigManager::byKey('core::branch', 'core', 'master') . '/core/config/version';
            $request_http = new \com_http($url);
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
        $this->_changed = Utils::attrChanged($this->_changed, $this->localVersion, $_localVersion);
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
        $this->_changed = Utils::attrChanged($this->_changed, $this->remoteVersion, $_remoteVersion);
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
        if ($this->getType() == 'core') {
            throw new \Exception(__('Vous ne pouvez pas supprimer le core de NextDom'));
        } else {
            switch ($this->getType()) {
                case 'plugin':
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
                    break;
            }
            try {
                $class = 'Repo' . $this->getSource();
                if (class_exists($class) && method_exists($class, 'deleteObjet') && ConfigManager::byKey($this->getSource() . '::enable') == 1) {
                    $class::deleteObjet($this);
                }
            } catch (\Exception $e) {

            }
            switch ($this->getType()) {
                case 'plugin':
                    $cibDir = NEXTDOM_ROOT . '/plugins/' . $this->getLogicalId();
                    if (file_exists($cibDir)) {
                        rrmdir($cibDir);
                    }
                    break;
            }
            $this->remove();
        }
    }

    /**
     * Prépare l'objet avant la sauvegarde
     * TODO: Bizarre, en gros le nom = logicialId
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $_name
     * @return $this
     */
    public function setName($_name)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->name, $_name);
        $this->name = $_name;
        return $this;
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $_id
     * @return $this
     */
    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
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

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public function getChanged()
    {
        return $this->_changed;
    }

    /**
     * @param $_changed
     * @return $this
     */
    /**
     * @param $_changed
     * @return $this
     */
    /**
     * @param $_changed
     * @return $this
     */
    public function setChanged($_changed)
    {
        $this->_changed = $_changed;
        return $this;
    }

    /**
     * @return string
     */
    /**
     * @return string
     */
    /**
     * @return string
     */
    public function getTableName()
    {
        return 'update';
    }
}
