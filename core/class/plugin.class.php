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
require_once dirname(__FILE__) . '/../../core/php/core.inc.php';

use NextDom\Managers\PluginManager;

class plugin {
    /*     * *************************Attributs****************************** */

    private $id;
    private $name = '';
    private $description = '';
    private $license = '';
    private $installation = '';
    private $author = '';
    private $require = '';
    private $category = '';
    private $filepath;
    private $index;
    private $display = '';
    private $mobile;
    private $eventjs = 0;
    private $hasDependency = 0;
    private $maxDependancyInstallTime = 30;
    private $hasOwnDeamon = 0;
    private $issue = '';
    private $changelog = '';
    private $documentation = '';
    private $info = array();
    private $include = array();
    private $functionality = array();
    private static $_cache = array();
    private static $_enable = null;

    /*     * ***********************Méthodes statiques*************************** */
    public function initPluginFromData($data) {
        global $NEXTDOM_INTERNAL_CONFIG;
        $this->setId($data['id']);
        $this->setName($data['name']);
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
        if (isset($data['licence'])) {
            $this->license = $data['licence'];
        }
        if (isset($data['license'])) {
            $this->license = $data['lisense'];
        }
        if (isset($data['author'])) {
            $this->author = $data['author'];
        }
        if (isset($data['installation'])) {
            $this->installation = $data['installation'];
        }
        if (isset($data['hasDependency'])) {
            $this->hasDependency = $data['hasDependency'];
        }
        if (isset($data['hasOwnDeamon'])) {
            $this->hasOwnDeamon = $data['hasOwnDeamon'];
        }
        if (isset($data['maxDependancyInstallTime'])) {
            $this->maxDependancyInstallTime = $data['maxDependancyInstallTime'];
        }
        if (isset($data['eventjs'])) {
            $this->eventjs = $data['eventjs'];
        }
        if (isset($data['require'])) {
            $this->require = $data['require'];
        }
        if (isset($data['category'])) {
            $this->category = $data['category'];
        }
        $this->filepath = $this->id;
        if (isset($data['index'])) {
            $this->index = $data['index'];
        }
        else {
            $this->index = $this->id;
        }
        if (isset($data['display'])) {
            $this->display = $data['display'];
        }
        if (isset($data['issue'])) {
            $this->issue = $data['issue'];
        }
        if (isset($data['changelog'])) {
            $this->changelog = str_replace('#language#', config::byKey('language', 'core', 'fr_FR'), $data['changelog']);
        }
        if (isset($data['documentation'])) {
            $this->documentation = str_replace('#language#', config::byKey('language', 'core', 'fr_FR'), $data['documentation']);
        }
        $this->mobile = '';
        if (file_exists(dirname(__FILE__) . '/../../plugins/' . $data['id'] . '/mobile/html')) {
            $this->mobile = (isset($data['mobile'])) ? $data['mobile'] : $data['id'];
        }
        if (isset($data['include'])) {
            $this->include = array(
                'file' => $data['include']['file'],
                'type' => $data['include']['type'],
            );
        } else {
            $this->include = array(
                'file' => $data['id'],
                'type' => 'class',
            );
        }
        $this->functionality['interact'] = method_exists($this->getId(), 'interact');
        $this->functionality['cron'] = method_exists($this->getId(), 'cron');
        $this->functionality['cron5'] = method_exists($this->getId(), 'cron5');
        $this->functionality['cron15'] = method_exists($this->getId(), 'cron15');
        $this->functionality['cron30'] = method_exists($this->getId(), 'cron30');
        $this->functionality['cronHourly'] = method_exists($this->getId(), 'cronHourly');
        $this->functionality['cronDaily'] = method_exists($this->getId(), 'cronDaily');
    }

    public static function byId($_id) {
        return PluginManager::byId($_id);
    }

    public static function getPathById($_id) {
        return PluginManager::getPathById($_id);
    }

    public static function listPlugin($_activateOnly = false, $_orderByCaterogy = false, $_translate = true, $_nameOnly = false) {
        return PluginManager::listPlugin($_activateOnly, $_orderByCaterogy, $_nameOnly);
    }

    public static function orderPlugin($a, $b) {
        return PluginManager::orderPlugin($a, $b);
    }

    public static function cron() {
        PluginManager::cron();
    }

    public static function cron5() {
        PluginManager::cron5();
    }

    public static function cron15() {
        PluginManager::cron15();
    }

    public static function cron30() {
        PluginManager::cron30();
    }

    public static function cronDaily() {
        PluginManager::cronDaily();
    }

    public static function cronHourly() {
        PluginManager::cronHourly();
    }

    public static function start() {
        PluginManager::start();
    }

    public static function stop() {
        PluginManager::stop();
    }

    public static function checkDeamon() {
        PluginManager::checkDeamon();
    }

    /*     * *********************Méthodes d'instance************************* */

    public function getPathToConfigurationById() {
        if (file_exists(__DIR__ . '/../../plugins/' . $this->id . '/plugin_info/configuration.php')) {
            return 'plugins/' . $this->id . '/plugin_info/configuration.php';
        } else {
            return '';
        }
    }

    public function report($_format = 'pdf', $_parameters = array()) {
        if ($this->getDisplay() == '') {
            throw new Exception(__('Vous ne pouvez pas faire de rapport sur un plugin sans panneau', __FILE__));
        }
        $url = network::getNetworkAccess('internal') . '/index.php?v=d&p=' . $this->getDisplay();
        $url .= '&m=' . $this->getId();
        $url .= '&report=1';
        return report::generate($url, 'plugin', $this->getId(), $_format, $_parameters);
    }

    public function isActive() {
        if (self::$_enable === null) {
            self::$_enable = config::getPluginEnable();
        }
        if (isset(self::$_enable[$this->id])) {
            return self::$_enable[$this->id];
        }
        return 0;
    }

    public function callInstallFunction($_function, $_direct = false) {
        if (!$_direct) {
            return $this->launch($_function, true);
        }
        if (strpos($_function, 'pre_') !== false) {
            log::add('plugin', 'debug', 'Recherche de ' . dirname(__FILE__) . '/../../plugins/' . $this->getId() . '/plugin_info/pre_install.php');
            if (file_exists(dirname(__FILE__) . '/../../plugins/' . $this->getId() . '/plugin_info/pre_install.php')) {
                log::add('plugin', 'debug', 'Fichier d\'installation trouvé pour  : ' . $this->getId());
                require_once dirname(__FILE__) . '/../../plugins/' . $this->getId() . '/plugin_info/pre_install.php';
                ob_start();
                $function = $this->getId() . '_' . $_function;
                if (function_exists($this->getId() . '_' . $_function)) {
                    $function();
                }
                return ob_get_clean();
            }
        } else {
            log::add('plugin', 'debug', 'Recherche de ' . dirname(__FILE__) . '/../../plugins/' . $this->getId() . '/plugin_info/install.php');
            if (file_exists(dirname(__FILE__) . '/../../plugins/' . $this->getId() . '/plugin_info/install.php')) {
                log::add('plugin', 'debug', 'Fichier d\'installation trouvé pour  : ' . $this->getId());
                require_once dirname(__FILE__) . '/../../plugins/' . $this->getId() . '/plugin_info/install.php';
                ob_start();
                $function = $this->getId() . '_' . $_function;
                if (function_exists($this->getId() . '_' . $_function)) {
                    $function();
                }
                return ob_get_clean();
            }
        }
    }

    public function dependancy_info($_refresh = false) {
        $plugin_id = $this->getId();
        if ($this->getHasDependency() != 1 || !method_exists($plugin_id, 'dependancy_info')) {
            return array('state' => 'nok', 'log' => 'nok');
        }
        $cache = cache::byKey('dependancy' . $this->getID());
        if ($_refresh) {
            $cache->remove();
        } else {
            $return = $cache->getValue();
            if (is_array($return) && $return['state'] == 'ok') {
                return $cache->getValue();
            }
        }
        $return = $plugin_id::dependancy_info();
        if (!isset($return['log'])) {
            $return['log'] = '';
        }
        if (isset($return['progress_file'])) {
            $return['progression'] = 0;
            if (@file_exists($return['progress_file'])) {
                $return['state'] = 'in_progress';
                $progression = trim(file_get_contents($return['progress_file']));
                if ($progression != '') {
                    $return['progression'] = $progression;
                }
            }
        }
        if ($return['state'] == 'in_progress') {
            if (config::byKey('lastDependancyInstallTime', $plugin_id) == '') {
                config::save('lastDependancyInstallTime', date('Y-m-d H:i:s'), $plugin_id);
            }
            $return['duration'] = round((strtotime('now') - strtotime(config::byKey('lastDependancyInstallTime', $plugin_id))) / 60);
        } else {
            $return['duration'] = -1;
        }
        $return['last_launch'] = config::byKey('lastDependancyInstallTime', $this->getId(), __('Inconnue', __FILE__));
        if ($return['state'] == 'ok') {
            cache::set('dependancy' . $this->getID(), $return);
        }
        return $return;
    }
    /**
     *
     * @return null
     * @throws Exception
     */
    public function dependancy_install() {
        $plugin_id = $this->getId();
        if ($this->getHasDependency() != 1 || !method_exists($plugin_id, 'dependancy_install')) {
            return;
        }
        if ((strtotime('now') - 60) <= strtotime(config::byKey('lastDependancyInstallTime', $plugin_id))) {
            $cache = cache::byKey('dependancy' . $this->getID());
            $cache->remove();
            throw new Exception(__('Vous devez attendre au moins 60 secondes entre deux lancements d\'installation de dépendances', __FILE__));
        }
        $dependancy_info = $this->dependancy_info(true);
        if ($dependancy_info['state'] == 'in_progress') {
            throw new Exception(__('Les dépendances sont déjà en cours d\'installation', __FILE__));
        }
        foreach (self::listPlugin(true) as $plugin) {
            if ($plugin->getId() == $this->getId()) {
                continue;
            }
            $dependancy_info = $plugin->dependancy_info();
            if ($dependancy_info['state'] == 'in_progress') {
                throw new Exception(__('Les dépendances d\'un autre plugin sont déjà en cours, veuillez attendre qu\'elles soient finies : ', __FILE__) . $plugin->getId());
            }
        }
        $cmd = $plugin_id::dependancy_install();
        if (is_array($cmd) && count($cmd) == 2) {
            $script = str_replace('#stype#', system::get('type'), $cmd['script']);
            $script_array = explode(' ', $script);
            if (file_exists($script_array[0])) {
                if (nextdom::isCapable('sudo')) {
                    $this->deamon_stop();
                    message::add($plugin_id, __('Attention : installation des dépendances lancée', __FILE__));
                    config::save('lastDependancyInstallTime', date('Y-m-d H:i:s'), $plugin_id);
                    exec(system::getCmdSudo() . '/bin/bash ' . $script . ' >> ' . $cmd['log'] . ' 2>&1 &');
                    sleep(1);
                } else {
                    log::add($plugin_id, 'error', __('Veuillez exécuter le script : ', __FILE__) . '/bin/bash ' . $script);
                }
            } else {
                log::add($plugin_id, 'error', __('Aucun script ne correspond à votre type de Linux : ', __FILE__) . $cmd['script'] . __(' avec #stype# : ', __FILE__) . system::get('type'));
            }
        }
        $cache = cache::byKey('dependancy' . $this->getID());
        $cache->remove();
        return;
    }

    public function deamon_changeAutoMode($_mode) {
        config::save('deamonAutoMode', $_mode, $this->getId());
        $plugin_id = $this->getId();
        if (method_exists($plugin_id, 'deamon_changeAutoMode')) {
            $plugin_id::deamon_changeAutoMode($_mode);
        }
    }
    /**
     *
     * @return array
     */
    public function deamon_info() {

        $plugin_id = $this->getId();
        if ($this->getHasOwnDeamon() != 1 || !method_exists($plugin_id, 'deamon_info')) {
            return array('launchable_message' => '', 'launchable' => 'nok', 'state' => 'nok', 'log' => 'nok', 'auto' => 0);
        }
        $return = $plugin_id::deamon_info();
        if ($this->getHasDependency() == 1 && method_exists($plugin_id, 'dependancy_info') && $return['launchable'] == 'ok') {
            $dependancy_info = $this->dependancy_info();
            if ($dependancy_info['state'] != 'ok') {
                $return['launchable'] = 'nok';
                if ($dependancy_info['state'] == 'in_progress') {
                    $return['launchable_message'] = __('Dépendances en cours d\'installation', __FILE__);
                } else {
                    $return['launchable_message'] = __('Dépendances non installées', __FILE__);
                }
            }
        }
        if (!isset($return['launchable_message'])) {
            $return['launchable_message'] = '';
        }
        if (!isset($return['log'])) {
            $return['log'] = '';
        }
        $return['auto'] = config::byKey('deamonAutoMode', $this->getId(), 1);
        if ($return['auto'] == 0) {
            $return['launchable_message'] = __('Gestion automatique désactivée', __FILE__);
        }
        if (config::byKey('enableCron', 'core', 1, true) == 0) {
            $return['launchable'] = 'nok';
            $return['launchable_message'] = __('Les crons et démons sont désactivés', __FILE__);
        }
        if (!nextdom::isStarted()) {
            $return['launchable'] = 'nok';
            $return['launchable_message'] = __('NextDom n\'est pas encore démarré', __FILE__);
        }
        $return['last_launch'] = config::byKey('lastDeamonLaunchTime', $this->getId(), __('Inconnue', __FILE__));
        return $return;
    }

    public function deamon_start($_forceRestart = false, $_auto = false) {
        $plugin_id = $this->getId();
        if ($_forceRestart) {
            $this->deamon_stop();
        }
        try {
            if ($this->getHasOwnDeamon() == 1 && method_exists($plugin_id, 'deamon_info')) {
                $deamon_info = $this->deamon_info();
                if ($_auto && $deamon_info['auto'] == 0) {
                    return;
                }
                if ($deamon_info['launchable'] == 'ok' && $deamon_info['state'] == 'nok' && method_exists($plugin_id, 'deamon_start')) {
                    $inprogress = cache::byKey('deamonStart' . $this->getId() . 'inprogress');
                    $info = $inprogress->getValue(array('datetime' => strtotime('now')-60));
                    $info['datetime'] = (isset($info['datetime'])) ? $info['datetime'] : strtotime('now')-60;
                    if (abs(strtotime('now') - $info['datetime']) < 45) {
                        throw new Exception(__('Vous devez attendre au moins 45 secondes entre deux lancements du démon. Dernier lancement : ' .date("Y-m-d H:i:s",$info['datetime']), __FILE__));
                    }
                    cache::set('deamonStart' . $this->getId() . 'inprogress', array('datetime' => strtotime('now')));
                    config::save('lastDeamonLaunchTime', date('Y-m-d H:i:s'), $plugin_id);
                    $plugin_id::deamon_start();
                }
            }
        } catch (Exception $e) {
            log::add($plugin_id, 'error', __('Erreur sur la fonction deamon_start du plugin : ', __FILE__) . $e->getMessage());
        } catch (Error $e) {
            log::add($plugin_id, 'error', __('Erreur sur la fonction deamon_start du plugin : ', __FILE__) . $e->getMessage());
        }
    }

    public function deamon_stop() {
        $plugin_id = $this->getId();
        try {
            if ($this->getHasOwnDeamon() == 1 && method_exists($plugin_id, 'deamon_info')) {
                $deamon_info = $this->deamon_info();
                if ($deamon_info['state'] == 'ok' && method_exists($plugin_id, 'deamon_stop')) {
                    $plugin_id::deamon_stop();
                }
            }
        } catch (Exception $e) {
            log::add($plugin_id, 'error', __('Erreur sur la fonction deamon_stop du plugin : ', __FILE__) . $e->getMessage());
        } catch (Error $e) {
            log::add($plugin_id, 'error', __('Erreur sur la fonction deamon_stop du plugin : ', __FILE__) . $e->getMessage());
        }
    }

    public function setIsEnable($_state) {
        if (version_compare(nextdom::version(), $this->getRequire()) == -1 && $_state == 1) {
            throw new Exception(__('Votre version de NextDom n\'est pas assez récente pour activer ce plugin', __FILE__));
        }
        $alreadyActive = config::byKey('active', $this->getId(), 0);
        if ($_state == 1) {
            config::save('active', $_state, $this->getId());
        }
        $deamonAutoState = config::byKey('deamonAutoMode', $this->getId(), 1);
        config::save('deamonAutoMode', 0, $this->getId());
        if ($_state == 0) {
            $eqLogics = eqLogic::byType($this->getId());
            if (is_array($eqLogics)) {
                foreach ($eqLogics as $eqLogic) {
                    try {
                        $eqLogic->setConfiguration('previousIsEnable', $eqLogic->getIsEnable());
                        $eqLogic->setConfiguration('previousIsVisible', $eqLogic->getIsVisible());
                        $eqLogic->setIsEnable(0);
                        $eqLogic->setIsVisible(0);
                        $eqLogic->save();
                    } catch (Exception $e) {

                    } catch (Error $e) {

                    }
                }
            }
            $listeners = listener::byClass($this->getId());
            if (is_array($listeners)) {
                foreach ($listeners as $listener) {
                    $listener->remove();
                }
            }
        } else if ($alreadyActive == 0 && $_state == 1) {
            foreach (eqLogic::byType($this->getId()) as $eqLogic) {
                try {
                    $eqLogic->setIsEnable($eqLogic->getConfiguration('previousIsEnable', 1));
                    $eqLogic->setIsVisible($eqLogic->getConfiguration('previousIsVisible', 1));
                    $eqLogic->save();
                } catch (Exception $e) {

                } catch (Error $e) {

                }
            }
        }
        try {
            if ($_state == 1) {
                log::add($this->getId(), 'info', 'Début d\'activation du plugin');
                $this->deamon_stop();

                $deamon_info = $this->deamon_info();
                sleep(1);
                log::add($this->getId(), 'info', 'Info sur le démon : ' . print_r($deamon_info, true));
                if ($deamon_info['state'] == 'ok') {
                    $this->deamon_stop();
                }
                if ($alreadyActive == 1) {
                    $out = $this->callInstallFunction('update');
                } else {
                    $out = $this->callInstallFunction('install');
                }
                $this->dependancy_info(true);
            } else {
                $this->deamon_stop();
                if ($alreadyActive == 1) {
                    $out = $this->callInstallFunction('remove');
                }
                rrmdir(nextdom::getTmpFolder('openvpn'));
            }
            if (isset($out) && trim($out) != '') {
                log::add($this->getId(), 'info', "Installation/remove/update result : " . $out);
            }
        } catch (Exception $e) {
            config::save('active', $alreadyActive, $this->getId());
            log::add('plugin', 'error', $e->getMessage());
            throw $e;
        } catch (Error $e) {
            config::save('active', $alreadyActive, $this->getId());
            log::add('plugin', 'error', $e->getMessage());
            throw $e;
        }
        if ($_state == 0) {
            config::save('active', $_state, $this->getId());
        }
        if ($deamonAutoState) {
            config::save('deamonAutoMode', 1, $this->getId());
        }
        if ($alreadyActive == 0 && $_state == 1) {
            config::save('log::level::' . $this->getId(), '{"100":"0","200":"0","300":"0","400":"0","1000":"0","default":"1"}');
        }
        return true;
    }

    public function launch($_function, $_callInstallFunction = false) {
        if ($_function == '') {
            throw new Exception('La fonction à lancer ne peut être vide');
        }
        if (!$_callInstallFunction && (!class_exists($this->getId()) || !method_exists($this->getId(), $_function))) {
            throw new Exception('Il n\'existe aucune méthode : ' . $this->getId() . '::' . $_function . '()');
        }
        $cmd = dirname(__FILE__) . '/../../core/php/jeePlugin.php ';
        $cmd .= ' plugin_id=' . $this->getId();
        $cmd .= ' function=' . $_function;
        $cmd .= ' callInstallFunction=' . $_callInstallFunction;
        if (nextdom::checkOngoingThread($cmd) > 0) {
            return true;
        }
        log::add($this->getId(), 'debug', __('Lancement de : ', __FILE__) . $cmd);
        if ($_callInstallFunction) {
            return system::php($cmd . ' >> /dev/null 2>&1');
        } else {
            system::php($cmd . ' >> /dev/null 2>&1 &');
        }
        return true;
    }

    public function getTranslation($_language) {
        $dir = dirname(__FILE__) . '/../../plugins/' . $this->getId() . '/core/i18n';
        if (!file_exists($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!file_exists($dir)) {
            return array();
        }
        if (file_exists($dir . '/' . $_language . '.json')) {
            $return = file_get_contents($dir . '/' . $_language . '.json');

            if (is_json($return)) {
                return json_decode($return, true);
            } else {
                return array();
            }
        }
        return array();
    }

    public function saveTranslation($_language, $_translation) {
        $dir = dirname(__FILE__) . '/../../plugins/' . $this->getId() . '/core/i18n';
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($dir . '/' . $_language . '.json', json_encode($_translation, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function getUpdate() {
        return update::byTypeAndLogicalId('plugin', $this->getId());
    }

    public function getPathImgIcon() {
        if (file_exists(dirname(__FILE__) . '/../../plugins/' . $this->getId() . '/plugin_info/' . $this->getId() . '_icon.png')) {
            return 'plugins/' . $this->getId() . '/plugin_info/' . $this->getId() . '_icon.png';
        }
        if (file_exists(dirname(__FILE__) . '/../../plugins/' . $this->getId() . '/doc/images/' . $this->getId() . '_icon.png')) {
            return 'plugins/' . $this->getId() . '/doc/images/' . $this->getId() . '_icon.png';
        }
        if (file_exists(dirname(__FILE__) . '/../../plugins/' . $this->getId() . '/plugin_info/' . strtolower($this->getId()) . '_icon.png')) {
            return 'plugins/' . $this->getId() . '/plugin_info/' . strtolower($this->getId()) . '_icon.png';
        }
        if (file_exists(dirname(__FILE__) . '/../../plugins/' . $this->getId() . '/doc/images/' . strtolower($this->getId()) . '_icon.png')) {
            return 'plugins/' . $this->getId() . '/doc/images/' . strtolower($this->getId()) . '_icon.png';
        }
        return 'core/img/no-image-plugin.png';
    }

    public function getLogList() {
        $return = array();
        foreach (ls(log::getPathToLog(''), '*') as $log) {
            if ($log == $this->getId()) {
                $return[] = $log;
                continue;
            }
            if (strpos($log, $this->getId()) === 0) {
                $return[] = $log;
                continue;
            }

        }
        return $return;
    }

    /*     * **********************Getteur Setteur*************************** */

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return nl2br($this->description);
    }

    public function getInfo($_name = '', $_default = '') {
        if (count($this->info) == 0) {
            $update = update::byLogicalId($this->id);
            if (is_object($update)) {
                $this->info = $update->getInfo();
            }
        }
        if ($_name !== '') {
            if (isset($this->info[$_name])) {
                return $this->info[$_name];
            }
            return $_default;
        }
        return $this->info;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getRequire() {
        return $this->require;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getLicense() {
        return $this->license;
    }

    public function getFilepath() {
        return $this->filepath;
    }

    public function getInstallation() {
        return nl2br($this->installation);
    }

    public function getIndex() {
        return $this->index;
    }

    public function getInclude() {
        return $this->include;
    }

    public function getDisplay() {
        return $this->display;
    }

    public function setDisplay($display) {
        $this->display = $display;
        return $this;
    }

    public function getMobile() {
        return $this->mobile;
    }

    public function setMobile($mobile) {
        $this->mobile = $mobile;
        return $this;
    }

    public function getEventjs() {
        return $this->eventjs;
    }

    public function setEventjs($eventjs) {
        $this->eventjs = $eventjs;
        return $this;
    }

    public function getHasDependency() {
        return $this->hasDependency;
    }

    public function setHasDependency($hasDependency) {
        $this->hasDependency = $hasDependency;
        return $this;
    }

    public function getHasOwnDeamon() {
        return $this->hasOwnDeamon;
    }

    public function setHasOwnDeamony($hasOwnDeamon) {
        $this->hasOwnDeamon = $hasOwnDeamon;
        return $this;
    }

    public function getMaxDependancyInstallTime() {
        return $this->maxDependancyInstallTime;
    }

    public function setMaxDependancyInstallTime($maxDependancyInstallTime) {
        $this->maxDependancyInstallTime = $maxDependancyInstallTime;
        return $this;
    }

    public function getIssue() {
        return $this->issue;
    }

    public function setIssue($issue) {
        $this->issue = $issue;
        return $this;
    }

    public function getChangelog() {
        if ($this->changelog == '') {
            return $this->getInfo('changelog');
        }
        return $this->changelog;
    }

    public function setChangelog($changelog) {
        $this->changelog = $changelog;
        return $this;
    }

    public function getDocumentation() {
        if ($this->documentation == '') {
            return $this->getInfo('doc');
        }
        return $this->documentation;
    }

    public function setDocumentation($documentation) {
        $this->documentation = $documentation;
        return $this;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param mixed $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
    }

    /**
     * @param mixed $installation
     */
    public function setInstallation($installation)
    {
        $this->installation = $installation;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @param mixed $require
     */
    public function setRequire($require)
    {
        $this->require = $require;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @param mixed $filepath
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @param mixed $hasOwnDeamon
     */
    public function setHasOwnDeamon($hasOwnDeamon)
    {
        $this->hasOwnDeamon = $hasOwnDeamon;
    }

    /**
     * @param array $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * @param array $include
     */
    public function setInclude($include)
    {
        $this->include = $include;
    }

    /**
     * @param array $functionality
     */
    public function setFunctionality($functionality)
    {
        $this->functionality = $functionality;
    }

}
