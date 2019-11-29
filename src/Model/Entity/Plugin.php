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

use NextDom\Enums\DateFormat;
use NextDom\Enums\LogTarget;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\ReportHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Managers\CacheManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\ListenerManager;
use NextDom\Managers\MessageManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;

/**
 * Plugin
 *
 * @ORM\Table(name="plugin")
 * @ORM\Entity
 */
class Plugin implements EntityInterface
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
    protected $info = [];
    protected $include = [];
    // @TODO : Pas sur que ça serve
    protected $functionality = [];

    /**
     * @param $data
     * @throws \Exception
     */
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
            $this->changelog = str_replace('#language#', ConfigManager::byKey('language', 'core', 'fr_FR'), $data['changelog']);
        }
        if (isset($data['documentation'])) {
            $this->documentation = str_replace('#language#', ConfigManager::byKey('language', 'core', 'fr_FR'), $data['documentation']);
        }
        $this->mobile = '';
        if (file_exists(NEXTDOM_ROOT . '/plugins/' . $data['id'] . '/mobile/html')) {
            $this->mobile = (isset($data['mobile'])) ? $data['mobile'] : $data['id'];
        }
        if (isset($data['include'])) {
            $this->include = [
                'file' => $data['include']['file'],
                'type' => $data['include']['type'],
            ];
        } else {
            $this->include = [
                'file' => $data['id'],
                'type' => 'class',
            ];
        }
        $this->functionality['interact'] = ['exists' => method_exists($this->getId(), 'interact'), 'controlable' => 1];
        $this->functionality['cron'] = ['exists' => method_exists($this->getId(), 'cron'), 'controlable' => 1];
        $this->functionality['cron5'] = ['exists' => method_exists($this->getId(), 'cron5'), 'controlable' => 1];
        $this->functionality['cron15'] = ['exists' => method_exists($this->getId(), 'cron15'), 'controlable' => 1];
        $this->functionality['cron30'] = ['exists' => method_exists($this->getId(), 'cron30'), 'controlable' => 1];
        $this->functionality['cronHourly'] = ['exists' => method_exists($this->getId(), 'cronHourly'), 'controlable' => 1];
        $this->functionality['cronDaily'] = ['exists' => method_exists($this->getId(), 'cronDaily'), 'controlable' => 1];
        $this->functionality['deadcmd'] = ['exists' => method_exists($this->getId(), 'deadCmd'), 'controlable' => 0];
        $this->functionality['health'] = ['exists' => method_exists($this->getId(), 'health'), 'controlable' => 0];
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
     * @param string|int $id Identifiant du plugin
     * @return $this
     */
    public function setId($id): Plugin
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Obtenir le fichier de configuration du plugin
     *
     * @return string Chemin du fichier de configuration du plugin
     */
    public function getPathToConfigurationById(): string
    {
        @trigger_error('This method is deprecated. Use getPathToConfiguration', E_USER_DEPRECATED);
        return $this->getPathToConfiguration();
    }

    public function getPathToConfiguration(): string
    {
        $result = '';
        if (file_exists(NEXTDOM_ROOT . '/plugins/' . $this->id . '/plugin_info/configuration.php')) {
            $result = 'plugins/' . $this->id . '/plugin_info/configuration.php';
        }
        return $result;
    }

    /**
     * Génère un rapport si le plugin le permet
     * @TODO: Fonction getDisplay
     *
     * @param string $outputFormat Format de sortie
     * @param array $parameters Paramètres du format de sortie
     *
     * @return string Chemin du fichier de sortie
     *
     * @throws \Exception
     */
    public function report($outputFormat = 'pdf', $parameters = [])
    {
        if ($this->getDisplay() == '') {
            throw new CoreException(__('core.cant-report'));
        }
        $url = NetworkHelper::getNetworkAccess('internal') . '/index.php?v=d&p=' . $this->getDisplay();
        $url .= '&m=' . $this->getId();
        $url .= '&report=1';
        return ReportHelper::generate($url, 'plugin', $this->getId(), $outputFormat, $parameters);
    }

    /**
     * @TODO: ???
     *
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * @TODO: ??
     * @param $display
     * @return $this
     */
    public function setDisplay($display): Plugin
    {
        $this->display = $display;
        return $this;
    }

    /**
     * Test si le plugin est actif
     * @TODO: Doit passer en static
     * @return int
     * @throws \Exception
     */
    public function isActive()
    {
        return PluginManager::isActive($this->id);
    }

    /**
     * Obtenir des informations sur les dépendances
     * @TODO: Renommer
     * @param bool $refresh
     * @return array
     * @throws \Exception
     */
    public function dependancy_info($refresh = false)
    {
        trigger_error('The method dependancy_info becoming getDependencyInfo', E_USER_DEPRECATED);
        return $this->getDependencyInfo($refresh);
    }

    /**
     * @param bool $refresh
     * @return array
     * @throws \Exception
     */
    public function getDependencyInfo($refresh = false)
    {
        $pluginId = $this->getId();
        if ($this->getHasDependency() != 1 || !method_exists($pluginId, 'dependancy_info')) {
            return ['state' => 'nok', 'log' => 'nok'];
        }
        $cache = CacheManager::byKey('dependancy' . $this->getID());
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
            if (ConfigManager::byKey('lastDependancyInstallTime', $pluginId) == '') {
                ConfigManager::save('lastDependancyInstallTime', date(DateFormat::FULL), $pluginId);
            }
            $result['duration'] = round((strtotime('now') - strtotime(ConfigManager::byKey('lastDependancyInstallTime', $pluginId))) / 60);
        } else {
            $result['duration'] = -1;
        }
        $result['last_launch'] = ConfigManager::byKey('lastDependancyInstallTime', $this->getId(), __('Inconnue'));
        if ($result['state'] == 'ok') {
            CacheManager::set('dependancy' . $this->getID(), $result);
        }
        return $result;
    }

    /**
     * Savoir si le plugin a des dépendances
     *
     * @return int @TODO mettre un bool
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
    public function setHasDependency($hasDependency): Plugin
    {
        $this->hasDependency = $hasDependency;
        return $this;
    }

    /**
     * Procédure d'installation des dépendances
     * @TODO: Corriger la faute
     * @return null
     *
     * @throws \Exception
     */
    public function dependancy_install()
    {
        $plugin_id = $this->getId();
        if ($this->getHasDependency() != 1 || !method_exists($plugin_id, 'dependancy_install')) {
            return;
        }
        if (abs(strtotime(DateFormat::NOW) - strtotime(ConfigManager::byKey('lastDependancyInstallTime', $plugin_id))) <= 60) {
            $cache = CacheManager::byKey('dependancy' . $this->getID());
            $cache->remove();
            throw new CoreException(__('Vous devez attendre au moins 60 secondes entre deux lancements d\'installation de dépendances'));
        }
        $dependancy_info = $this->getDependencyInfo(true);
        if ($dependancy_info['state'] == 'in_progress') {
            throw new CoreException(__('Les dépendances sont déjà en cours d\'installation'));
        }
        foreach (PluginManager::listPlugin(true) as $plugin) {
            if ($plugin->getId() == $this->getId()) {
                continue;
            }
            $dependancy_info = $plugin->getDependencyInfo();
            if ($dependancy_info['state'] == 'in_progress') {
                throw new CoreException(__('Les dépendances d\'un autre plugin sont déjà en cours, veuillez attendre qu\'elles soient finies : ') . $plugin->getId());
            }
        }
        $cmd = $plugin_id::dependancy_install();
        if (is_array($cmd) && count($cmd) == 2) {
            $script = str_replace('#stype#', SystemHelper::getCommand('type'), $cmd['script']);
            $script_array = explode(' ', $script);
            if (file_exists($script_array[0])) {
                if (NextDomHelper::isCapable('sudo')) {
                    $this->deamon_stop();
                    MessageManager::add($plugin_id, __('Attention : installation des dépendances lancée'));
                    ConfigManager::save('lastDependancyInstallTime', date(DateFormat::FULL), $plugin_id);
                    exec(SystemHelper::getCmdSudo() . '/bin/bash ' . $script . ' >> ' . $cmd['log'] . ' 2>&1 &');
                    sleep(1);
                } else {
                    LogHelper::addError($plugin_id, __('Veuillez exécuter le script : ') . '/bin/bash ' . $script);
                }
            } else {
                LogHelper::addError($plugin_id, __('Aucun script ne correspond à votre type de Linux : ') . $cmd['script'] . __(' avec #stype# : ') . SystemHelper::getCommand('type'));
            }
        }
        $cache = CacheManager::byKey('dependancy' . $this->getID());
        $cache->remove();
        return;
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
        } catch (\Throwable $e) {
            LogHelper::addError($plugin_id, __('Erreur sur la fonction deamon_stop du plugin : ') . $e->getMessage());
        }
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
     * Obtenir les informations sur le daemon
     *
     * @return array
     * @throws \Exception
     */
    public function deamon_info()
    {

        $plugin_id = $this->getId();
        if ($this->getHasOwnDeamon() != 1 || !method_exists($plugin_id, 'deamon_info')) {
            return ['launchable_message' => '', 'launchable' => 'nok', 'state' => 'nok', 'log' => 'nok', 'auto' => 0];
        }
        $result = $plugin_id::deamon_info();
        if ($this->getHasDependency() == 1 && method_exists($plugin_id, 'dependancy_info') && $result['launchable'] == 'ok') {
            $dependancy_info = $this->getDependencyInfo();
            if ($dependancy_info['state'] != 'ok') {
                $result['launchable'] = 'nok';
                if ($dependancy_info['state'] == 'in_progress') {
                    $result['launchable_message'] = __('Dépendances en cours d\'installation');
                } else {
                    $result['launchable_message'] = __('Dépendances non installées');
                }
            }
        }
        if (!isset($result['launchable_message'])) {
            $result['launchable_message'] = '';
        }
        if (!isset($result['log'])) {
            $result['log'] = '';
        }
        $result['auto'] = ConfigManager::byKey('deamonAutoMode', $this->getId(), 1);
        if ($result['auto'] == 0) {
            $result['launchable_message'] = __('Gestion automatique désactivée');
        }
        if (ConfigManager::byKey('enableCron', 'core', 1, true) == 0) {
            $result['launchable'] = 'nok';
            $result['launchable_message'] = __('Les crons et démons sont désactivés');
        }
        if (!NextDomHelper::isStarted()) {
            $result['launchable'] = 'nok';
            $result['launchable_message'] = __('NextDom n\'est pas encore démarré');
        }
        $result['last_launch'] = ConfigManager::byKey('lastDeamonLaunchTime', $this->getId(), __('Inconnue'));
        return $result;
    }

    /**
     * @TODO Surement le mode de chargement du daemon au démarrage. Sans doute
     *
     * @param $_mode
     * @throws \Exception
     */
    public function deamon_changeAutoMode($_mode)
    {
        ConfigManager::save('deamonAutoMode', $_mode, $this->getId());
        $pluginId = $this->getId();
        if (method_exists($pluginId, 'deamon_changeAutoMode')) {
            $pluginId::deamon_changeAutoMode($_mode);
        }
    }

    /**
     * Démarre le daemon du plugin
     *
     * @param bool $forceRestart
     * @param bool $auto
     * @throws \Exception
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
                if ($deamon_info['state'] == 'ok' && ConfigManager::byKey('deamonRestartNumber', $pluginId, 0) != 0) {
                    ConfigManager::save('deamonRestartNumber', 0, $pluginId);
                }
                if ($auto && $deamon_info['auto'] == 0) {
                    return;
                }
                if ($deamon_info['launchable'] == 'ok' && $deamon_info['state'] == 'nok' && method_exists($pluginId, 'deamon_start')) {
                    $inprogress = CacheManager::byKey('deamonStart' . $this->getId() . 'inprogress');
                    $info = $inprogress->getValue(['datetime' => strtotime('now') - 60]);
                    $info['datetime'] = (isset($info['datetime'])) ? $info['datetime'] : strtotime('now') - 60;
                    if (abs(strtotime(DateFormat::NOW) - $info['datetime']) < 45) {
                        if ($auto) {
                            return;
                        }
                        throw new CoreException(__('Vous devez attendre au moins 45 secondes entre deux lancements du démon. Dernier lancement : ' . date("Y-m-d H:i:s", $info['datetime'])));
                    }
                    if (ConfigManager::byKey('deamonRestartNumber', $pluginId, 0) > 3) {
                        LogHelper::addError($pluginId, __('Attention je pense qu\'il y a un soucis avec le démon que j\'ai relancé plus de 3 fois consecutivement'));
                    }
                    if (!$forceRestart) {
                        ConfigManager::save('deamonRestartNumber', ConfigManager::byKey('deamonRestartNumber', $pluginId, 0) + 1, $pluginId);
                    }
                    CacheManager::set('deamonStart' . $this->getId() . 'inprogress', ['datetime' => strtotime('now')]);
                    ConfigManager::save('lastDeamonLaunchTime', date(DateFormat::FULL), $pluginId);
                    $pluginId::deamon_start();
                }
            }
        } catch (\Throwable $e) {
            LogHelper::addError($pluginId, __('Erreur sur la fonction deamon_start du plugin : ') . $e->getMessage());
        }
    }

    /**
     * Change l'état d'activation d'un plugin
     *
     * @param int $state Etat d'activation du plugin
     *
     * @return bool
     *
     * @throws \Throwable
     */
    public function setIsEnable($state)
    {
        if (version_compare(NextDomHelper::getJeedomVersion(), $this->getRequire()) == -1 && $state == 1) {
            throw new CoreException(__('Votre version de NextDom n\'est pas assez récente pour activer ce plugin'));
        }
        $alreadyActive = ConfigManager::byKey('active', $this->getId(), 0);
        if ($state == 1) {
            ConfigManager::save('active', $state, $this->getId());
        }
        $deamonAutoState = ConfigManager::byKey('deamonAutoMode', $this->getId(), 1);
        ConfigManager::save('deamonAutoMode', 0, $this->getId());
        if ($state == 0) {
            $eqLogics = EqLogicManager::byType($this->getId());
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
            $listeners = ListenerManager::byClass($this->getId());
            if (is_array($listeners)) {
                foreach ($listeners as $listener) {
                    $listener->remove();
                }
            }
        } else if ($alreadyActive == 0 && $state == 1) {
            foreach (EqLogicManager::byType($this->getId()) as $eqLogic) {
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
                LogHelper::addInfo($this->getId(), 'Début d\'activation du plugin');
                $this->deamon_stop();

                $deamon_info = $this->deamon_info();
                sleep(1);
                LogHelper::addInfo($this->getId(), 'Info sur le démon : ' . print_r($deamon_info, true));
                if ($deamon_info['state'] == 'ok') {
                    $this->deamon_stop();
                }
                if ($alreadyActive == 1) {
                    $out = $this->callInstallFunction('update');
                } else {
                    $out = $this->callInstallFunction('install');
                }
                $this->getDependencyInfo(true);
            } else {
                $this->deamon_stop();
                if ($alreadyActive == 1) {
                    $out = $this->callInstallFunction('remove');
                }
                rrmdir(NextDomHelper::getTmpFolder($this->getId()));
            }
            if (isset($out) && trim($out) != '') {
                LogHelper::addInfo($this->getId(), "Installation/remove/update result : " . $out);
            }
        } catch (\Throwable $e) {
            ConfigManager::save('active', $alreadyActive, $this->getId());
            LogHelper::addError('plugin', $e->getMessage());
            throw $e;
        }

        if ($state == 0) {
            ConfigManager::save('active', $state, $this->getId());
        }
        if ($deamonAutoState) {
            ConfigManager::save('deamonAutoMode', 1, $this->getId());
        }
        if ($alreadyActive == 0 && $state == 1) {
            ConfigManager::save('log::level::' . $this->getId(), '{"100":"0","200":"0","300":"0","400":"0","1000":"0","default":"1"}');
        }
        return true;
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
     * Appelle les fonctions liées aux actions d'installation/préinstallation/mise à jour et suppression d'un plugin
     * La fonction de préinstallation nommé @TODO:TROUVER SON NOM doit se trouver dans un fichier nommé "pre_install.php" situé dans le répertoire plugin_info du plugin
     * Les autres fonctions doivent se trouver dans le fichier install.php située dans le répertoire plugin_info du plugin
     *
     * @param $functionToCall
     * @param bool $direct Lance la fonction passée en paramètre directement
     *
     * @TODO: Amélioration possible, tester si le fichier existe, l'inclure, puis tester si la méthode existe plutot que de lire le contenu du fichier
     *
     * @return bool|string
     *
     * @throws \Exception
     */
    public function callInstallFunction($functionToCall, $direct = false)
    {
        if ($direct) {
            // Lancement de la procédure de préinstallation
            $targetFile = 'install.php';
            if (strpos($functionToCall, 'pre_') !== false) {
                $targetFile = 'pre_install.php';
            }
            LogHelper::addDebug(LogTarget::PLUGIN, 'Recherche de ' . NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/plugin_info/' . $targetFile);
            if (file_exists(NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/plugin_info/' . $targetFile)) {
                LogHelper::addDebug(LogTarget::PLUGIN, 'Fichier d\'installation trouvé pour  : ' . $this->getId());
                /** @noinspection PhpIncludeInspection */
                require_once NEXTDOM_ROOT . '/plugins/' . $this->getId() . '/plugin_info/' . $targetFile;
                ob_start();
                $function = $this->getId() . '_' . $functionToCall;
                if (function_exists($this->getId() . '_' . $functionToCall)) {
                    $function();
                }
                return ob_get_clean();
            }
        } else {
            return $this->launch($functionToCall, true);
        }
        return null;
    }

    /**
     * @TODO: Lance un truc, peut être un Nokia 3310
     *
     * @param $functionToCall
     * @param bool $callInstallFunction
     *
     * @return bool|string
     *
     * @throws \Exception
     */
    public function launch($functionToCall, $callInstallFunction = false)
    {
        if ($functionToCall == '') {
            throw new CoreException('La fonction à lancer ne peut être vide');
        }
        if (!$callInstallFunction && (!class_exists($this->getId()) || !method_exists($this->getId(), $functionToCall))) {
            throw new CoreException('Il n\'existe aucune méthode : ' . $this->getId() . '::' . $functionToCall . '()');
        }
        $cmd = NEXTDOM_ROOT . '/src/Api/start_plugin_func.php ';
        $cmd .= ' plugin_id=' . $this->getId();
        $cmd .= ' function=' . $functionToCall;
        $cmd .= ' callInstallFunction=' . $callInstallFunction;
        if (NextDomHelper::checkOngoingThread($cmd) > 0) {
            return true;
        }
        LogHelper::addDebug($this->getId(), __('Lancement de : ') . $cmd);
        if ($callInstallFunction) {
            return SystemHelper::php($cmd . ' >> /dev/null 2>&1');
        } else {
            SystemHelper::php($cmd . ' >> /dev/null 2>&1 &');
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
     * @TODO: Changer le format
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
     * Get plugin update data
     *
     * @return array|mixed|null
     * @throws \Exception
     */
    public function getUpdate()
    {
        return UpdateManager::byTypeAndLogicalId('plugin', $this->getId());
    }

    /**
     * @return string
     */
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
        return '/public/img/NextDom_Plugin_Gray.png';
    }

    /**
     * Obtenir la liste des logs
     *
     * @return array
     */
    public function getLogList(): array
    {
        $result = [];
        foreach (FileSystemHelper::ls(LogHelper::getPathToLog(''), '*') as $log) {
            if ($log == $this->getId()) {
                $result[] = $log;
            } elseif (strpos($log, $this->getId()) === 0) {
                $result[] = $log;
            }
        }
        return $result;
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
     * Définir le nom
     *
     * @param string $name
     * @return Plugin
     */
    public function setName(string $name): Plugin
    {
        $this->name = $name;
        return $this;
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
     * Obtenir l'auteur
     *
     * @return string Auteur
     */
    public function getAuthor(): string
    {
        return $this->author;
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
     * @param $key
     * @return $this
     */
    public function setCategory($key)
    {
        $this->category = $key;
        return $this;
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
     * @TODO: ???
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @TODO: ???
     * @return array
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * @TODO: ??
     *
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @TODO: ??
     * @param $mobile
     * @return $this
     */
    public function setMobile($mobile): Plugin
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @TODO ??
     * @return int
     */
    public function getEventjs()
    {
        return $this->eventjs;
    }

    /**
     * @TODO ??
     * @param $eventjs
     * @return $this
     */
    public function setEventjs($eventjs): Plugin
    {
        $this->eventjs = $eventjs;
        return $this;
    }

    /**
     * Définir si le plugin a son propre daemon
     *
     * @param $hasOwnDeamon
     *
     * @return $this
     */
    public function setHasOwnDeamony($hasOwnDeamon): Plugin
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
     * @param int|string $maxDependancyInstallTime Temps maximum pour installer les dépendances.
     *
     * @return $this
     */
    public function setMaxDependancyInstallTime($maxDependancyInstallTime): Plugin
    {
        $this->maxDependancyInstallTime = $maxDependancyInstallTime;
        return $this;
    }

    /**
     * @TODO ????
     * @return string
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * Définir une issue @TODO ???
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
     * @throws \Exception
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
     * @param string $changelog Adresse des changelogs
     *
     * @return $this
     */
    public function setChangelog($changelog): Plugin
    {
        $this->changelog = $changelog;
        return $this;
    }

    /**
     * Obtenir les informations
     *
     * @param string $name
     * @param string $default
     * @return array|mixed|string
     * @throws \Exception
     */
    public function getInfo($name = '', $default = '')
    {
        if (count($this->info) == 0) {
            $update = UpdateManager::byLogicalId($this->id);
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
     * Obtenir l'adresse de la documentation
     *
     * @return string Adresse de la documentation
     * @throws \Exception
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
     * @param string $documentation Adresse de la documentation
     *
     * @return $this
     */
    public function setDocumentation($documentation): Plugin
    {
        $this->documentation = $documentation;
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
        return 'plugin';
    }
}
