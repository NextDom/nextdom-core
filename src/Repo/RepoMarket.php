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

namespace NextDom\Repo;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Api;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\BackupManager;
use NextDom\Managers\CacheManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\MessageManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;
use NextDom\Managers\UserManager;
use NextDom\Model\Entity\Update;

class RepoMarket
{
    /*     * *************************Attributs****************************** */

    public static $_name = 'Market';

    public static $_scope = array(
        'plugin' => true,
        'backup' => false,
        'hasConfiguration' => true,
        'proxy' => true,
        'sendPlugin' => true,
        'hasStore' => true,
        'hasScenarioStore' => true,
        'test' => true,
    );

    public static $_configuration = array(
        'configuration' => array(
            'address' => array(
                'name' => 'Adresse',
                'type' => 'input',
            ),
            'username' => array(
                'name' => 'Nom d\'utilisateur',
                'type' => 'input',
            ),
            'password' => array(
                'name' => 'Mot de passe',
                'type' => 'password',
            ),

            'cloud::backup::name' => array(
                'name' => '[Backup cloud] Nom',
                'type' => 'input',
            ),
            'cloud::backup::password' => array(
                'name' => '[Backup cloud] Mot de passe',
                'type' => 'password',
            ),
            'cloud::backup::fullfrequency' => array(
                'name' => '[Backup cloud] Fréquence backup full',
                'type' => 'select',
                'values' => array('1D' => 'Chaque jour', '1W' => 'Chaque semaine', '1M' => 'Chaque mois'),
            ),
        ),
        'parameters_for_add' => array(
            'version' => array(
                'name' => 'Version : beta, stable',
                'type' => 'input',
            ),
        ),
    );

    private $id;
    private $name;
    private $type;
    private $datetime;
    private $description;
    private $categorie;
    private $changelog;
    private $doc;
    private $version;
    private $user_id;
    private $downloaded;
    private $status;
    private $author;
    private $logicalId;
    private $rating;
    private $utilization;
    private $isAuthor;
    private $img;
    private $buyer;
    private $purchase = 0;
    private $cost = 0;
    private $realcost = 0;
    private $link;
    private $certification;
    private $language;
    private $private;
    private $updateBy;
    private $parameters;
    private $hardwareCompatibility;
    private $nbInstall;
    private $allowVersion = array();

    /*     * ***********************Méthodes statiques*************************** */

    /**
     * @param Update $_update
     */
    public static function checkUpdate(&$_update)
    {
        if (is_array($_update)) {
            if (count($_update) < 1) {
                return;
            }
            $markets = array('logicalId' => array(), 'version' => array());
            $marketObject = array();
            foreach ($_update as $update) {
                $markets['logicalId'][] = array('logicalId' => $update->getLogicalId(), 'type' => $update->getType());
                $markets['version'][] = $update->getConfiguration('version', 'stable');
                $marketObject[$update->getType() . $update->getLogicalId()] = $update;
            }
            $markets_infos = self::getInfo($markets['logicalId'], $markets['version']);
            foreach ($markets_infos as $logicalId => $market_info) {
                $update = $marketObject[$logicalId];
                if (is_object($update)) {
                    $update->setStatus($market_info['status']);
                    $update->setConfiguration('market', $market_info['market']);
                    $update->setRemoteVersion($market_info['datetime']);
                    if ($update->getConfiguration('version') == '') {
                        $update->setConfiguration('version', 'stable');
                    }
                    $update->save();
                }
            }
            return;
        }
        $market_info = self::getInfo(array('logicalId' => $_update->getLogicalId(), 'type' => $_update->getType()), $_update->getConfiguration('version', 'stable'));
        $_update->setStatus($market_info['status']);
        $_update->setConfiguration('market', $market_info['market']);
        $_update->setRemoteVersion($market_info['datetime']);
        $_update->save();
    }

    public static function getInfo($_logicalId, $_version = 'stable')
    {
        $returns = array();
        if (is_array($_logicalId) && is_array($_version) && count($_logicalId) == count($_version)) {
            if (is_array(reset($_logicalId))) {
                $markets = self::byLogicalIdAndType($_logicalId);
            } else {
                $markets = self::byLogicalId($_logicalId);
            }

            $returns = array();
            $countLogicalId = count($_logicalId);
            for ($i = 0; $i < $countLogicalId; $i++) {
                if (is_array($_logicalId[$i])) {
                    $logicalId = $_logicalId[$i]['type'] . $_logicalId[$i]['logicalId'];
                } else {
                    $logicalId = $_logicalId[$i];
                }
                $return['owner'] = array();
                $return['datetime'] = '0000-01-01 00:00:00';
                if ($logicalId == '' || ConfigManager::byKey('market::address') == '') {
                    $return['owner']['market'] = 0;
                    $return['status'] = 'ok';
                    return $return;
                }

                if (ConfigManager::byKey('market::username') != '' && ConfigManager::byKey('market::password') != '') {
                    $return['owner']['market'] = 1;
                } else {
                    $return['owner']['market'] = 0;
                }
                $return['market'] = 0;

                try {
                    if (isset($markets[$logicalId])) {
                        $market = $markets[$logicalId];
                        if (!is_object($market)) {
                            $return['status'] = 'ok';
                        } else {
                            $return['datetime'] = $market->getDatetime($_version[$i]);
                            $return['market'] = 1;
                            $return['owner']['market'] = $market->getIsAuthor();
                            $update = UpdateManager::byTypeAndLogicalId($market->getType(), $market->getLogicalId());
                            $updateDateTime = '0000-01-01 00:00:00';
                            if (is_object($update)) {
                                $updateDateTime = $update->getLocalVersion();
                            }
                            if ($updateDateTime < $market->getDatetime($_version[$i], $updateDateTime)) {
                                $return['status'] = 'update';
                            } else {
                                $return['status'] = 'ok';
                            }
                        }
                    } else {
                        $return['status'] = 'ok';
                    }
                } catch (\Exception $e) {
                    LogHelper::add('market', 'debug', __('Erreur self::getinfo : ') . $e->getMessage());
                    $return['status'] = 'ok';
                }
                $returns[$logicalId] = $return;
            }
            return $returns;
        }
        $return = array();
        $return['datetime'] = '0000-01-01 00:00:00';
        $return['owner'] = array();
        if (ConfigManager::byKey('market::address') == '') {
            $return['market'] = 0;
            $return['owner']['market'] = 0;
            $return['status'] = 'ok';
            return $return;
        }

        if (ConfigManager::byKey('market::username') != '' && ConfigManager::byKey('market::password') != '') {
            $return['owner']['market'] = 1;
        } else {
            $return['owner']['market'] = 0;
        }
        $return['market'] = 0;

        try {
            if (is_array($_logicalId)) {
                $market = self::byLogicalIdAndType($_logicalId['logicalId'], $_logicalId['type']);
            } else {
                $market = self::byLogicalId($_logicalId);
            }
            if (!is_object($market)) {
                $return['status'] = 'depreciated';
            } else {
                $return['datetime'] = $market->getDatetime($_version);
                $return['market'] = 1;
                $return['owner']['market'] = $market->getIsAuthor();
                $update = UpdateManager::byTypeAndLogicalId($market->getType(), $market->getLogicalId());
                $updateDateTime = '0000-01-01 00:00:00';
                if (is_object($update)) {
                    $updateDateTime = $update->getLocalVersion();
                }
                if ($updateDateTime < $market->getDatetime($_version, $updateDateTime)) {
                    $return['status'] = 'update';
                } else {
                    $return['status'] = 'ok';
                }
            }
        } catch (\Exception $e) {
            LogHelper::add('market', 'debug', __('Erreur self::getinfo : ') . $e->getMessage());
            $return['status'] = 'ok';
        }
        return $return;
    }

    public static function byLogicalIdAndType($_logicalId, $_type = '')
    {
        $market = self::getJsonRpc();
        if (is_array($_logicalId)) {
            $options = $_logicalId;
            $timeout = 240;
        } else {
            $options = array('logicalId' => $_logicalId, 'type' => $_type);
            $timeout = 10;
        }
        if ($market->sendRequest('market::byLogicalIdAndType', $options, $timeout, null, 1)) {
            if (is_array($_logicalId)) {
                $return = array();
                foreach ($market->getResult() as $logicalId => $result) {
                    if (isset($result['id'])) {
                        $return[$logicalId] = self::construct($result);
                    }
                }
                return $return;
            }
            return self::construct($market->getResult());
        } else {
            LogHelper::add('market', 'debug', print_r($market, true));
            throw new CoreException($market->getError(), $market->getErrorCode());
        }
    }

    public static function getJsonRpc()
    {
        $internalIp = '';
        try {
            $internalIp = NetworkHelper::getNetworkAccess('internal', 'ip');
        } catch (\Exception $e) {

        }
        $uname = shell_exec('uname -a');
        if (ConfigManager::byKey('market::username') != '' && ConfigManager::byKey('market::password') != '') {
            $params = array(
                'username' => ConfigManager::byKey('market::username'),
                'password' => self::getPassword(),
                'password_type' => 'sha1',
                'nextdomversion' => NextDomHelper::getNextdomVersion(),
                'hwkey' => NextDomHelper::getHardwareKey(),
                'information' => array(
                    'nbMessage' => MessageManager::nbMessage(),
                    'nbUpdate' => UpdateManager::nbNeedUpdate(),
                    'hardware' => (method_exists('nextdom', 'getHardwareName')) ? NextDomHelper::getHardwareName() : '',
                    'uname' => $uname,
                ),
                'market_api_key' => Api::getApiKey('apimarket'),
                'localIp' => $internalIp,
                'nextdom_name' => ConfigManager::byKey('name'),
                'plugin_install_list' => PluginManager::listPlugin(true, false, true),
            );
            if (ConfigManager::byKey('market::allowDNS') != 1 || ConfigManager::byKey('network::disableMangement') == 1) {
                $params['url'] = NetworkHelper::getNetworkAccess('external');
            }
            $jsonrpc = new \jsonrpcClient(ConfigManager::byKey('market::address') . '/core/api/api.php', '', $params);
        } else {
            $jsonrpc = new \jsonrpcClient(ConfigManager::byKey('market::address') . '/core/api/api.php', '', array(
                'nextdomversion' => NextDomHelper::getNextdomVersion(),
                'hwkey' => NextDomHelper::getHardwareKey(),
                'localIp' => $internalIp,
                'nextdom_name' => ConfigManager::byKey('name'),
                'plugin_install_list' => PluginManager::listPlugin(true, false, true),
                'information' => array(
                    'nbMessage' => MessageManager::nbMessage(),
                    'nbUpdate' => UpdateManager::nbNeedUpdate(),
                    'hardware' => (method_exists('nextdom', 'getHardwareName')) ? NextDomHelper::getHardwareName() : '',
                    'uname' => $uname,
                ),
            ));
        }
        $jsonrpc->setCb_class('RepoMarket');
        $jsonrpc->setCb_function('postJsonRpc');
        $jsonrpc->setNoSslCheck(true);
        return $jsonrpc;
    }

    /*     * ***********************BACKUP*************************** */

    public static function getPassword()
    {
        $password = ConfigManager::byKey('market::password');
        if (!is_sha1($password)) {
            return sha1($password);
        }
        return $password;
    }

    /**
     *
     * @param array $_arrayMarket
     * @return \self
     */
    public static function construct(array $_arrayMarket)
    {
        $market = new self();
        if (!isset($_arrayMarket['id'])) {
            return;
        }
        $market->setId($_arrayMarket['id'])
            ->setName($_arrayMarket['name'])
            ->setType($_arrayMarket['type']);
        $market->datetime = json_encode($_arrayMarket['datetime'], JSON_UNESCAPED_UNICODE);
        $market->setDescription($_arrayMarket['description'])
            ->setDownloaded($_arrayMarket['downloaded'])
            ->setUser_id($_arrayMarket['user_id'])
            ->setVersion($_arrayMarket['version'])
            ->setCategorie($_arrayMarket['categorie']);
        $market->status = json_encode($_arrayMarket['status'], JSON_UNESCAPED_UNICODE);
        $market->setAuthor($_arrayMarket['author']);
        if (isset($_arrayMarket['changelog'])) {
            $market->setChangelog($_arrayMarket['changelog']);
        }
        if (isset($_arrayMarket['doc'])) {
            $market->setDoc($_arrayMarket['doc']);
        }
        $market->setLogicalId($_arrayMarket['logicalId']);
        if (isset($_arrayMarket['utilization'])) {
            $market->setUtilization($_arrayMarket['utilization']);
        }
        if (isset($_arrayMarket['certification'])) {
            $market->setCertification($_arrayMarket['certification']);
        }
        if (isset($_arrayMarket['allowVersion'])) {
            $market->setAllowVersion($_arrayMarket['allowVersion']);
        }
        if (isset($_arrayMarket['nbInstall'])) {
            $market->setNbInstall($_arrayMarket['nbInstall']);
        }
        $market->setPurchase($_arrayMarket['purchase'])
            ->setCost($_arrayMarket['cost']);
        $market->rating = ($_arrayMarket['rating']);
        $market->setBuyer($_arrayMarket['buyer'])
            ->setUpdateBy($_arrayMarket['updateBy'])
            ->setPrivate($_arrayMarket['private']);
        $market->img = json_encode($_arrayMarket['img'], JSON_UNESCAPED_UNICODE);
        $market->link = json_encode($_arrayMarket['link'], JSON_UNESCAPED_UNICODE);
        $market->language = json_encode($_arrayMarket['language'], JSON_UNESCAPED_UNICODE);
        if (isset($_arrayMarket['hardwareCompatibility'])) {
            $market->hardwareCompatibility = json_encode($_arrayMarket['hardwareCompatibility'], JSON_UNESCAPED_UNICODE);
        }

        $market->setRealcost($_arrayMarket['realCost']);
        if (!isset($_arrayMarket['isAuthor'])) {
            $_arrayMarket['isAuthor'] = true;
        }
        $market->setIsAuthor($_arrayMarket['isAuthor']);

        if (isset($_arrayMarket['parameters']) && is_array($_arrayMarket['parameters'])) {
            foreach ($_arrayMarket['parameters'] as $key => $value) {
                $market->setParameters($key, $value);
            }
        }
        return $market;
    }

    public static function byLogicalId($_logicalId)
    {
        $market = self::getJsonRpc();

        if (is_array($_logicalId)) {
            $options = $_logicalId;
            $timeout = 240;
        } else {
            $options = array('logicalId' => $_logicalId);
            $timeout = 10;
        }
        if ($market->sendRequest('market::byLogicalId', $options, $timeout, null, 1)) {
            if (is_array($_logicalId)) {
                $return = array();
                foreach ($market->getResult() as $logicalId => $result) {
                    if (isset($result['id'])) {
                        $return[$logicalId] = self::construct($result);
                    }
                }
                return $return;
            }
            return self::construct($market->getResult());
        } else {
            throw new CoreException($market->getError(), $market->getErrorCode());
        }
    }

    public function getDatetime($_key = '', $_default = '')
    {
        return utils::getJsonAttr($this->datetime, $_key, $_default);
    }

    public function setDatetime($_key, $_value)
    {
        $this->datetime = utils::setJsonAttr($this->datetime, $_key, $_value);
        return $this;
    }

    public function getIsAuthor()
    {
        return $this->isAuthor;
    }

    public function setIsAuthor($isAuthor)
    {
        $this->isAuthor = $isAuthor;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getLogicalId()
    {
        return $this->logicalId;
    }

    public function setLogicalId($logicalId)
    {
        $this->logicalId = $logicalId;
        return $this;
    }

    public static function downloadObject($_update)
    {
        $market = self::byLogicalIdAndType($_update->getLogicalId(), $_update->getType());
        if (is_object($market)) {
            $file = $market->install($_update->getConfiguration('version', 'stable'));
        } else {
            throw new CoreException(__('Objet introuvable sur le market : ') . $_update->getLogicalId() . '/' . $_update->getType());
        }
        return array('path' => $file, 'localVersion' => $market->getDatetime($_update->getConfiguration('version', 'stable')));
    }

    /*     * ***********************CRON*************************** */

    public function install($_version = 'stable')
    {
        $tmp_dir = NextDomHelper::getTmpFolder('market');
        $tmp = $tmp_dir . '/' . $this->getLogicalId() . '.zip';
        if (file_exists($tmp)) {
            unlink($tmp);
        }
        if (!is_writable($tmp_dir)) {
            exec(SystemHelper::getCmdSudo() . 'chmod 777 -R ' . $tmp);
        }
        if (!is_writable($tmp_dir)) {
            throw new CoreException(__('Impossible d\'écrire dans le répertoire : ') . $tmp . __('. Exécuter la commande suivante en SSH : sudo chmod 777 -R ') . $tmp_dir);
        }

        $url = ConfigManager::byKey('market::address') . "/core/php/downloadFile.php?id=" . $this->getId() . '&version=' . $_version . '&nextdomversion=' . NextDomHelper::getNextdomVersion() . '&hwkey=' . NextDomHelper::getHardwareKey() . '&username=' . urlencode(ConfigManager::byKey('market::username')) . '&password=' . self::getPassword() . '&password_type=sha1';
        LogHelper::add('update', 'alert', __('Téléchargement de ') . $this->getLogicalId() . '...');
        exec('wget --no-check-certificate "' . $url . '" -O ' . $tmp . ' 2>&1 >> ' . LogHelper::getPathToLog('update'));
        switch ($this->getType()) {
            case 'plugin':
                return $tmp;
                break;
            default:
                LogHelper::add('update', 'alert', __('Installation des plugin, widget, scénario...'));
                $type = $this->getType();
                if (class_exists($type) && method_exists($type, 'getFromMarket')) {
                    $type::getFromMarket($this, $tmp);
                }
                LogHelper::add('update', 'alert', __("OK\n"));
                break;
        }
        return false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /*     * ***********************INFO*************************** */

    public static function deleteObjet($_update)
    {
        try {
            $market = self::byLogicalIdAndType($_update->getLogicalId(), $_update->getType());
        } catch (\Exception $e) {
            $market = new RepoMarket();
            $market->setLogicalId($_update->getLogicalId());
            $market->setType($_update->getType());
        }
        try {
            if (is_object($market)) {
                $market->remove();
            }
        } catch (\Exception $e) {

        }
    }

    /*     * ***********************UTILS*************************** */

    public function remove()
    {
        $cache = CacheManager::byKey('market::info::' . $this->getLogicalId());
        if (is_object($cache)) {
            $cache->remove();
        }
        switch ($this->getType()) {
            case 'plugin':

                break;
            default:
                $type = $this->getType();
                if (class_exists($type) && method_exists($type, 'removeFromMarket')) {
                    $type::removeFromMarket($this);
                }
                break;
        }
    }

    public static function objectInfo($_update)
    {
        $url = 'https://nextdom.github.io/documentation/plugins/' . $_update->getLogicalId() . '/' . ConfigManager::byKey('language', 'core', 'fr_FR') . '/index.html';
        if ($_update->getConfiguration('third_plugin', null) === null) {
            $_update->setConfiguration('third_plugin', 0);
            $header = get_headers($url);
            if (strpos($header[0], '200') === false) {
                $_update->setConfiguration('third_plugin', 1);
                $url = 'https://nextdom.github.io/documentation/third_plugin/' . $_update->getLogicalId() . '/' . ConfigManager::byKey('language', 'core', 'fr_FR') . '/index.html';
            }
            $_update->save();
        } elseif ($_update->getConfiguration('third_plugin', 0) == 1) {
            $url = 'https://nextdom.github.io/documentation/third_plugin/' . $_update->getLogicalId() . '/' . ConfigManager::byKey('language', 'core', 'fr_FR') . '/index.html';
        }
        return array(
            'doc' => $url,
            'changelog' => $url . '#_changelog',
            'display' => 'https://www.jeedom.fr/market/index.php?v=d&p=market&type=plugin&plugin_id=' . $_update->getLogicalId(),
        );
    }

    public static function backup_send($_path)
    {
        if (ConfigManager::byKey('market::backupServer') == '' || ConfigManager::byKey('market::backupPassword') == '') {
            throw new \Exception(__('Aucun serveur de backup defini. Avez vous bien un abonnement au backup cloud ?'));
        }
        if (ConfigManager::byKey('market::cloud::backup::password') == '') {
            throw new \Exception(__('Vous devez obligatoirement avoir un mot de passe pour le backup cloud'));
        }
        shell_exec(SystemHelper::getCmdSudo() . ' rm -rf /tmp/duplicity-*-tempdir');
        self::backup_createFolderIsNotExist();
        self::backup_install();
        $base_dir = realpath(__DIR__ . '/../../');
        if (!file_exists($base_dir . '/tmp')) {
            mkdir($base_dir . '/tmp');
        }
        $excludes = array(
            $base_dir . '/tmp',
            $base_dir . '/log',
            $base_dir . '/backup',
            $base_dir . '/doc',
            $base_dir . '/docs',
            $base_dir . '/plugins/*/doc',
            $base_dir . '/plugins/*/docs',
            $base_dir . '/tests',
            $base_dir . '/.git',
            $base_dir . '/.log',
            $base_dir . '/core/config/common.config.php',
            $base_dir . '/' . ConfigManager::byKey('backup::path'),
        );
        if (ConfigManager::byKey('recordDir', 'camera') != '') {
            $excludes[] = $base_dir . '/' . ConfigManager::byKey('recordDir', 'camera');
        }
        $cmd = SystemHelper::getCmdSudo() . ' PASSPHRASE="' . ConfigManager::byKey('market::cloud::backup::password') . '"';
        $cmd .= ' duplicity incremental --full-if-older-than ' . ConfigManager::byKey('market::cloud::backup::fullfrequency', 'core', '1M');
        foreach ($excludes as $exclude) {
            $cmd .= ' --exclude "' . $exclude . '"';
        }
        $cmd .= ' --num-retries 2';
        $cmd .= ' --ssl-no-check-certificate';
        $cmd .= ' --tempdir ' . $base_dir;
        $cmd .= ' ' . $base_dir . '  "webdavs://' . ConfigManager::byKey('market::username') . ':' . ConfigManager::byKey('market::backupPassword');
        $cmd .= '@' . ConfigManager::byKey('market::backupServer') . '/remote.php/webdav/' . ConfigManager::byKey('market::cloud::backup::name') . '"';
        try {
            \com_shell::execute($cmd);
        } catch (\Exception $e) {
            if (self::backup_errorAnalyzed($e->getMessage()) != null) {
                throw new CoreException('[backup cloud] ' . self::backup_errorAnalyzed($e->getMessage()));
            }
            if (strpos($e->getMessage(), 'Insufficient Storage') !== false) {
                self::backup_clean();
            }
            SystemHelper::kill('duplicity');
            shell_exec(SystemHelper::getCmdSudo() . ' rm -rf ' . $base_dir . '/tmp/duplicity*');
            shell_exec(SystemHelper::getCmdSudo() . ' rm -rf ~/.cache/duplicity/*');
            \com_shell::execute($cmd);
        }
    }

    public static function backup_createFolderIsNotExist()
    {
        $client = new \Sabre\DAV\Client(array(
            'baseUri' => 'https://' . ConfigManager::byKey('market::backupServer'),
            'userName' => ConfigManager::byKey('market::username'),
            'password' => ConfigManager::byKey('market::backupPassword'),
        ));
        $adapter = new \League\Flysystem\WebDAV\WebDAVAdapter($client);
        $filesystem = new \League\Flysystem\Filesystem($adapter);
        $folders = $filesystem->listContents('/remote.php/webdav/');
        $found = false;
        if (count($folders) > 0) {
            foreach ($folders as $folder) {
                if ($folder['basename'] == ConfigManager::byKey('market::cloud::backup::name')) {
                    $found = true;
                    break;
                }
            }
        }
        if (!$found) {
            $filesystem->createDir('/remote.php/webdav/' . ConfigManager::byKey('market::cloud::backup::name'));
        }
    }

    public static function backup_install()
    {
        if (exec('which duplicity | wc -l') == 0) {
            try {
                \com_shell::execute('sudo apt-get -y install duplicity');
            } catch (\Exception $e) {

            }
        }
    }

    public static function backup_errorAnalyzed($_error)
    {
        if (strpos($_error, 'decryption failed: Bad session key') !== false) {
            return __('Clef de chiffrement invalide. Si vous oubliez votre mot de passe aucune récupération n\'est possible. Veuillez supprimer le backup à partir de votre page profil sur le market');
        }
        return null;
    }

    public static function backup_clean($_nb = null)
    {
        if (ConfigManager::byKey('market::backupServer') == '' || ConfigManager::byKey('market::backupPassword') == '') {
            return;
        }
        if (ConfigManager::byKey('market::cloud::backup::password') == '') {
            return;
        }
        self::backup_install();
        shell_exec(SystemHelper::getCmdSudo() . ' rm -rf /tmp/duplicity-*-tempdir');
        if ($_nb == null) {
            $_nb = 0;
            $lists = self::backup_list();
            foreach ($lists as $name) {
                if (strpos($name, 'Full') !== false) {
                    $_nb++;
                }
            }
            $_nb = ($_nb - 2 < 1) ? 1 : $_nb - 2;
        }
        $cmd = SystemHelper::getCmdSudo() . ' PASSPHRASE="' . ConfigManager::byKey('market::cloud::backup::password') . '"';
        $cmd .= ' duplicity remove-all-but-n-full ' . $_nb . ' --force ';
        $cmd .= ' --ssl-no-check-certificate';
        $cmd .= ' --num-retries 1';
        $cmd .= ' "webdavs://' . ConfigManager::byKey('market::username') . ':' . ConfigManager::byKey('market::backupPassword');
        $cmd .= '@' . ConfigManager::byKey('market::backupServer') . '/remote.php/webdav/' . ConfigManager::byKey('market::cloud::backup::name') . '"';
        try {
            \com_shell::execute($cmd);
        } catch (\Exception $e) {
            if (self::backup_errorAnalyzed($e->getMessage()) != null) {
                throw new CoreException('[restore cloud] ' . self::backup_errorAnalyzed($e->getMessage()));
            }
            throw new CoreException('[restore cloud] ' . $e->getMessage());
        }
    }

    public static function backup_list()
    {
        if (ConfigManager::byKey('market::backupServer') == '' || ConfigManager::byKey('market::backupPassword') == '') {
            return array();
        }
        if (ConfigManager::byKey('market::cloud::backup::password') == '') {
            return array();
        }
        self::backup_createFolderIsNotExist();
        self::backup_install();
        $return = array();
        $cmd = SystemHelper::getCmdSudo();
        $cmd .= ' duplicity collection-status';
        $cmd .= ' --ssl-no-check-certificate';
        $cmd .= ' --num-retries 1';
        $cmd .= ' --timeout 60';
        $cmd .= ' "webdavs://' . ConfigManager::byKey('market::username') . ':' . ConfigManager::byKey('market::backupPassword');
        $cmd .= '@' . ConfigManager::byKey('market::backupServer') . '/remote.php/webdav/' . ConfigManager::byKey('market::cloud::backup::name') . '"';
        try {
            $results = explode("\n", \com_shell::execute($cmd));
        } catch (\Exception $e) {
            shell_exec(SystemHelper::getCmdSudo() . ' rm -rf ~/.cache/duplicity/*');
            $results = explode("\n", \com_shell::execute($cmd));
        }
        foreach ($results as $line) {
            if (strpos($line, 'Full') === false && strpos($line, 'Incremental') === false && strpos($line, 'Complète') === false && strpos($line, 'Incrémentale') === false) {
                continue;
            }
            $return[] = trim(substr($line, 0, -1));
        }
        return array_reverse($return);
    }

    public static function backup_restore($_backup)
    {
        $backup_dir = calculPath(ConfigManager::byKey('backup::path'));
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0770, true);
        }
        if (!is_writable($backup_dir)) {
            throw new CoreException('Impossible d\'accéder au dossier de sauvegarde. Veuillez vérifier les droits : ' . $backup_dir);
        }
        $restore_dir = '/tmp/nextdom_cloud_restore';
        if (file_exists($restore_dir)) {
            \com_shell::execute(SystemHelper::getCmdSudo() . ' rm -rf ' . $restore_dir);
        }
        self::backup_install();
        $base_dir = '/usr/jeedom_duplicity';
        if (!file_exists($base_dir)) {
            mkdir($base_dir);
        }
        mkdir($restore_dir);
        $timestamp = strtotime(trim(str_replace(array('Full', 'Incremental'), '', $_backup)));
        $backup_name = str_replace(' ', '_', 'backup-cloud-' . ConfigManager::byKey('market::cloud::backup::name') . '-' . date("Y-m-d-H\hi", $timestamp) . '.tar.gz');
        $cmd = SystemHelper::getCmdSudo() . ' PASSPHRASE="' . ConfigManager::byKey('market::cloud::backup::password') . '"';
        $cmd .= ' duplicity --file-to-restore /';
        $cmd .= ' --time ' . $timestamp;
        $cmd .= ' --num-retries 1';
        $cmd .= ' --tempdir ' . $base_dir . '/tmp';
        $cmd .= ' "webdavs://' . ConfigManager::byKey('market::username') . ':' . ConfigManager::byKey('market::backupPassword');
        $cmd .= '@' . ConfigManager::byKey('market::backupServer') . '/remote.php/webdav/' . ConfigManager::byKey('market::cloud::backup::name') . '"';
        $cmd .= ' ' . $restore_dir;
        try {
            \com_shell::execute($cmd);
        } catch (\Exception $e) {
            if (self::backup_errorAnalyzed($e->getMessage()) != null) {
                throw new CoreException('[restore cloud] ' . self::backup_errorAnalyzed($e->getMessage()));
            }
            throw new CoreException('[restore cloud] ' . $e->getMessage());
        }
        shell_exec(SystemHelper::getCmdSudo() . ' rm -rf ' . $base_dir);
        system('cd ' . $restore_dir . ';tar cfz "' . $backup_dir . '/' . $backup_name . '" . > /dev/null');
        if (file_exists($restore_dir)) {
            \com_shell::execute(SystemHelper::getCmdSudo() . ' rm -rf ' . $restore_dir);
        }
        BackupManager::restore($backup_dir . '/' . $backup_name, true);
    }

    public static function cronHourly()
    {
        if (strtotime(ConfigManager::byKey('market::lastCommunication', 'core', 0)) > (strtotime('now') - (24 * 3600))) {
            return;
        }
        sleep(rand(0, 1800));
        try {
            self::test();
        } catch (\Exception $e) {

        }
    }

    public static function test()
    {
        $market = self::getJsonRpc();
        if ($market->sendRequest('market::test')) {
            return $market->getResult();
        } else {
            throw new CoreException($market->getError(), $market->getErrorCode());
        }
    }

    public static function cron5()
    {
        try {
            $monitoring_state = self::monitoring_status();
            if (self::monitoring_allow() && !$monitoring_state) {
                self::monitoring_start();
            }
            if (!self::monitoring_allow() && $monitoring_state) {
                self::monitoring_stop();
            }
        } catch (\Exception $e) {

        }
    }

    public static function monitoring_status()
    {
        return (count(SystemHelper::ps('zabbix')) > 0);
    }

    public static function monitoring_allow()
    {
        if (ConfigManager::byKey('market::monitoringServer') == '') {
            return false;
        }
        if (ConfigManager::byKey('market::monitoringName') == '') {
            return false;
        }
        return true;
    }

    public static function monitoring_start()
    {
        preg_match_all('/(\d\.\d\.\d)/m', shell_exec(SystemHelper::getCmdSudo() . ' zabbix_agentd -V'), $matches);
        self::monitoring_install();
        $cmd = SystemHelper::getCmdSudo() . " chmod -R 777 /etc/zabbix;";
        $cmd .= SystemHelper::getCmdSudo() . " sed -i '/ServerActive=/d' /etc/zabbix/zabbix_agentd.conf;";
        $cmd .= SystemHelper::getCmdSudo() . " sed -i '/Hostname=/d' /etc/zabbix/zabbix_agentd.conf;";
        $cmd .= SystemHelper::getCmdSudo() . " sed -i '/TLSConnect=/d' /etc/zabbix/zabbix_agentd.conf;";
        $cmd .= SystemHelper::getCmdSudo() . " sed -i '/TLSAccept=/d' /etc/zabbix/zabbix_agentd.conf;";
        $cmd .= SystemHelper::getCmdSudo() . " sed -i '/TLSPSKIdentity=/d' /etc/zabbix/zabbix_agentd.conf;";
        $cmd .= SystemHelper::getCmdSudo() . " sed -i '/TLSPSKFile=/d' /etc/zabbix/zabbix_agentd.conf;";
        $cmd .= SystemHelper::getCmdSudo() . ' echo "ServerActive=' . ConfigManager::byKey('market::monitoringServer') . '" >> /etc/zabbix/zabbix_agentd.conf;';
        $cmd .= SystemHelper::getCmdSudo() . ' echo "Hostname=' . ConfigManager::byKey('market::monitoringName') . '" >> /etc/zabbix/zabbix_agentd.conf;';
        if (!isset($matches[0]) || !isset($matches[0][0]) || version_compare($matches[0][0], '3.0.0') >= 0) {
            $cmd .= SystemHelper::getCmdSudo() . ' echo "TLSConnect=psk" >> /etc/zabbix/zabbix_agentd.conf;';
            $cmd .= SystemHelper::getCmdSudo() . ' echo "TLSAccept=psk" >> /etc/zabbix/zabbix_agentd.conf;';
            $cmd .= SystemHelper::getCmdSudo() . ' echo "TLSPSKIdentity=' . ConfigManager::byKey('market::monitoringPskIdentity') . '" >> /etc/zabbix/zabbix_agentd.conf;';
            $cmd .= SystemHelper::getCmdSudo() . ' echo "TLSPSKFile=/etc/zabbix/zabbix_psk" >> /etc/zabbix/zabbix_agentd.conf;';
            $cmd .= SystemHelper::getCmdSudo() . ' echo "' . ConfigManager::byKey('market::monitoringPsk') . '" > /etc/zabbix/zabbix_psk;';
        }
        if (!file_exists('/var/log/zabbix')) {
            $cmd .= SystemHelper::getCmdSudo() . ' mkdir /var/log/zabbix;';
        }
        $cmd .= SystemHelper::getCmdSudo() . ' chmod 777 -R /var/log/zabbix;';
        if (!file_exists('/var/log/zabbix-agent')) {
            $cmd .= SystemHelper::getCmdSudo() . ' mkdir /var/log/zabbix-agent;';
        }
        $cmd .= SystemHelper::getCmdSudo() . ' chmod 777 -R /var/log/zabbix-agent;';
        if (!file_exists('/etc/zabbix/zabbix_agentd.conf.d')) {
            $cmd .= SystemHelper::getCmdSudo() . ' mkdir /etc/zabbix/zabbix_agentd.conf.d;';
            $cmd .= SystemHelper::getCmdSudo() . ' chmod 777 -R /etc/zabbix/zabbix_agentd.conf.d;';
        }
        $cmd .= SystemHelper::getCmdSudo() . ' systemctl restart zabbix-agent;';
        $cmd .= SystemHelper::getCmdSudo() . ' systemctl enable zabbix-agent;';
        shell_exec($cmd);
    }

    /******************************MONITORING********************************/

    public static function monitoring_install()
    {
        if (file_exists('/etc/zabbix')) {
            return;
        }
        $logfile = LogHelper::getPathToLog('market_zabbix_installation');
        if (strpos(php_uname(), 'x86_64') !== false) {
            if (file_exists('/etc/debian_version')) {
                $deb_version = file_get_contents('/etc/debian_version');
                if (version_compare($deb_version, '9', '>=')) {
                    shell_exec('cd /tmp/;' . SystemHelper::getCmdSudo() . ' wget http://repo.zabbix.com/zabbix/4.0/debian/pool/main/z/zabbix-release/zabbix-release_4.0-2%2Bstretch_all.deb >> ' . $logfile . ' 2>&1;' . SystemHelper::getCmdSudo() . ' dpkg -i zabbix-release_3.4-1+stretch_all.deb  >> ' . $logfile . ' 2>&1;' . SystemHelper::getCmdSudo() . ' rm zabbix-release_3.4-1+stretch_all.deb  >> ' . $logfile . ' 2>&1');
                } else {
                    shell_exec('cd /tmp/;' . SystemHelper::getCmdSudo() . ' wget http://repo.zabbix.com/zabbix/4.0/debian/pool/main/z/zabbix-release/zabbix-release_4.0-2%2Bjessie_all.deb  >> ' . $logfile . ' 2>&1;' . SystemHelper::getCmdSudo() . ' dpkg -i zabbix-release_3.4-1+jessie_all.deb  >> ' . $logfile . ' 2>&1;' . SystemHelper::getCmdSudo() . ' rm zabbix-release_3.4-1+jessie_all.deb  >> ' . $logfile . ' 2>&1');
                }
            }
        }
        shell_exec(SystemHelper::getCmdSudo() . ' apt-get update  >> ' . $logfile . ' 2>&1');
        shell_exec(SystemHelper::getCmdSudo() . ' apt-get -y install zabbix-agent  >> ' . $logfile . ' 2>&1');
    }

    /*     * *********************Methode d'instance************************* */

    public static function monitoring_stop()
    {
        $cmd = SystemHelper::getCmdSudo() . ' systemctl stop zabbix-agent;';
        $cmd .= SystemHelper::getCmdSudo() . ' systemctl disable zabbix-agent;';
        shell_exec($cmd);
    }

    /*******************************health********************************/

    public static function health()
    {
        $return = array();
        if (ConfigManager::byKey('market::monitoringServer') != '') {
            $monitoring_state = self::monitoring_status();
            $return[] = array(
                'name' => __('Cloud monitoring actif'),
                'state' => $monitoring_state,
                'result' => ($monitoring_state) ? __('OK') : __('NOK'),
                'comment' => ($monitoring_state) ? '' : __('Attendez 10 minutes si le service ne redémarre pas contacter le support'),
            );
        }
        return $return;
    }

    public static function saveTicket($_ticket)
    {
        $jsonrpc = self::getJsonRpc();
        $_ticket['user_plugin'] = '';
        foreach (PluginManager::listPlugin() as $plugin) {
            $_ticket['user_plugin'] .= $plugin->getId();
            $update = $plugin->getUpdate();
            if (is_object($update)) {
                $_ticket['user_plugin'] .= '[' . $update->getConfiguration('version', 'stable') . ',' . $update->getSource() . ',' . $update->getLocalVersion() . ']';
            }
            $_ticket['user_plugin'] .= ',';
        }
        trim($_ticket['user_plugin'], ',');
        if (isset($_ticket['options']['page'])) {
            $_ticket['options']['page'] = substr($_ticket['options']['page'], strpos($_ticket['options']['page'], 'index.php'));
        }
        $_ticket['options']['nextdom_version'] = NextDomHelper::getNextdomVersion();
        $_ticket['options']['uname'] = shell_exec('uname -a');
        if (!$jsonrpc->sendRequest('ticket::save', array('ticket' => $_ticket), 300)) {
            throw new CoreException($jsonrpc->getErrorMessage());
        }
        if ($_ticket['openSupport'] == 1) {
            UserManager::supportAccess(true);
        }
        return $jsonrpc->getResult();
    }

    public static function supportAccess($_enable = true, $_key = '')
    {
        $jsonrpc = self::getJsonRpc();
        $url = NetworkHelper::getNetworkAccess('external') . '/index.php?auth=' . $_key;
        if (!$jsonrpc->sendRequest('register::supportAccess', array('enable' => $_enable, 'urlSupport' => $url))) {
            throw new CoreException($jsonrpc->getErrorMessage());
        }
    }

    public static function getPurchaseInfo()
    {
        $market = self::getJsonRpc();
        if ($market->sendRequest('purchase::getInfo')) {
            return $market->getResult();
        }
        return null;
    }

    /*     * **********************Getteur Setteur*************************** */

    public static function distinctCategorie($_type)
    {
        $market = self::getJsonRpc();
        if ($market->sendRequest('market::distinctCategorie', array('type' => $_type))) {
            return $market->getResult();
        } else {
            throw new CoreException($market->getError(), $market->getErrorCode());
        }
    }

    public static function postJsonRpc(&$_result)
    {
        ConfigManager::save('market::lastCommunication', date('Y-m-d H:i:s'));
        if (is_array($_result)) {
            $restart_dns = false;
            $restart_monitoring = false;
            if (isset($_result['register::dnsToken']) && ConfigManager::byKey('dns::token') != $_result['register::dnsToken']) {
                ConfigManager::save('dns::token', $_result['register::dnsToken']);
                $restart_dns = true;
            }
            if (isset($_result['register::dnsNumber']) && ConfigManager::byKey('dns::number') != $_result['register::dnsNumber']) {
                ConfigManager::save('dns::number', $_result['register::dnsNumber']);
                $restart_dns = true;
            }
            if (isset($_result['register::vpnurl']) && ConfigManager::byKey('dns::vpnurl') != $_result['register::vpnurl']) {
                ConfigManager::save('dns::vpnurl', $_result['register::vpnurl']);
                $restart_dns = true;
            }
            if (isset($_result['register::vpnPort']) && ConfigManager::byKey('vpn::port') != $_result['register::vpnPort']) {
                ConfigManager::save('vpn::port', $_result['register::vpnPort']);
                $restart_dns = true;
            }
            if (isset($_result['user::backupServer']) && ConfigManager::byKey('market::backupServer') != $_result['user::backupServer']) {
                ConfigManager::save('market::backupServer', $_result['user::backupServer']);
                $restart_monitoring = true;
            }
            if (isset($_result['user::backupPassword']) && ConfigManager::byKey('market::backupPassword') != $_result['user::backupPassword']) {
                ConfigManager::save('market::backupPassword', $_result['user::backupPassword']);
                $restart_monitoring = true;
            }
            if (isset($_result['user::monitoringServer']) && ConfigManager::byKey('market::monitoringServer') != $_result['user::monitoringServer']) {
                ConfigManager::save('market::monitoringServer', $_result['user::monitoringServer']);
                $restart_monitoring = true;
            }
            if (isset($_result['register::monitoringPsk']) && ConfigManager::byKey('market::monitoringPsk') != $_result['register::monitoringPsk']) {
                ConfigManager::save('market::monitoringPsk', $_result['register::monitoringPsk']);
                $restart_monitoring = true;
            }
            if (isset($_result['register::monitoringPskIdentity']) && ConfigManager::byKey('market::monitoringPskIdentity') != $_result['register::monitoringPskIdentity']) {
                ConfigManager::save('market::monitoringPskIdentity', $_result['register::monitoringPskIdentity']);
                $restart_monitoring = true;
            }
            if (isset($_result['register::monitoringName']) && ConfigManager::byKey('market::monitoringName') != $_result['register::monitoringName']) {
                ConfigManager::save('market::monitoringName', $_result['register::monitoringName']);
                $restart_monitoring = true;
            }
            if ($restart_monitoring) {
                self::monitoring_stop();
            }
            if ($restart_dns && ConfigManager::byKey('market::allowDNS') == 1) {
                NetworkHelper::dnsStart();
            }
            if (ConfigManager::byKey('market::allowDNS') == 1 && isset($_result['NextDomHelper::url']) && ConfigManager::byKey('NextDomHelper::url') != $_result['NextDomHelper::url']) {
                ConfigManager::save('NextDomHelper::url', $_result['NextDomHelper::url']);
            }
            if (isset($_result['register::hwkey_nok']) && $_result['register::hwkey_nok'] == 1) {
                ConfigManager::save('NextDomHelper::installKey', '');
            }
        }
    }

    public static function byId($_id)
    {
        $market = self::getJsonRpc();
        if ($market->sendRequest('market::byId', array('id' => $_id))) {
            return self::construct($market->getResult());
        } else {
            throw new CoreException($market->getError(), $market->getErrorCode());
        }
    }

    public static function byMe()
    {
        $market = self::getJsonRpc();
        if ($market->sendRequest('market::byAuthor', array())) {
            $return = array();
            foreach ($market->getResult() as $result) {
                if (isset($result['id'])) {
                    $return[] = self::construct($result);
                }
            }
            return $return;
        } else {
            throw new CoreException($market->getError(), $market->getErrorCode());
        }
    }

    public static function byStatusAndType($_status, $_type)
    {
        $market = self::getJsonRpc();
        if ($market->sendRequest('market::byStatusAndType', array('status' => $_status, 'type' => $_type))) {
            $return = array();
            foreach ($market->getResult() as $result) {
                if (isset($result['id'])) {
                    $return[] = self::construct($result);
                }
            }
            return $return;
        } else {
            LogHelper::add('market', 'debug', print_r($market, true));
            throw new CoreException($market->getError(), $market->getErrorCode());
        }
    }

    public static function byStatus($_status)
    {
        $market = self::getJsonRpc();
        if ($market->sendRequest('market::byStatus', array('status' => $_status))) {
            $return = array();
            foreach ($market->getResult() as $result) {
                if (isset($result['id'])) {
                    $return[] = self::construct($result);
                }
            }
            return $return;
        } else {
            throw new CoreException($market->getError(), $market->getErrorCode());
        }
    }

    public static function byFilter($_filter)
    {
        $market = self::getJsonRpc();
        if ($market->sendRequest('market::byFilter', $_filter)) {
            $return = array();
            foreach ($market->getResult() as $result) {
                if (isset($result['id'])) {
                    $return[] = self::construct($result);
                }
            }
            return $return;
        } else {
            throw new CoreException($market->getError(), $market->getErrorCode());
        }
    }

    public function getRating($_key = 'average')
    {
        $rating = $this->rating;
        if (isset($rating[$_key])) {
            return $rating[$_key];
        }
        return 0;
    }

    public function setRating($_rating)
    {
        $market = self::getJsonRpc();
        if (!$market->sendRequest('market::setRating', array('rating' => $_rating, 'id' => $this->getId()))) {
            throw new CoreException($market->getError());
        }
    }

    public function save()
    {
        $cache = CacheManager::byKey('market::info::' . $this->getLogicalId());
        if (is_object($cache)) {
            $cache->remove();
        }
        $market = self::getJsonRpc();
        $params = Utils::o2a($this);
        if (isset($params['changelog'])) {
            unset($params['changelog']);
        }
        switch ($this->getType()) {
            case 'plugin':
                $plugin_id = $this->getLogicalId();
                $cibDir = NextDomHelper::getTmpFolder('market') . '/' . $plugin_id;
                if (file_exists($cibDir)) {
                    rrmdir($cibDir);
                }
                mkdir($cibDir);
                $exclude = array('tmp', '.git', '.DStore');
                if (property_exists($plugin_id, '_excludeOnSendPlugin')) {
                    $exclude = array_merge($plugin_id::$_excludeOnSendPlugin);
                }
                exec('find ' . realpath(__DIR__ . '/../../plugins/' . $plugin_id) . ' -name "*.sh" -type f -exec dos2unix {} \;');
                rcopy(realpath(__DIR__ . '/../../plugins/' . $plugin_id), $cibDir, true, $exclude, true);
                if (file_exists($cibDir . '/data')) {
                    rrmdir($cibDir . '/data');
                }
                $tmp = NextDomHelper::getTmpFolder('market') . '/' . $plugin_id . '.zip';
                if (file_exists($tmp)) {
                    if (!unlink($tmp)) {
                        throw new CoreException(__('Impossible de supprimer : ') . $tmp . __('. Vérifiez les droits'));
                    }
                }
                if (!create_zip($cibDir, $tmp)) {
                    throw new CoreException(__('Echec de création de l\'archive zip'));
                }
                rrmdir($cibDir);
                break;
            default:
                $type = $this->getType();
                if (!class_exists($type) || !method_exists($type, 'shareOnMarket')) {
                    throw new CoreException(__('Aucune fonction correspondante à : ') . $type . '::shareOnMarket');
                }
                $tmp = $type::shareOnMarket($this);
                break;
        }
        if (!file_exists($tmp)) {
            throw new CoreException(__('Impossible de trouver le fichier à envoyer : ') . $tmp);
        }
        $file = array(
            'file' => '@' . realpath($tmp),
        );
        if (!$market->sendRequest('market::save', $params, 30, $file)) {
            throw new CoreException($market->getError());
        }
        unlink($tmp);
        $update = UpdateManager::byTypeAndLogicalId($this->getType(), $this->getLogicalId());
        if (!is_object($update)) {
            $update = new update();
            $update->setLogicalId($this->getLogicalId());
            $update->setType($this->getType());
        }
        if ($update->getSource() == 'market') {
            $update->setConfiguration('version', 'beta');
            $update->setLocalVersion(date('Y-m-d H:i:s', strtotime('+10 minute' . date('Y-m-d H:i:s'))));
            $update->save();
        }
        $update->checkUpdate();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getCategorie()
    {
        return $this->categorie;
    }

    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getDownloaded()
    {
        return $this->downloaded;
    }

    public function setDownloaded($downloaded)
    {
        $this->downloaded = $downloaded;
        return $this;
    }

    public function getStatus($_key = '', $_default = '')
    {
        return utils::getJsonAttr($this->status, $_key, $_default);
    }

    public function setStatus($_key, $_value)
    {
        $this->status = utils::setJsonAttr($this->status, $_key, $_value);
        return $this;
    }

    public function getLink($_key = '', $_default = '')
    {
        return utils::getJsonAttr($this->link, $_key, $_default);
    }

    public function setLink($_key, $_value)
    {
        $this->link = utils::setJsonAttr($this->link, $_key, $_value);
        return $this;
    }

    public function getLanguage($_key = '', $_default = '')
    {
        return utils::getJsonAttr($this->language, $_key, $_default);
    }

    public function setLanguage($_key, $_value)
    {
        $this->language = utils::setJsonAttr($this->language, $_key, $_value);
        return $this;
    }

    public function getImg($_key = '', $_default = '')
    {
        return utils::getJsonAttr($this->img, $_key, $_default);
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    public function getChangelog()
    {
        return $this->changelog;
    }

    public function setChangelog($changelog)
    {
        $this->changelog = $changelog;
        return $this;
    }

    public function getNbInstall()
    {
        return $this->nbInstall;
    }

    public function setNbInstall($nbInstall)
    {
        $this->nbInstall = $nbInstall;
        return $this;
    }

    public function getPrivate()
    {
        return $this->private;
    }

    public function setPrivate($private)
    {
        $this->private = $private;
        return $this;
    }

    public function getUtilization()
    {
        return $this->utilization;
    }

    public function setUtilization($utilization)
    {
        $this->utilization = $utilization;
        return $this;
    }

    public function getPurchase()
    {
        return $this->purchase;
    }

    public function setPurchase($purchase)
    {
        $this->purchase = $purchase;
        return $this;
    }

    public function getCost()
    {
        return $this->cost;
    }

    public function setCost($cost)
    {
        $this->cost = $cost;
        return $this;
    }

    public function getRealcost()
    {
        return $this->realcost;
    }

    public function setRealcost($realcost)
    {
        $this->realcost = $realcost;
        return $this;
    }

    public function getBuyer()
    {
        return $this->buyer;
    }

    public function setBuyer($buyer)
    {
        $this->buyer = $buyer;
        return $this;
    }

    public function getCertification()
    {
        return $this->certification;
    }

    public function setCertification($certification)
    {
        $this->certification = $certification;
        return $this;
    }

    public function getDoc()
    {
        return $this->doc;
    }

    public function setDoc($doc)
    {
        $this->doc = $doc;
        return $this;
    }

    public function getUpdateBy()
    {
        return $this->updateBy;
    }

    public function setUpdateBy($updateBy)
    {
        $this->updateBy = $updateBy;
        return $this;
    }

    public function getAllowVersion()
    {
        return $this->allowVersion;
    }

    public function setAllowVersion($allowVersion)
    {
        $this->allowVersion = $allowVersion;
        return $allowVersion;
    }

    public function getHardwareCompatibility($_key = '', $_default = '')
    {
        return utils::getJsonAttr($this->hardwareCompatibility, $_key, $_default);
    }

    public function setHardwareCompatibility($_key, $_value)
    {
        $this->hardwareCompatibility = utils::setJsonAttr($this->hardwareCompatibility, $_key, $_value);
        return $this;
    }

    public function getParameters($_key = '', $_default = '')
    {
        return utils::getJsonAttr($this->parameters, $_key, $_default);
    }

    public function setParameters($_key, $_value)
    {
        $this->parameters = utils::setJsonAttr($this->parameters, $_key, $_value);
        return $this;
    }

}
