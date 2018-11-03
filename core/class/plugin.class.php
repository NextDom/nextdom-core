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

use NextDom\Managers\PluginManager;

class plugin
{
    protected $id;
    protected $name = '';
    protected $description = '';
    protected $license = '';
    protected $installation = '';
    protected $author = '';
    protected $require = '';
    protected $category = '';
    protected $filepath;
    protected $index;
    protected $display = '';
    protected $mobile;
    protected $eventjs = 0;
    protected $hasDependency = 0;
    protected $maxDependancyInstallTime = 30;
    protected $hasOwnDeamon = 0;
    protected $issue = '';
    protected $changelog = '';
    protected $documentation = '';
    protected $info = array();
    protected $include = array();
    protected $functionality = array();

    public static function byId($id)
    {
        return PluginManager::byId($id);
    }

    public static function getPathById($id)
    {
        return PluginManager::getPathById($id);
    }

    public static function listPlugin($activateOnly = false, $orderByCaterogy = false, $translate = true, $nameOnly = false)
    {
        return PluginManager::listPlugin($activateOnly, $orderByCaterogy, $nameOnly);
    }

    public static function orderPlugin($a, $b)
    {
        return PluginManager::orderPlugin($a, $b);
    }

    public static function heartbeat()
    {
        PluginManager::heartbeat();
    }
    
    public static function cron()
    {
        PluginManager::cron();
    }

    public static function cron5()
    {
        PluginManager::cron5();
    }

    public static function cron15()
    {
        PluginManager::cron15();
    }

    public static function cron30()
    {
        PluginManager::cron30();
    }

    public static function cronDaily()
    {
        PluginManager::cronDaily();
    }

    public static function cronHourly()
    {
        PluginManager::cronHourly();
    }

    public static function start()
    {
        PluginManager::start();
    }

    public static function stop()
    {
        PluginManager::stop();
    }

    public static function checkDeamon()
    {
        PluginManager::checkDeamon();
    }

    public function initPluginFromData($data)
    {
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
        } else {
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
        if (file_exists(NEXTDOM_ROOT . '/plugins/' . $data['id'] . '/mobile/html')) {
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
        $this->functionality['interact'] = array('exists' => method_exists($this->getId(), 'interact'), 'controlable' => 1);
        $this->functionality['cron'] = array('exists' => method_exists($this->getId(), 'cron'), 'controlable' => 1);
        $this->functionality['cron5'] = array('exists' => method_exists($this->getId(), 'cron5'), 'controlable' => 1);
        $this->functionality['cron15'] = array('exists' => method_exists($this->getId(), 'cron15'), 'controlable' => 1);
        $this->functionality['cron30'] = array('exists' => method_exists($this->getId(), 'cron30'), 'controlable' => 1);
        $this->functionality['cronHourly'] = array('exists' => method_exists($this->getId(), 'cronHourly'), 'controlable' => 1);
        $this->functionality['cronDaily'] = array('exists' => method_exists($this->getId(), 'cronDaily'), 'controlable' => 1);
        $this->functionality['deadcmd'] = array('exists' => method_exists($this->getId(), 'deadCmd'), 'controlable' => 0);
        $this->functionality['health'] = array('exists' => method_exists($this->getId(), 'health'), 'controlable' => 0);
    }

    /**
     * Obtenir le fichier de configuration du plugin
     *
     * @return string Chemin du fichier de configuration du plugin
     */
    public function getPathToConfigurationById(): string
    {
        $result = '';
        if (file_exists(NEXTDOM_ROOT . '/plugins/' . $this->id . '/plugin_info/configuration.php')) {
            $result = 'plugins/' . $this->id . '/plugin_info/configuration.php';
        }
        return $result;
    }

    /**
     * Génère un rapport si le plugin le permet
     * TODO: Fonction getDisplay
     *
     * @param string $outputFormat Format de sortie
     * @param array $parameters Paramètres du format de sortie
     *
     * @return string Chemin du fichier de sortie
     *
     * @throws Exception
     */
    public function report($outputFormat = 'pdf', $parameters = array())
    {
        if ($this->getDisplay() == '') {
            throw new \Exception(__('core.cant-report'));
        }
        $url = \network::getNetworkAccess('internal') . '/index.php?v=d&p=' . $this->getDisplay();
        $url .= '&m=' . $this->getId();
        $url .= '&report=1';
        if (isset($_parameters['arg']) && trim($_parameters['arg']) != '') {
		       $url .= '&' . $_parameters['arg'];
		    }
        return \report::generate($url, 'plugin', $this->getId(), $outputFormat, $parameters);
    }

    /**
     * Test si le plugin est actif
     * TODO: Doit passer en static
     * @return int
     */
    public function isActive()
    {
        return PluginManager::isActive($this->id);
    }

    /**
     * Appelle les fonctions liées aux actions d'installation/préinstallation/mise à jour et suppression d'un plugin
     * La fonction de préinstallation nommé TODO:TROUVER SON NOM doit se trouver dans un fichier nommé "pre_install.php" situé dans le répertoire plugin_info du plugin
     * Les autres fonctions doivent se trouver dans le fichier install.php située dans le répertoire plugin_info du plugin
     *
     * @param $functionToCall
     * @param bool $direct Lance la fonction passée en paramètre directement
     *
     * TODO: Amélioration possible, tester si le fichier existe, l'inclure, puis tester si la méthode existe plutot que de lire le contenu du fichier
     *
     * @return bool|string
     *
     * @throws Exception
     */
    public function callInstallFunction($functionToCall, $direct = false)
    {
        if ($direct) {
            // Lancement de la procédure de préinstallation
            if (strpos($functionToCall, 'pre_') !== false) {
                \log::add('plugin', 'debug', 'Recherche de ' . NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/plugin_info/pre_install.php');
                if (file_exists(NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/plugin_info/pre_install.php')) {
                    \log::add('plugin', 'debug', 'Fichier d\'installation trouvé pour  : ' . $this->getId());
                    require_once NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/plugin_info/pre_install.php';
                    ob_start();
                    $function = $this->getId() . '_' . $functionToCall;
                    if (function_exists($this->getId() . '_' . $functionToCall)) {
                        $function();
                    }
                    return ob_get_clean();
                }
            } else {
                \log::add('plugin', 'debug', 'Recherche de ' . NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/plugin_info/install.php');
                if (file_exists(NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/plugin_info/install.php')) {
                    \log::add('plugin', 'debug', 'Fichier d\'installation trouvé pour  : ' . $this->getId());
                    require_once NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/plugin_info/install.php';
                    ob_start();
                    $function = $this->getId() . '_' . $functionToCall;
                    if (function_exists($this->getId() . '_' . $functionToCall)) {
                        $function();
                    }
                    return ob_get_clean();
                }
            }
        } else {
            return $this->launch($functionToCall, true);
        }
    }

    /**
     * Obtenir des informations sur les dépendances
     * TODO: Renommer
     * @param bool $refresh
     * @return array
     */
    public function dependancy_info($refresh = false)
    {
        $pluginId = $this->getId();
        if ($this->getHasDependency() != 1 || !method_exists($pluginId, 'dependancy_info')) {
            return array('state' => 'nok', 'log' => 'nok');
        }
        $cache = \cache::byKey('dependancy' . $this->getID());
        if ($refresh) {
            $cache->remove();
        } else {
            $result = $cache->getValue();
            if (is_array($result) && $result['state'] == 'ok') {
                return $cache->getValue();
            }
        }
        $result = $pluginId::dependancy_info();
        if (!isset($result['log'])) {
            $result['log'] = '';
        }
        if (isset($result['progress_file'])) {
            $result['progression'] = 0;
            if (@file_exists($result['progress_file'])) {
                $result['state'] = 'in_progress';
                $progression = trim(file_get_contents($result['progress_file']));
                if ($progression != '') {
                    $result['progression'] = $progression;
                }
            }
        }
        if ($result['state'] == 'in_progress') {
            if (config::byKey('lastDependancyInstallTime', $pluginId) == '') {
                config::save('lastDependancyInstallTime', date('Y-m-d H:i:s'), $pluginId);
            }
            $result['duration'] = round((strtotime('now') - strtotime(config::byKey('lastDependancyInstallTime', $pluginId))) / 60);
        } else {
            $result['duration'] = -1;
        }
        $result['last_launch'] = config::byKey('lastDependancyInstallTime', $this->getId(), __('Inconnue', __FILE__));
        if ($result['state'] == 'ok') {
            \cache::set('dependancy' . $this->getID(), $result);
        }
        return $result;
    }

    /**
     * Proécude d'installation des dépendances
     * TODO: Corriger la faute
     * @return null
     *
     * @throws Exception
     */
    public function dependancy_install()
    {
        $plugin_id = $this->getId();
        if ($this->getHasDependency() != 1 || !method_exists($plugin_id, 'dependancy_install')) {
            return;
        }
        if ((strtotime('now') - 60) <= strtotime(config::byKey('lastDependancyInstallTime', $plugin_id))) {
            $cache = \cache::byKey('dependancy' . $this->getID());
            $cache->remove();
            throw new \Exception(__('Vous devez attendre au moins 60 secondes entre deux lancements d\'installation de dépendances', __FILE__));
        }
        $dependancy_info = $this->dependancy_info(true);
        if ($dependancy_info['state'] == 'in_progress') {
            throw new \Exception(__('Les dépendances sont déjà en cours d\'installation', __FILE__));
        }
        foreach (self::listPlugin(true) as $plugin) {
            if ($plugin->getId() == $this->getId()) {
                continue;
            }
            $dependancy_info = $plugin->dependancy_info();
            if ($dependancy_info['state'] == 'in_progress') {
                throw new \Exception(__('Les dépendances d\'un autre plugin sont déjà en cours, veuillez attendre qu\'elles soient finies : ', __FILE__) . $plugin->getId());
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
                    \log::add($plugin_id, 'error', __('Veuillez exécuter le script : ', __FILE__) . '/bin/bash ' . $script);
                }
            } else {
                \log::add($plugin_id, 'error', __('Aucun script ne correspond à votre type de Linux : ', __FILE__) . $cmd['script'] . __(' avec #stype# : ', __FILE__) . system::get('type'));
            }
        }
        $cache = \cache::byKey('dependancy' . $this->getID());
        $cache->remove();
        return;
    }

    /**
     * TODO Surement le mode de chargement du daemon au démarrage. Sans doute
     *
     * @param $_mode
     */
    public function deamon_changeAutoMode($_mode)
    {
        config::save('deamonAutoMode', $_mode, $this->getId());
        $pluginId = $this->getId();
        if (method_exists($pluginId, 'deamon_changeAutoMode')) {
            $pluginId::deamon_changeAutoMode($_mode);
        }
    }

    /**
     * Obtenir les informations sur le daemon
     *
     * @return array
     */
    public function deamon_info()
    {

        $plugin_id = $this->getId();
        if ($this->getHasOwnDeamon() != 1 || !method_exists($plugin_id, 'deamon_info')) {
            return array('launchable_message' => '', 'launchable' => 'nok', 'state' => 'nok', 'log' => 'nok', 'auto' => 0);
        }
        $result = $plugin_id::deamon_info();
        if ($this->getHasDependency() == 1 && method_exists($plugin_id, 'dependancy_info') && $result['launchable'] == 'ok') {
            $dependancy_info = $this->dependancy_info();
            if ($dependancy_info['state'] != 'ok') {
                $result['launchable'] = 'nok';
                if ($dependancy_info['state'] == 'in_progress') {
                    $result['launchable_message'] = __('Dépendances en cours d\'installation', __FILE__);
                } else {
                    $result['launchable_message'] = __('Dépendances non installées', __FILE__);
                }
            }
        }
        if (!isset($result['launchable_message'])) {
            $result['launchable_message'] = '';
        }
        if (!isset($result['log'])) {
            $result['log'] = '';
        }
        $result['auto'] = config::byKey('deamonAutoMode', $this->getId(), 1);
        if ($result['auto'] == 0) {
            $result['launchable_message'] = __('Gestion automatique désactivée', __FILE__);
        }
        if (config::byKey('enableCron', 'core', 1, true) == 0) {
            $result['launchable'] = 'nok';
            $result['launchable_message'] = __('Les crons et démons sont désactivés', __FILE__);
        }
        if (!nextdom::isStarted()) {
            $result['launchable'] = 'nok';
            $result['launchable_message'] = __('NextDom n\'est pas encore démarré', __FILE__);
        }
        $result['last_launch'] = config::byKey('lastDeamonLaunchTime', $this->getId(), __('Inconnue', __FILE__));
        return $result;
    }

    /**
     * Démarre le daemon du plugin
     *
     * @param bool $forceRestart
     * @param bool $auto
     */
    public function deamon_start($forceRestart = false, $auto = false)
    {
        $pluginId = $this->getId();
        if ($forceRestart) {
            $this->deamon_stop();
        }
        try {
            if ($this->getHasOwnDeamon() == 1 && method_exists($pluginId, 'deamon_info')) {
                $deamon_info = $this->deamon_info();
                if ($deamon_info['state'] == 'ok' && config::byKey('deamonRestartNumber', $plugin_id, 0) != 0) {
                    config::save('deamonRestartNumber', 0, $plugin_id);
                }
                if ($auto && $deamon_info['auto'] == 0) {
                    return;
                }
                if ($deamon_info['launchable'] == 'ok' && $deamon_info['state'] == 'nok' && method_exists($pluginId, 'deamon_start')) {
                    $inprogress = \cache::byKey('deamonStart' . $this->getId() . 'inprogress');
                    $info = $inprogress->getValue(array('datetime' => strtotime('now') - 60));
                    $info['datetime'] = (isset($info['datetime'])) ? $info['datetime'] : strtotime('now') - 60;
                    if (abs(strtotime('now') - $info['datetime']) < 45) {
                        throw new \Exception(__('Vous devez attendre au moins 45 secondes entre deux lancements du démon. Dernier lancement : ' . date("Y-m-d H:i:s", $info['datetime']), __FILE__));
                    }
                    if (config::byKey('deamonRestartNumber', $plugin_id, 0) > 3) {
                        log::add($plugin_id, 'error', __('Attention je pense qu\'il y a un soucis avec le démon que j\'ai relancé plus de 3 fois consecutivement', __FILE__));
                    }
                    if (!$_forceRestart) {
                        config::save('deamonRestartNumber', config::byKey('deamonRestartNumber', $plugin_id, 0) + 1, $plugin_id);
                    }
                    \cache::set('deamonStart' . $this->getId() . 'inprogress', array('datetime' => strtotime('now')));
                    \config::save('lastDeamonLaunchTime', date('Y-m-d H:i:s'), $pluginId);
                    $pluginId::deamon_start();
                }
            }
        } catch (Throwable $e) {
            \log::add($pluginId, 'error', __('Erreur sur la fonction deamon_start du plugin : ', __FILE__) . $e->getMessage());
        }
    }

    /**
     * Arrête le daemon du plugin
     */
    public function deamon_stop()
    {
        $plugin_id = $this->getId();
        try {
            if ($this->getHasOwnDeamon() == 1 && method_exists($plugin_id, 'deamon_info')) {
                $deamon_info = $this->deamon_info();
                if ($deamon_info['state'] == 'ok' && method_exists($plugin_id, 'deamon_stop')) {
                    $plugin_id::deamon_stop();
                }
            }
        } catch (Throwable $e) {
            \log::add($plugin_id, 'error', __('Erreur sur la fonction deamon_stop du plugin : ', __FILE__) . $e->getMessage());
        }
    }

    /**
     * Change l'état d'activation d'un plugin
     *
     * @param int $state Etat d'activation du plugin
     *
     * @return bool
     *
     * @throws Exception
     */
    public function setIsEnable($state)
    {
        if (version_compare(nextdom::version(), $this->getRequire()) == -1 && $state == 1) {
            throw new \Exception(__('Votre version de NextDom n\'est pas assez récente pour activer ce plugin', __FILE__));
        }
        $alreadyActive = config::byKey('active', $this->getId(), 0);
        if ($state == 1) {
            config::save('active', $state, $this->getId());
        }
        $deamonAutoState = config::byKey('deamonAutoMode', $this->getId(), 1);
        config::save('deamonAutoMode', 0, $this->getId());
        if ($state == 0) {
            $eqLogics = eqLogic::byType($this->getId());
            if (is_array($eqLogics)) {
                foreach ($eqLogics as $eqLogic) {
                    try {
                        $eqLogic->setConfiguration('previousIsEnable', $eqLogic->getIsEnable());
                        $eqLogic->setConfiguration('previousIsVisible', $eqLogic->getIsVisible());
                        $eqLogic->setIsEnable(0);
                        $eqLogic->setIsVisible(0);
                        $eqLogic->save();
                    } catch (\Throwable $e) {

                    }
                }
            }
            $listeners = listener::byClass($this->getId());
            if (is_array($listeners)) {
                foreach ($listeners as $listener) {
                    $listener->remove();
                }
            }
        } else if ($alreadyActive == 0 && $state == 1) {
            foreach (eqLogic::byType($this->getId()) as $eqLogic) {
                try {
                    $eqLogic->setIsEnable($eqLogic->getConfiguration('previousIsEnable', 1));
                    $eqLogic->setIsVisible($eqLogic->getConfiguration('previousIsVisible', 1));
                    $eqLogic->save();
                } catch (\Throwable $e) {

                }
            }
        }
        try {
            if ($state == 1) {
                \log::add($this->getId(), 'info', 'Début d\'activation du plugin');
                $this->deamon_stop();

                $deamon_info = $this->deamon_info();
                sleep(1);
                \log::add($this->getId(), 'info', 'Info sur le démon : ' . print_r($deamon_info, true));
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
                \log::add($this->getId(), 'info', "Installation/remove/update result : " . $out);
            }
        } catch (\Throwable $e) {
            config::save('active', $alreadyActive, $this->getId());
            \log::add('plugin', 'error', $e->getMessage());
            throw $e;
        }

        if ($state == 0) {
            config::save('active', $state, $this->getId());
        }
        if ($deamonAutoState) {
            config::save('deamonAutoMode', 1, $this->getId());
        }
        if ($alreadyActive == 0 && $state == 1) {
            config::save('log::level::' . $this->getId(), '{"100":"0","200":"0","300":"0","400":"0","1000":"0","default":"1"}');
        }
        return true;
    }

    /**
     * TODO: Lance un truc, peut être un Nokia 3310
     *
     * @param $functionToCall
     * @param bool $callInstallFunction
     *
     * @return bool|string
     *
     * @throws Exception
     */
    public function launch($functionToCall, $callInstallFunction = false)
    {
        if ($functionToCall == '') {
            throw new \Exception('La fonction à lancer ne peut être vide');
        }
        if (!$callInstallFunction && (!class_exists($this->getId()) || !method_exists($this->getId(), $functionToCall))) {
            throw new \Exception('Il n\'existe aucune méthode : ' . $this->getId() . '::' . $functionToCall . '()');
        }
        $cmd = NEXTDOM_ROOT . '/core/php/jeePlugin.php ';
        $cmd .= ' plugin_id=' . $this->getId();
        $cmd .= ' function=' . $functionToCall;
        $cmd .= ' callInstallFunction=' . $callInstallFunction;
        if (nextdom::checkOngoingThread($cmd) > 0) {
            return true;
        }
        \log::add($this->getId(), 'debug', __('Lancement de : ', __FILE__) . $cmd);
        if ($callInstallFunction) {
            return system::php($cmd . ' >> /dev/null 2>&1');
        } else {
            system::php($cmd . ' >> /dev/null 2>&1 &');
        }
        return true;
    }

    /**
     * Obtenir la traduction d'un plugin
     *
     * @param string $language Langue demandée
     *
     * @return array
     */
    public function getTranslation(string $language): array
    {
        $dir = NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/core/i18n';
        if (!file_exists($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!file_exists($dir)) {
            return [];
        }
        if (file_exists($dir . '/' . $language . '.json')) {
            $result = file_get_contents($dir . '/' . $language . '.json');

            if (is_json($result)) {
                return json_decode($result, true);
            }
        }
        return [];
    }

    /**
     * Sauvegarde un traduction
     * TODO: Changer le format
     * @param $_language
     * @param $_translation
     */
    public function saveTranslation($_language, $_translation)
    {
        $dir = NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/core/i18n';
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($dir . '/' . $_language . '.json', json_encode($_translation, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtenir une mise à jour
     * TODO: C'est à dire
     * @return array|mixed|null
     */
    public function getUpdate()
    {
        return update::byTypeAndLogicalId('plugin', $this->getId());
    }

    public function getPathImgIcon()
    {
        if (file_exists(NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/plugin_info/' . $this->getId() . '_icon.png')) {
            return 'plugins/' . $this->getId() . '/plugin_info/' . $this->getId() . '_icon.png';
        }
        if (file_exists(NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/doc/images/' . $this->getId() . '_icon.png')) {
            return 'plugins/' . $this->getId() . '/doc/images/' . $this->getId() . '_icon.png';
        }
        if (file_exists(NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/plugin_info/' . strtolower($this->getId()) . '_icon.png')) {
            return 'plugins/' . $this->getId() . '/plugin_info/' . strtolower($this->getId()) . '_icon.png';
        }
        if (file_exists(NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/doc/images/' . strtolower($this->getId()) . '_icon.png')) {
            return 'plugins/' . $this->getId() . '/doc/images/' . strtolower($this->getId()) . '_icon.png';
        }
        return '/public/img/NextDom_Plugin.png';
    }

    /**
     * Obtenir la liste des logs
     *
     * @return array
     */
    public function getLogList(): array
    {
        $result = array();
        foreach (\ls(\log::getPathToLog(''), '*') as $log) {
            if ($log == $this->getId()) {
                $result[] = $log;
            } elseif (strpos($log, $this->getId()) === 0) {
                $result[] = $log;
            }
        }
        return $result;
    }

    /**
     * Obtenir l'identifiant
     *
     * @return mixed Identifiant du plugin
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Définir l'identifiant
     * @param $id Identifiant du plugin
     * @return $this
     */
    public function setId($id): plugin
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Définir le nom
     *
     * @param string $id Nom du plugin
     */
    public function setName(string $name): plugin
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Obtenir le nom
     *
     * @return string Nom
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Obtenir la description
     *
     * @return string Descsription
     */
    public function getDescription()
    {
        return nl2br($this->description);
    }

    /**
     * Obtenir les informations
     *
     * @param string $name
     * @param string $default
     * @return array|mixed|string
     */
    public function getInfo($name = '', $default = '')
    {
        if (count($this->info) == 0) {
            $update = update::byLogicalId($this->id);
            if (is_object($update)) {
                $this->info = $update->getInfo();
            }
        }
        if ($name !== '') {
            if (isset($this->info[$name])) {
                return $this->info[$name];
            }
            return $default;
        }
        return $this->info;
    }

    /**
     * Obtenir l'auteur
     *
     * @return string Auteur
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Obtenir la version de NextDom requise
     *
     * @return string Version de NextDom requise
     */
    public function getRequire(): string
    {
        return $this->require;
    }

    /**
     * Obtenir la catégorie
     *
     * @return string Catégorie
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Obtenir la licence
     *
     * @return string Licence
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Obtenir le chemin complet
     *
     * @return string Chemin complet
     */
    public function getFilepath(): string
    {
        return $this->filepath;
    }

    /**
     *
     * @return string
     */
    public function getInstallation(): string
    {
        return nl2br($this->installation);
    }

    /**
     * TODO: ???
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * TODO: ???
     * @return array
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * TODO: ???
     *
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * TODO: ??
     * @param $display
     * @return $this
     */
    public function setDisplay($display): plugin
    {
        $this->display = $display;
        return $this;
    }

    /**
     * TODO: ??
     *
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * TODO: ??
     * @param $mobile
     * @return $this
     */
    public function setMobile($mobile): plugin
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * TODO ??
     * @return int
     */
    public function getEventjs()
    {
        return $this->eventjs;
    }

    /**
     * TODO ??
     * @param $eventjs
     * @return $this
     */
    public function setEventjs($eventjs): plugin
    {
        $this->eventjs = $eventjs;
        return $this;
    }

    /**
     * Savoir si le plugin a des dépendances
     *
     * @return int TODO mettre un bool
     */
    public function getHasDependency()
    {
        return $this->hasDependency;
    }

    /**
     * Définir si le plugin a des dépendances
     *
     * @param $hasDependency
     *
     * @return $this
     */
    public function setHasDependency($hasDependency): plugin
    {
        $this->hasDependency = $hasDependency;
        return $this;
    }

    /**
     * Savoir si le plugin a son propre daemon
     *
     * @return int
     */
    public function getHasOwnDeamon()
    {
        return $this->hasOwnDeamon;
    }

    /**
     * Définir si le plugin a son propre daemon
     *
     * @param $hasOwnDeamon
     *
     * @return $this
     */
    public function setHasOwnDeamony($hasOwnDeamon): plugin
    {
        $this->hasOwnDeamon = $hasOwnDeamon;
        return $this;
    }

    /**
     * Obtenir la limite de temps pour installer les dépendances
     *
     * @return int Temps maximum pour installer les dépendances
     */
    public function getMaxDependancyInstallTime()
    {
        return $this->maxDependancyInstallTime;
    }

    /**
     * Définir la limite de temps pour installer les dépendances
     *
     * @param $maxDependancyInstallTime Temps maximum pour installer les dépendances.
     *
     * @return $this
     */
    public function setMaxDependancyInstallTime($maxDependancyInstallTime): plugin
    {
        $this->maxDependancyInstallTime = $maxDependancyInstallTime;
        return $this;
    }

    /**
     * TODO ????
     * @return string
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * Définir une issue TODO ???
     * @param $issue
     * @return $this
     */
    public function setIssue($issue)
    {
        $this->issue = $issue;
        return $this;
    }

    /**
     * Obtenir l'adresse des changelogs
     *
     * @return string Adresse des changelogs
     */
    public function getChangelog(): string
    {
        if ($this->changelog == '') {
            return $this->getInfo('changelog');
        }
        return $this->changelog;
    }

    /**
     * Définir l'adresse des changelogs
     *
     * @param $changelog Adresse des changelogs
     *
     * @return $this
     */
    public function setChangelog($changelog): plugin
    {
        $this->changelog = $changelog;
        return $this;
    }

    /**
     * Obtenir l'adresse de la documentation
     *
     * @return string Adresse de la documentation
     */
    public function getDocumentation(): string
    {
        if ($this->documentation == '') {
            return $this->getInfo('doc');
        }
        return $this->documentation;
    }

    /**
     * Définir l'adresse de la documentation
     *
     * @param $documentation Adresse de la documentation
     *
     * @return $this
     */
    public function setDocumentation($documentation): plugin
    {
        $this->documentation = $documentation;
        return $this;
    }

    public function setCategory($key)
    {
        $this->category = $key;
        return $this;
    }
}
