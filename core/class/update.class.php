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

/* * ***************************Includes********************************* */
require_once NEXTDOM_ROOT . '/core/php/core.inc.php';

use NextDom\Managers\UpdateManager;

class update
{
    private $id;
    private $type = 'plugin';
    private $logicalId;
    private $name;
    private $localVersion;
    private $remoteVersion;
    private $status;
    private $configuration;
    private $source = 'market';
    private $_changeUpdate = false;
    private $_changed = false;

    public static function checkAllUpdate($_filter = '', $_findNewObject = true)
    {
        return UpdateManager::checkAllUpdate($_filter, $_findNewObject);
    }

    public static function listRepo()
    {
        return UpdateManager::listRepo();
    }

    public static function repoById($_id)
    {
        return UpdateManager::repoById($_id);
    }

    public static function updateAll($_filter = '')
    {
        return UpdateManager::updateAll($_filter);
    }

    public static function byId($_id)
    {
        return UpdateManager::byId($_id);
    }

    public static function byStatus($_status)
    {
        return UpdateManager::byStatus($_status);
    }

    public static function byLogicalId($_logicalId)
    {
        return UpdateManager::byLogicalId($_logicalId);
    }

    public static function byType($_type)
    {
        return UpdateManager::byType($_type);
    }

    public static function byTypeAndLogicalId($_type, $_logicalId)
    {
        return UpdateManager::byTypeAndLogicalId($_type, $_logicalId);
    }

    public static function all($_filter = '')
    {
        return UpdateManager::all($_filter);
    }

    public static function nbNeedUpdate()
    {
        return UpdateManager::nbNeedUpdate();
    }

    public static function findNewUpdateObject()
    {
        return UpdateManager::findNewUpdateObject();
    }

    public static function listCoreUpdate()
    {
        return UpdateManager::listCoreUpdate();
    }

    /**
     * Obtenir les informations de la mise à jour
     *
     * @return array
     */
    public function getInfo()
    {
        $result = [];
        if ($this->getType() != 'core') {
            $class = 'repo_' . $this->getSource();
            if (class_exists($class) && method_exists($class, 'objectInfo') && config::byKey($this->getSource() . '::enable') == 1) {
                $result = $class::objectInfo($this);
            }
        }
        return $result;
    }

    /**
     * Start update
     *
     * @throws Exception
     */
    public function doUpdate()
    {
        if ($this->getConfiguration('doNotUpdate') == 1 && $this->getType() != 'core') {
            log::add('update', 'alert', __('Vérification des mises à jour, mise à jour et réinstallation désactivées sur ') . $this->getLogicalId());
            return;
        }
        if ($this->getType() == 'core') {
            nextdom::update();
        } else {
            $class = 'repo_' . $this->getSource();
            if (class_exists($class) && method_exists($class, 'downloadObject') && config::byKey($this->getSource() . '::enable') == 1) {
                $this->preInstallUpdate();
                $cibDir = nextdom::getTmpFolder('market') . '/' . $this->getLogicalId();
                if (file_exists($cibDir)) {
                    rrmdir($cibDir);
                }
                mkdir($cibDir);
                if (!file_exists($cibDir) && !mkdir($cibDir, 0775, true)) {
                    throw new Exception(__('Impossible de créer le dossier  : ' . $cibDir . '. Problème de droits ?', __FILE__));
                }
                log::add('update', 'alert', __('Téléchargement du plugin...', __FILE__));
                $info = $class::downloadObject($this);
                if ($info['path'] !== false) {
                    $tmp = $info['path'];
                    log::add('update', 'alert', __("OK\n", __FILE__));

                    if (!file_exists($tmp)) {
                        throw new Exception(__('Impossible de trouver le fichier zip : ', __FILE__) . $this->getConfiguration('path'));
                    }
                    if (filesize($tmp) < 100) {
                        throw new Exception(__('Echec lors du téléchargement du fichier. Veuillez réessayer plus tard (taille inférieure à 100 octets). Cela peut être lié à un manque de place, une version minimale requise non consistente avec votre version de NextDom, un soucis du plugin sur le market, etc.', __FILE__));
                    }
                    $extension = strtolower(strrchr($tmp, '.'));
                    if (!in_array($extension, array('.zip'))) {
                        throw new Exception('Extension du fichier non valide (autorisé .zip) : ' . $extension);
                    }
                    log::add('update', 'alert', __('Décompression du zip...', __FILE__));
                    $zip = new ZipArchive;
                    $res = $zip->open($tmp);
                    if ($res === TRUE) {
                        if (!$zip->extractTo($cibDir . '/')) {
                            $content = file_get_contents($tmp);
                            throw new Exception(__('Impossible d\'installer le plugin. Les fichiers n\'ont pas pu être décompressés : ', __FILE__) . substr($content, 255));
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
                        } catch (Exception $e) {

                        }
                        if (!file_exists($cibDir . '/plugin_info')) {
                            $files = ls($cibDir, '*');
                            if (count($files) == 1 && file_exists($cibDir . '/' . $files[0] . 'plugin_info')) {
                                $cibDir = $cibDir . '/' . $files[0];
                            }
                        }
                        rmove($cibDir . '/', NEXTDOM_ROOT . '/plugins/' . $this->getLogicalId(), false, array(), true);
                        rrmdir($cibDir);
                        $cibDir = nextdom::getTmpFolder('market') . '/' . $this->getLogicalId();
                        if (file_exists($cibDir)) {
                            rrmdir($cibDir);
                        }
                        log::add('update', 'alert', __("OK\n", __FILE__));
                    } else {
                        throw new Exception(__('Impossible de décompresser l\'archive zip : ', __FILE__) . $tmp . ' => ' . ZipErrorMessage($res));
                    }
                }
                $this->postInstallUpdate($info);
            }
        }
        $this->refresh();
        $this->checkUpdate();
    }

    /**
     * Supprime une information de mise à jour
     *
     * @throws Exception
     */
    public function deleteObjet()
    {
        if ($this->getType() == 'core') {
            throw new Exception(__('Vous ne pouvez pas supprimer le core de NextDom', __FILE__));
        } else {
            switch ($this->getType()) {
                case 'plugin':
                    try {
                        $plugin = plugin::byId($this->getLogicalId());
                        if (is_object($plugin)) {
                            try {
                                $plugin->setIsEnable(0);
                            } catch (Exception $e) {

                            } catch (Error $e) {

                            }
                            foreach (eqLogic::byType($this->getLogicalId()) as $eqLogic) {
                                try {
                                    $eqLogic->remove();
                                } catch (Exception $e) {

                                } catch (Error $e) {

                                }
                            }
                        }
                        config::remove('*', $this->getLogicalId());
                    } catch (Exception $e) {

                    } catch (Error $e) {

                    }
                    break;
            }
            try {
                $class = 'repo_' . $this->getSource();
                if (class_exists($class) && method_exists($class, 'deleteObjet') && config::byKey($this->getSource() . '::enable') == 1) {
                    $class::deleteObjet($this);
                }
            } catch (Exception $e) {

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
     * Lance la procédure de préinstallation d'un objet
     *
     * @throws Exception
     */
    public function preInstallUpdate()
    {
        if (!file_exists(NEXTDOM_ROOT . '/plugins')) {
            mkdir(NEXTDOM_ROOT . '/plugins');
            @chown(NEXTDOM_ROOT . '/plugins', system::getWWWUid());
            @chgrp(NEXTDOM_ROOT . '/plugins', system::getWWWGid());
            @chmod(NEXTDOM_ROOT . '/plugins', 0775);
        }
        log::add('update', 'alert', __('Début de la mise à jour de : ', __FILE__) . $this->getLogicalId() . "\n");
        switch ($this->getType()) {
            case 'plugin':
                $cibDir = NEXTDOM_ROOT . '/plugins/' . $this->getLogicalId();
                if (!file_exists($cibDir) && !mkdir($cibDir, 0775, true)) {
                    throw new Exception(__('Impossible de créer le dossier  : ' . $cibDir . '. Problème de droits ?', __FILE__));
                }
                try {
                    $plugin = plugin::byId($this->getLogicalId());
                    if (is_object($plugin)) {
                        log::add('update', 'alert', __('Action de pré-update...', __FILE__));
                        $plugin->callInstallFunction('pre_update');
                        log::add('update', 'alert', __("OK\n", __FILE__));
                    }
                } catch (Exception $e) {

                } catch (Error $e) {

                }
        }
    }

    /**
     * Lancer la procédure post installation
     *
     * @param $informations
     * @throws Exception
     */
    public function postInstallUpdate($informations)
    {
        log::add('update', 'alert', __('Post-installation de ', __FILE__) . $this->getLogicalId() . '...');
        switch ($this->getType()) {
            case 'plugin':
                try {
                    $plugin = plugin::byId($this->getLogicalId());
                } catch (Exception $e) {
                    $this->remove();
                    throw new Exception(__('Impossible d\'installer le plugin. Le nom du plugin est différent de l\'ID ou le plugin n\'est pas correctement formé. Veuillez contacter l\'auteur.', __FILE__));
                } catch (Error $e) {
                    $this->remove();
                    throw new Exception(__('Impossible d\'installer le plugin. Le nom du plugin est différent de l\'ID ou le plugin n\'est pas correctement formé. Veuillez contacter l\'auteur.', __FILE__));
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
        log::add('update', 'alert', __("OK\n", __FILE__));
    }

    /**
     * Obtenir la dernière version disponible
     *
     * @return null|string
     */
    public static function getLastAvailableVersion()
    {
        try {
            $url = 'https://raw.githubusercontent.com/nextdom/core/' . config::byKey('core::branch', 'core', 'master') . '/core/config/version';
            $request_http = new com_http($url);
            return trim($request_http->exec());
        } catch (Exception $e) {

        } catch (\Error $e) {

        }
        return null;
    }

    /**
     * Vérifier si une mise à jour est disponible
     *
     * @return type
     */
    public function checkUpdate()
    {
        if ($this->getConfiguration('doNotUpdate') == 1 && $this->getType() != 'core') {
            log::add('update', 'alert', __('Vérification des mises à jour, mise à jour et réinstallation désactivées sur ', __FILE__) . $this->getLogicalId());
            return;
        }
        if ($this->getType() == 'core') {
            if (config::byKey('update::allowCore', 'core', 1) != 1) {
                return;
            }
            if (config::byKey('core::repo::provider') == 'default') {
                $this->setRemoteVersion(self::getLastAvailableVersion());
            } else {
                $class = 'repo_' . config::byKey('core::repo::provider');
                if (!method_exists($class, 'versionCore') || config::byKey(config::byKey('core::repo::provider') . '::enable') != 1) {
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
                $class = 'repo_' . $this->getSource();
                if (class_exists($class) && method_exists($class, 'checkUpdate') && config::byKey($this->getSource() . '::enable') == 1) {
                    $class::checkUpdate($this);
                }
            } catch (Exception $ex) {

            } catch (Error $ex) {

            }
        }
    }

    /**
     * Prépare l'objet avant la sauvegarde
     * TODO: Bizarre, en gros le nom = logicialId
     * @throws Exception
     */
    public function preSave()
    {
        if ($this->getLogicalId() == '') {
            throw new Exception(__('Le logical ID ne peut pas être vide', __FILE__));
        }
        if ($this->getName() == '') {
            $this->setName($this->getLogicalId());
        }
    }

    /**
     * Sauvegarde l'objet dans la base de données
     *
     * @return bool
     */
    public function save()
    {
        return DB::save($this);
    }

    /**
     * Envoi un évènement
     */
    public function postSave()
    {
        if ($this->_changeUpdate) {
            event::add('update::refreshUpdateNumber');
        }
    }

    /**
     * Supprime l'objet de la base de données
     *
     * @return bool
     */
    public function remove()
    {
        return DB::remove($this);
    }

    /**
     * Rafraichit les informations à partir de la base de données
     *
     * @throws Exception
     */
    public function refresh()
    {
        DB::refresh($this);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getConfiguration($_key = '', $_default = '')
    {
        return utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    public function setId($_id)
    {
        $this->_changed = utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
    }

    public function setName($_name)
    {
        $this->_changed = utils::attrChanged($this->_changed, $this->name, $_name);
        $this->name = $_name;
        return $this;
    }

    public function setStatus($_status)
    {
        if ($_status != $this->status) {
            $this->_changeUpdate = true;
            $this->_changed = true;
        }
        $this->status = $_status;
        return $this;
    }

    public function setConfiguration($_key, $_value)
    {
        $configuration = utils::setJsonAttr($this->configuration, $_key, $_value);
        $this->_changed = utils::attrChanged($this->_changed, $this->configuration, $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($_type)
    {
        $this->_changed = utils::attrChanged($this->_changed, $this->type, $_type);
        $this->type = $_type;
        return $this;
    }

    public function getLocalVersion()
    {
        return $this->localVersion;
    }

    public function getRemoteVersion()
    {
        return $this->remoteVersion;
    }

    public function setLocalVersion($_localVersion)
    {
        $this->_changed = utils::attrChanged($this->_changed, $this->localVersion, $_localVersion);
        $this->localVersion = $_localVersion;
        return $this;
    }

    public function setRemoteVersion($_remoteVersion)
    {
        $this->_changed = utils::attrChanged($this->_changed, $this->remoteVersion, $_remoteVersion);
        $this->remoteVersion = $_remoteVersion;
        return $this;
    }

    public function getLogicalId()
    {
        return $this->logicalId;
    }

    public function setLogicalId($_logicalId)
    {
        $this->_changed = utils::attrChanged($this->_changed, $this->logicalId, $_logicalId);
        $this->logicalId = $_logicalId;
        return $this;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource($_source)
    {
        $this->_changed = utils::attrChanged($this->_changed, $this->source, $_source);
        $this->source = $_source;
        return $this;
    }

    public function getChanged()
    {
        return $this->_changed;
    }

    public function setChanged($_changed)
    {
        $this->_changed = $_changed;
        return $this;
    }
}
