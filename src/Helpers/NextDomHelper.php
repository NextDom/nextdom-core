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

namespace NextDom\Helpers;

use NextDom\Enums\ApiModeEnum;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\EventManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\CacheManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\UpdateManager;

class NextDomHelper
{
    /**
     *
     */
    private static $nextdomConfiguration;

    /**
     * TODO ???
     * @param $data
     */
    public static function addRemoveHistory($data)
    {
        $removeHistory = array();
        if (file_exists(NEXTDOM_ROOT . '/data/remove_history.json')) {
            $removeHistory = json_decode(file_get_contents(NEXTDOM_ROOT . '/data/remove_history.json'), true);
        }
        $removeHistory[] = $data;
        $removeHistory = array_slice($removeHistory, -200, 200);
        file_put_contents(NEXTDOM_ROOT . '/data/remove_history.json', json_encode($removeHistory));
    }

    /**
     * Get all dead commands
     *
     * @return array
     */
    public static function getDeadCmd()
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        $result = array();
        $cmd = \config::byKey('interact::warnme::defaultreturncmd', 'core', '');
        if ($cmd != '') {
            if (!CmdManager::byId(str_replace('#', '', $cmd))) {
                $result[] = array('detail' => 'Administration', 'help' => __('Commande retour interactions'), 'who' => $cmd);
            }
        }
        $cmd = \config::byKey('emailAdmin', 'core', '');
        if ($cmd != '') {
            if (!CmdManager::byId(str_replace('#', '', $cmd))) {
                $result[] = array('detail' => 'Administration', 'help' => __('Commande information utilisateur'), 'who' => $cmd);
            }
        }
        foreach ($NEXTDOM_INTERNAL_CONFIG['alerts'] as $level => $value) {
            $cmds = \config::byKey('alert::' . $level . 'Cmd', 'core', '');
            preg_match_all("/#([0-9]*)#/", $cmds, $matches);
            foreach ($matches[1] as $cmd_id) {
                if (CmdManager::byId($cmd_id)) {
                    $result[] = array('detail' => 'Administration', 'help' => __('Commande sur ') . $value['name'], 'who' => '#' . $cmd_id . '#');
                }
            }
        }
        return $result;
    }

    /**
     * Get system health
     * Test all functionnalities
     *
     * @return array Data about system health
     */
    public static function health(): array
    {
        $okStr = __('common.ok');
        $nokStr = __('common.nok');

        $systemHealth = array();

        $state = true;
        $version = '';
        $uname = shell_exec('uname -a');
        if (SystemHelper::getDistrib() != 'debian') {
            $state = false;
        } else {
            $version = trim(strtolower(file_get_contents('/etc/debian_version')));
            if (version_compare($version, '8', '<')) {
                if (strpos($version, 'jessie') === false && strpos($version, 'stretch') === false) {
                    $state = false;
                }
            }
        }
        $systemHealth[] = array(
            'icon' => 'fa-cogs',
            'name' => __('health.os-version'),
            'state' => $state,
            'result' => ($state) ? $uname . ' [' . $version . ']' : $uname,
            'comment' => ($state) ? '' : __('Vous n\'êtes pas sur un OS officiellement supporté par l\'équipe NextDom (toute demande de support pourra donc être refusée). Les OS officiellement supporté sont Debian Jessie et Debian Strech (voir <a href="https://jeedom.github.io/documentation/compatibility/fr_FR/index" target="_blank">ici</a>)'),
        );

        $nbNeededUpdate = UpdateManager::nbNeedUpdate();
        $state = ($nbNeededUpdate == 0) ? true : false;
        $systemHealth[] = array(
            'icon' => 'fa-heartbeat',
            'name' => __('health.update-to-date'),
            'state' => $state,
            'result' => ($state) ? $okStr : $nbNeededUpdate,
            'comment' => '',
        );

        $state = (\config::byKey('enableCron', 'core', 1, true) != 0) ? true : false;
        $systemHealth[] = array(
            'icon' => 'fa-calendar-alt',
            'name' => __('health.cron-enabled'),
            'state' => $state,
            'result' => ($state) ? $okStr : $nokStr,
            'comment' => ($state) ? '' : __('health.cron-disabled'),
        );

        $state = (\config::byKey('enableScenario') == 0 && count(ScenarioManager::all()) > 0) ? false : true;
        $systemHealth[] = array(
            'icon' => 'fa-film',
            'name' => __('health.scenario-enabled'),
            'state' => $state,
            'result' => ($state) ? $okStr : $nokStr,
            'comment' => ($state) ? '' : __('health.scenario-disabled'),
        );

        $state = self::isStarted();
        $systemHealth[] = array(
            'icon' => 'fa-play',
            'name' => __('health.product-started'),
            'state' => $state,
            'result' => ($state) ? $okStr . ' - ' . file_get_contents(self::getTmpFolder() . '/started') : $nokStr,
            'comment' => '',
        );

        $state = self::isDateOk();
        $cache = CacheManager::byKey('hour');
        $lastKnowDate = $cache->getValue();
        $systemHealth[] = array(
            'icon' => 'fa-clock',
            'name' => __('health.system-date'),
            'state' => $state,
            'result' => ($state) ? $okStr . ' - ' . date('Y-m-d H:i:s') . ' (' . $lastKnowDate . ')' : date('Y-m-d H:i:s'),
            'comment' => ($state) ? '' : __('health.system-date-error'),
        );

        $state = self::isCapable('sudo', true);
        $systemHealth[] = array(
            'icon' => 'fa-user-secret',
            'name' => __('health.sudo-rights'),
            'state' => ($state) ? 1 : 2,
            'result' => ($state) ? $okStr : $nokStr,
            'comment' => ($state) ? '' : __('sudo-error'),
        );

        $systemHealth[] = array(
            'icon' => 'fa-code-branch',
            'name' => __('health.product-version'),
            'state' => true,
            'result' => self::getVersion(),
            'comment' => '',
        );

        $state = version_compare(phpversion(), '7.0', '>=');
        $systemHealth[] = array(
            'icon' => 'fa-code',
            'name' => __('health.php-version'),
            'state' => $state,
            'result' => phpversion(),
            'comment' => ($state) ? '' : __('health.php-error'),
        );

        $version = \DB::Prepare('select version()', array(), \DB::FETCH_TYPE_ROW);
        $systemHealth[] = array(
            'icon' => 'fa-database',
            'name' => __('health.database-version'),
            'state' => true,
            'result' => $version['version()'],
            'comment' => '',
        );

        $value = self::checkSpaceLeft();
        $systemHealth[] = array(
            'icon' => 'fa-hdd',
            'name' => __('health.harddisk-freespace'),
            'state' => ($value > 10),
            'result' => $value . ' %',
            'comment' => '',
        );

        $values = getSystemMemInfo();
        $value = round(($values['MemAvailable'] / $values['MemTotal']) * 100);
        $systemHealth[] = array(
            'icon' => 'fa-th',
            'name' => __('Mémoire disponible'),
            'state' => ($value > 15),
            'result' => $value . ' %',
            'comment' => '',
        );

        $value = shell_exec('sudo dmesg | grep oom | wc -l');
        $systemHealth[] = array(
            'icon' => 'fa-th-large',
            'name' => __('health.enough-memory'),
            'state' => ($value == 0),
            'result' => $value,
            'comment' => ($value == 0) ? '' : __('health.processes-killed'),
        );

        if ($values['SwapTotal'] != 0 && $values['SwapTotal'] !== null) {
            $value = round(($values['SwapFree'] / $values['SwapTotal']) * 100);
            $systemHealth[] = array(
                'icon' => 'fa-hdd',
                'name' => __('health.available-swap'),
                'state' => ($value > 15),
                'result' => $value . ' %',
                'comment' => '',
            );
        } else {
            $systemHealth[] = array(
                'icon' => 'fa-hdd',
                'name' => __('health.available-swap'),
                'state' => 2,
                'result' => __('health.unknow'),
                'comment' => '',
            );
        }

        $values = sys_getloadavg();
        $systemHealth[] = array(
            'icon' => 'fa-fire',
            'name' => __('health.load'),
            'state' => ($values[2] < 20),
            'result' => $values[0] . ' - ' . $values[1] . ' - ' . $values[2],
            'comment' => '',
        );

        $state = \network::test('internal');
        $systemHealth[] = array(
            'icon' => 'fa-plug',
            'name' => __('health.internal-network-conf'),
            'state' => $state,
            'result' => ($state) ? $okStr : $nokStr,
            'comment' => ($state) ? '' : __('health.network-config'),
        );

        $state = \network::test('external');
        $systemHealth[] = array(
            'icon' => 'fa-globe',
            'name' => __('health.external-network-conf'),
            'state' => $state,
            'result' => ($state) ? $okStr : $nokStr,
            'comment' => ($state) ? '' : __('health.network-config'),
        );

        $cache_health = array(
            'icon' => 'fa-inbox',
            'comment' => '',
            'name' => __('health.cache-persistence'));
        if (CacheManager::isPersistOk()) {
            if (\config::byKey('cache::engine') != 'FilesystemCache' && \config::byKey('cache::engine') != 'PhpFileCache') {
                $cache_health['state'] = true;
                $cache_health['result'] = $okStr;
            } else {
                $filename = __DIR__ . '/../../var/cache.tar.gz.tar.gz';
                $cache_health['state'] = true;
                $cache_health['result'] = $okStr . ' (' . date('Y-m-d H:i:s', filemtime($filename)) . ')';
            }
        } else {
            $cache_health['state'] = false;
            $cache_health['result'] = $nokStr;
            $cache_health['comment'] = __('health.cache-not-saved');
        }
        $systemHealth[] = $cache_health;

        $state = shell_exec('systemctl show apache2 | grep  PrivateTmp | grep yes | wc -l');
        $systemHealth[] = array(
            'icon' => 'fa-folder',
            'name' => __('health.apache-private-tmp'),
            'state' => $state,
            'result' => ($state) ? $okStr : $nokStr,
            'comment' => ($state) ? '' : __('health.apache-private-tmp-disabled'),
        );

        foreach (UpdateManager::listRepo() as $repo) {
            if ($repo['enable']) {
                $class = $repo['class'];
                if (class_exists($class) && method_exists($class, 'health')) {
                    $systemHealth += array_merge($systemHealth, $class::health());
                }
            }
        }

        return $systemHealth;
    }

    /**
     * Get informations about system installation
     */
    public static function sick()
    {
        $cmd = NEXTDOM_ROOT.'/scripts/sick.php';
        $cmd .= ' >> ' . \log::getPathToLog('sick') . ' 2>&1';
        SystemHelper::php($cmd);
    }

    /**
     * Test if NextDom running right
     *
     * @return bool
     */
    public static function isOk()
    {
        if (!self::isStarted()) {
            return false;
        }
        if (!self::isDateOk()) {
            return false;
        }
        if (\config::byKey('enableScenario') == 0 && count(ScenarioManager::all()) > 0) {
            return false;
        }
        if (!self::isCapable('sudo')) {
            return false;
        }
        if (\config::byKey('enableCron', 'core', 1, true) == 0) {
            return false;
        }
        return true;
    }

    /**
     * Start update
     *
     * @param array $options Options list of /install/update.php script
     */
    public static function update($options = array())
    {
        \log::clear('update');
        $params = '';
        if (count($options) > 0) {
            foreach ($options as $key => $value) {
                $params .= '"' . $key . '"="' . $value . '" ';
            }
        }
        $cmd = NEXTDOM_ROOT . '/install/update.php ' . $params;
        $cmd .= ' >> ' . \log::getPathToLog('update') . ' 2>&1 &';
        SystemHelper::php($cmd);
    }

    /**
     * Get configuration informations or global configuration
     *
     * @param string $askedKey
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public static function getConfiguration(string $askedKey = '', $defaultValue = false)
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        if ($askedKey == '') {
            return $NEXTDOM_INTERNAL_CONFIG;
        }
        if (!is_array(self::$nextdomConfiguration)) {
            self::$nextdomConfiguration = array();
        }
        // TODO: Bizarre
        if (!$defaultValue && isset(self::$nextdomConfiguration[$askedKey])) {
            return self::$nextdomConfiguration[$askedKey];
        }
        $keys = explode(':', $askedKey);

        $result = $NEXTDOM_INTERNAL_CONFIG;
        foreach ($keys as $key) {
            if (isset($result[$key])) {
                $result = $result[$key];
            }
        }
        if ($defaultValue) {
            return $result;
        }
        self::$nextdomConfiguration[$askedKey] = self::checkValueInConfiguration($askedKey, $result);

        return self::$nextdomConfiguration[$askedKey];
    }

    /**
     * TODO: ???
     *
     * @param string $configKey
     * @param mixed $configValue
     * @return array|mixed|string
     */
    private static function checkValueInConfiguration($configKey, $configValue)
    {
        if (!is_array(self::$nextdomConfiguration)) {
            self::$nextdomConfiguration = array();
        }
        if (isset(self::$nextdomConfiguration[$configKey])) {
            return self::$nextdomConfiguration[$configKey];
        }
        if (is_array($configValue)) {
            foreach ($configValue as $key => $value) {
                $configValue[$key] = self::checkValueInConfiguration($configKey . ':' . $key, $value);
            }
            self::$nextdomConfiguration[$configKey] = $configValue;
            return $configValue;
        } else {
            $config = \config::byKey($configKey);
            return ($config == '') ? $configValue : $config;
        }
    }

    /**
     * Get NextDom version
     *
     * @return string
     */
    public static function getVersion()
    {
        if (file_exists(NEXTDOM_ROOT . '/core/config/version')) {
            return trim(file_get_contents(NEXTDOM_ROOT . '/core/config/version'));
        }
        return '';
    }

    /**
     * Stop all cron tasks and scenarios
     */
    public static function stopSystem()
    {
        $okStr = __('common.ok');
        echo __('core.disable-tasks');
        \config::save('enableCron', 0);
        foreach (\cron::all() as $cron) {
            if ($cron->running()) {
                try {
                    $cron->halt();
                    echo '.';
                } catch (CoreException $e) {
                    sleep(5);
                    $cron->halt();
                }

            }
        }
        echo " $okStr\n";

        /*         * **********arrêt des crons********************* */

        if (\cron::jeeCronRun()) {
            echo __('core.disable-cron-master');
            $pid = \cron::getPidFile();
            SystemHelper::kill($pid);
            echo " $okStr\n";
        }

        /*         * *********Arrêt des scénarios**************** */

        echo __('core.disable-all-scenarios');
        \config::save('enableScenario', 0);
        foreach (ScenarioManager::all() as $scenario) {
            try {
                $scenario->stop();
                echo '.';
            } catch (CoreException $e) {
                sleep(5);
                $scenario->stop();
            }
        }
        echo " $okStr\n";
    }

    /**
     * Start all cron tasks and scenarios
     *
     * @throws CoreException
     */
    public static function startSystem()
    {
        $okStr = __('common.ok');

        try {
            echo __('core.enable-all-scenarios');
            \config::save('enableScenario', 1);
            echo " $okStr\n";
            echo __('core.enable-tasks');
            \config::save('enableCron', 1);
            echo " $okStr\n";
        } catch (CoreException $e) {
            if (!isset($_GET['mode']) || $_GET['mode'] != 'force') {
                throw $e;
            } else {
                echo '***ERROR*** ' . $e->getMessage();
            }
        }
    }

    /**
     * Test if NextDom is started
     *
     * @return bool True if NextDom is started
     */
    public static function isStarted(): bool
    {
        return file_exists(self::getTmpFolder() . '/started');
    }

    /**
     * Update time status and get it
     *
     * @return boolean Time status
     */
    public static function isDateOk()
    {
        if (\config::byKey('ignoreHourCheck') == 1) {
            return true;
        }
        $cache = CacheManager::byKey('hour');
        $lastKnowDate = $cache->getValue();
        if (strtotime($lastKnowDate) > strtotime('now')) {
            self::forceSyncHour();
            sleep(3);
            if (strtotime($lastKnowDate) > strtotime('now')) {
                return false;
            }
        }
        $minDateValue = new \DateTime('2017-01-01');
        $mindate = strtotime($minDateValue->format('Y-m-d 00:00:00'));
        $maxDateValue = $minDateValue->modify('+6 year')->format('Y-m-d 00:00:00');
        $maxdate = strtotime($maxDateValue);
        if (strtotime('now') < $mindate || strtotime('now') > $maxdate) {
            self::forceSyncHour();
            sleep(3);
            if (strtotime('now') < $mindate || strtotime('now') > $maxdate) {
                \log::add('core', 'error', sprintf(__('core.incorrect-sys-date'), $minDateValue, $maxDateValue) . (new \DateTime())->format('Y-m-d H:i:s'), 'dateCheckFailed');
                return false;
            }
        }
        return true;
    }

    /**
     * Check an event
     *
     * @param $event
     * @param bool $forceSyncMode
     */
    public static function event($event, $forceSyncMode = false)
    {
        ScenarioManager::check($event, $forceSyncMode);
    }

    /**
     * Task started minutes
     */
    public static function cron()
    {
        if (!self::isStarted()) {
            echo date('Y-m-d H:i:s') . ' starting NextDom';
            \log::add('starting', 'debug', __('Démarrage de nextdom'));
            try {
                \log::add('starting', 'debug', __('Arrêt des crons'));
                foreach (\cron::all() as $cron) {
                    if ($cron->running() && $cron->getClass() != 'nextdom' && $cron->getFunction() != 'cron') {
                        try {
                            $cron->halt();
                        } catch (CoreException $e) {
                            \log::add('starting', 'error', __('Erreur sur l\'arrêt d\'une tâche cron : ') . \log::exception($e));
                        }
                    }
                }
            } catch (CoreException $e) {
                \log::add('starting', 'error', __('Erreur sur l\'arrêt des tâches crons : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Restauration du cache'));
                CacheManager::restore();
            } catch (CoreException $e) {
                \log::add('starting', 'error', __('Erreur sur la restauration du CacheManager : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Nettoyage du cache des péripheriques USB'));
                $cache = CacheManager::byKey('nextdom::usbMapping');
                $cache->remove();
            } catch (CoreException $e) {
                \log::add('starting', 'error', __('Erreur sur le nettoyage du CacheManager des péripheriques USB : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Nettoyage du cache des péripheriques Bluetooth'));
                $cache = CacheManager::byKey('nextdom::bluetoothMapping');
                $cache->remove();
            } catch (CoreException $e) {
                \log::add('starting', 'error', __('Erreur sur le nettoyage du CacheManager des péripheriques Bluetooth : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Démarrage des processus Internet de NextDom'));
                self::startSystem();
            } catch (CoreException $e) {
                \log::add('starting', 'error', __('Erreur sur le démarrage interne de NextDom : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Ecriture du fichier ') . self::getTmpFolder() . '/started');
                if (file_put_contents(self::getTmpFolder() . '/started', date('Y-m-d H:i:s')) === false) {
                    \log::add('starting', 'error', __('Impossible d\'écrire ' . self::getTmpFolder() . '/started'));
                }
            } catch (CoreException $e) {
                \log::add('starting', 'error', __('Impossible d\'écrire ' . self::getTmpFolder() . '/started : ') . \log::exception($e));
            }

            if (!file_exists(self::getTmpFolder() . '/started')) {
                \log::add('starting', 'critical', __('Impossible d\'écrire ' . self::getTmpFolder() . '/started pour une raison inconnue. NextDom ne peut démarrer'));
                return;
            }

            try {
                \log::add('starting', 'debug', __('Vérification de la \configuration réseau interne'));
                if (!\network::test('internal')) {
                    \network::checkConf('internal');
                }
            } catch (CoreException $e) {
                \log::add('starting', 'error', __('Erreur sur la \configuration réseau interne : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Envoi de l\'événement de démarrage'));
                self::event('start');
            } catch (CoreException $e) {
                \log::add('starting', 'error', __('Erreur sur l\'envoi de l\'événement de démarrage : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Démarrage des plugins'));
                PluginManager::start();
            } catch (CoreException $e) {
                \log::add('starting', 'error', __('Erreur sur le démarrage des plugins : ') . \log::exception($e));
            }

            try {
                if (\config::byKey('market::enable') == 1) {
                    \log::add('starting', 'debug', __('Test de connexion au market'));
                    \repo_market::test();
                }
            } catch (CoreException $e) {
                \log::add('starting', 'error', __('Erreur sur la connexion au market : ') . \log::exception($e));
            }
            \log::add('starting', 'debug', __('Démarrage de nextdom fini avec succès'));
            EventManager::add('refresh');
        }
        self::isDateOk();
    }

    /**
     * Task started every 5 minutes
     */
    public static function cron5()
    {
        try {
            \network::cron5();
        } catch (CoreException $e) {
            \log::add('network', 'error', 'network::cron : ' . $e->getMessage());
        }
        try {
            foreach (\update::listRepo() as $name => $repo) {
                $class = 'repo_' . $name;
                if (class_exists($class) && method_exists($class, 'cron5') && \config::byKey($name . '::enable') == 1) {
                    $class::cron5();
                }
            }
        } catch (CoreException $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        }
        try {
            EqLogicManager::checkAlive();
        } catch (CoreException $e) {

        }
    }

    /**
     * Task started every hours
     */
    public static function cronHourly()
    {
        try {
            CacheManager::set('hour', date('Y-m-d H:i:s'));
        } catch (CoreException $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        }
        try {
            if (\config::byKey('update::autocheck', 'core', 1) == 1 && (\config::byKey('update::lastCheck') == '' || (strtotime('now') - strtotime(\config::byKey('update::lastCheck'))) > (23 * 3600))) {
                UpdateManager::checkAllUpdate();
                $updates = UpdateManager::byStatus('update');
                if (count($updates) > 0) {
                    $toUpdate = '';
                    foreach ($updates as $update) {
                        $toUpdate .= $update->getLogicalId() . ',';
                    }
                }
                $updates = \update::byStatus('update');
                if (count($updates) > 0) {
                    \message::add('update', __('De nouvelles mises à jour sont disponibles : ') . trim($toUpdate, ','), '', 'newUpdate');
                }
            }
        } catch (CoreException $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        }
        try {
            foreach (UpdateManager::listRepo() as $name => $repo) {
                $class = 'repo_' . $name;
                if (class_exists($class) && method_exists($class, 'cronHourly') && \config::byKey($name . '::enable') == 1) {
                    $class::cronHourly();
                }
            }
        } catch (CoreException $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        }
    }

    /**
     * Task started everyday
     */
    public static function cronDaily()
    {
        try {
            ScenarioManager::cleanTable();
            ScenarioManager::consystencyCheck();
            \log::chunk();
            \cron::clean();
            \report::clean();
            \DB::optimize();
            CacheManager::clean();
        } catch (CoreException $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        }
    }

    /**
     * TODO: ????
     * @param array $_replaces
     */
    public static function replaceTag(array $_replaces)
    {
        $datas = array();
        foreach ($_replaces as $key => $value) {
            $datas = array_merge($datas, CmdManager::searchConfiguration($key));
            $datas = array_merge($datas, EqLogicManager::searchConfiguration($key));
            $datas = array_merge($datas, JeeObjectManager::searchConfiguration($key));
            $datas = array_merge($datas, ScenarioManager::searchByUse(array(array('action' => '#' . $key . '#'))));
            $datas = array_merge($datas, ScenarioExpressionManager::searchExpression($key, $key, false));
            $datas = array_merge($datas, ScenarioExpressionManager::searchExpression('variable(' . str_replace('#', '', $key) . ')'));
            $datas = array_merge($datas, ScenarioExpressionManager::searchExpression('variable', str_replace('#', '', $key), true));
        }
        if (count($datas) > 0) {
            foreach ($datas as $data) {
                \utils::a2o($data, json_decode(str_replace(array_keys($_replaces), $_replaces, json_encode(\utils::o2a($data))), true));
                $data->save();
            }
        }
    }

    /**
     * TODO: ???
     *
     * @param $cmd
     *
     * @return string
     */
    public static function checkOngoingThread(string $cmd): string
    {
        return shell_exec('(ps ax || ps w) | grep "' . $cmd . '$" | grep -v "grep" | wc -l');
    }

    /**
     * Get command thread process id
     *
     * @param string $cmd Command to test
     *
     * @return string PID
     */
    public static function retrievePidThread(string $cmd)
    {
        return shell_exec('(ps ax || ps w) | grep "' . $cmd . '$" | grep -v "grep" | awk \'{print $1}\'');
    }

    /**
     * TODO: ??
     *
     * @param $version
     * @param bool $lightMode
     *
     * @return mixed|string
     */
    public static function versionAlias($version, bool $lightMode = true)
    {
        if (!$lightMode) {
            if ($version == 'dplan') {
                return 'plan';
            } else if ($version == 'dview') {
                return 'view';
            } else if ($version == 'mview') {
                return 'view';
            }
        }
        $alias = array(
            'mview' => 'mobile',
            'dview' => 'dashboard',
            'dplan' => 'dashboard',
        );
        return (isset($alias[$version])) ? $alias[$version] : $version;
    }

    /**
     * TODO: ?? Ca fait plein de choses
     *
     * @param $input
     *
     * @return string
     */
    public static function toHumanReadable($input)
    {
        return ScenarioManager::toHumanReadable(EqLogicManager::toHumanReadable(CmdManager::cmdToHumanReadable($input)));
    }

    /**
     * TODO: ?? Ca aussi ça fait plein de choses
     *
     * @param $input
     *
     * @return array|mixed(
     */
    public static function fromHumanReadable($input)
    {
        return ScenarioManager::fromHumanReadable(EqLogicManager::fromHumanReadable(CmdManager::humanReadableToCmd($input)));
    }

    /**
     * TODO: Ca évalue des choses dans les expressions
     *
     * @param $input
     * @param null $scenario
     *
     * @return mixed|string
     */
    public static function evaluateExpression($input, $scenario = null)
    {
        try {
            $input = ScenarioExpressionManager::setTags($input, $scenario, true);
            $result = evaluate($input);
            if (is_bool($result) || is_numeric($result)) {
                return $result;
            }
            return $input;
        } catch (CoreException $exc) {
            return $input;
        }
    }

    /**
     * TODO: Calcul les stats de quelquechose
     *
     * @param $calcType
     * @param $values
     *
     * @return float|int|null
     */
    public static function calculStat($calcType, $values)
    {
        switch ($calcType) {
            case 'sum':
                return array_sum($values);
                break;
            case 'avg':
                return array_sum($values) / count($values);
                break;
        }
        return null;
    }

    /**
     * TODO: Fait des trucs
     *
     * @param string $_string
     *
     * @return array
     */
    public static function getTypeUse($_string = '')
    {
        $result = array('cmd' => array(), 'scenario' => array(), 'eqLogic' => array(), 'dataStore' => array(), 'plan' => array(), 'view' => array());
        preg_match_all("/#([0-9]*)#/", $_string, $matches);
        foreach ($matches[1] as $cmd_id) {
            if (isset($result['cmd'][$cmd_id])) {
                continue;
            }
            $cmd = CmdManager::byId($cmd_id);
            if (!is_object($cmd)) {
                continue;
            }
            $result['cmd'][$cmd_id] = $cmd;
        }
        preg_match_all('/"scenario_id":"([0-9]*)"/', $_string, $matches);
        foreach ($matches[1] as $scenario_id) {
            if (isset($result['scenario'][$scenario_id])) {
                continue;
            }
            $scenario = ScenarioManager::byId($scenario_id);
            if (!is_object($scenario)) {
                continue;
            }
            $result['scenario'][$scenario_id] = $scenario;
        }
        preg_match_all("/#scenario([0-9]*)#/", $_string, $matches);
        foreach ($matches[1] as $scenario_id) {
            if (isset($result['scenario'][$scenario_id])) {
                continue;
            }
            $scenario = ScenarioManager::byId($scenario_id);
            if (!is_object($scenario)) {
                continue;
            }
            $result['scenario'][$scenario_id] = $scenario;
        }
        preg_match_all("/#eqLogic([0-9]*)#/", $_string, $matches);
        foreach ($matches[1] as $eqLogic_id) {
            if (isset($result['eqLogic'][$eqLogic_id])) {
                continue;
            }
            $eqLogic = EqLogicManager::byId($eqLogic_id);
            if (!is_object($eqLogic)) {
                continue;
            }
            $result['eqLogic'][$eqLogic_id] = $eqLogic;
        }
        preg_match_all('/"eqLogic":"([0-9]*)"/', $_string, $matches);
        foreach ($matches[1] as $eqLogic_id) {
            if (isset($result['eqLogic'][$eqLogic_id])) {
                continue;
            }
            $eqLogic = EqLogicManager::byId($eqLogic_id);
            if (!is_object($eqLogic)) {
                continue;
            }
            $result['eqLogic'][$eqLogic_id] = $eqLogic;
        }
        preg_match_all('/variable\((.*?)\)/', $_string, $matches);
        foreach ($matches[1] as $variable) {
            if (isset($result['dataStore'][$variable])) {
                continue;
            }
            $dataStore = \dataStore::byTypeLinkIdKey('scenario', -1, trim($variable));
            if (!is_object($dataStore)) {
                continue;
            }
            $result['dataStore'][$variable] = $dataStore;
        }
        preg_match_all('/"view_id":"([0-9]*)"/', $_string, $matches);
        foreach ($matches[1] as $view_id) {
            if (isset($result['view'][$view_id])) {
                continue;
            }
            $view = \view::byId($view_id);
            if (!is_object($view)) {
                continue;
            }
            $result['view'][$view_id] = $view;
        }
        preg_match_all('/"plan_id":"([0-9]*)"/', $_string, $matches);
        foreach ($matches[1] as $plan_id) {
            if (isset($result['plan'][$plan_id])) {
                continue;
            }
            $plan = \planHeader::byId($plan_id);
            if (!is_object($plan)) {
                continue;
            }
            $result['plan'][$plan_id] = $plan;
        }
        return $result;
    }

    /**
     * Shutdown the system
     */
    public static function haltSystem()
    {
        self::stopSystemAndExecuteCommand('shutdown -h now', __('Vous pouvez arrêter le système'));
    }

    /**
     * Reboot the system
     */
    public static function rebootSystem()
    {
        self::stopSystemAndExecuteCommand('reboot', __('Vous pouvez lancer le redémarrage du système'));
    }

    /**
     * TODO: ???
     *
     * @param string $command Command to execute after stop preparation
     * @param string $errorMessage Message to show if actions failed
     *
     * @throws CoreException
     */
    private static function stopSystemAndExecuteCommand($command, $errorMessage) {
        PluginManager::stop();
        CacheManager::persist();
        if (self::isCapable('sudo')) {
            exec(SystemHelper::getCmdSudo() . $command);
        } else {
            throw new CoreException($errorMessage);
        }
    }

    /**
     * Sync time
     */
    public static function forceSyncHour()
    {
        shell_exec(SystemHelper::getCmdSudo() . 'service ntp stop;' . SystemHelper::getCmdSudo() . 'ntpdate -s ' . \config::byKey('ntp::optionalServer', 'core', '0.debian.pool.ntp.org') . ';' . SystemHelper::getCmdSudo() . 'service ntp start');
    }

    /**
     * Clean file system rights
     */
    public static function cleanFileSystemRight()
    {
        $path = __DIR__ . '/../../*';
		$cmd = SystemHelper::getCmdSudo() . 'chown -R ' . SystemHelper::getWWWGid() . ':' . SystemHelper::getWWWUid() . ' ' . $path . ';';
		$cmd .= SystemHelper::getCmdSudo() . 'chmod 774 -R ' . $path . ';';
		$cmd .= SystemHelper::getCmdSudo() . 'find /var/log/nextdom -type f -exec chmod 664 {} +;';
		$cmd .= SystemHelper::getCmdSudo() . 'chmod 774 -R ' . $path . ';';
		var_dump($cmd);
		exec($cmd);
    }

    /**
     * Check space left
     *
     * @return float
     */
    public static function checkSpaceLeft(): float
    {
        return round(disk_free_space(NEXTDOM_ROOT) / disk_total_space(NEXTDOM_ROOT) * 100);
    }

    /**
     * Get temporary folder and creates it if not exists
     *
     * @param null $plugin
     *
     * @return string
     */
    public static function getTmpFolder($plugin = null) {
        $result = '/' . trim(\config::byKey('folder::tmp'), '/');
        if ($plugin !== null) {
            $result .= '/' . $plugin;
        }
        if (!file_exists($result)) {
            mkdir($result, 0774, true);
            $cmd = SystemHelper::getCmdSudo() . 'chown -R ' . SystemHelper::getWWWGid() . ':' . SystemHelper::getWWWUid() . ' ' . $result . ';';
            \com_shell::execute($cmd);
        }
        return $result;
    }

    /**
     * Get hardware key
     *
     * @return bool|string
     */
    public static function getHardwareKey()
    {
        $result = \config::byKey('nextdom::installKey');
        if ($result == '') {
            $result = substr(sha512(microtime() . \config::genKey()), 0, 63);
            \config::save('nextdom::installKey', $result);
        }
        return $result;
    }

    /**
     * Get hostname
     *
     * @return string
     */
    public static function getHardwareName()
    {
        if (\config::byKey('hardware_name') != '') {
            return \config::byKey('hardware_name');
        }
        $result = 'diy';
        $uname = shell_exec('uname -a');
        if (file_exists('/.dockerinit')) {
            $result = 'docker';
        } else if (file_exists('/usr/bin/raspi-config')) {
            $result = 'rpi';
        } else if (strpos($uname, 'cubox') !== false || strpos($uname, 'imx6') !== false || file_exists('/media/boot/multiboot/meson64_odroidc2.dtb.linux')) {
            $result = 'miniplus';
        }
        if (file_exists('/media/boot/multiboot/meson64_odroidc2.dtb.linux')) {
            $result = 'smart';
        }
        \config::save('hardware_name', $result);
        return \config::byKey('hardware_name');
    }

    /**
     * Test if NextDom can call system function
     *
     * @param string $systemFunc Function to test
     * @param bool $forceRefresh Force refresh in configuration
     *
     * @return bool True if $systemFunc can be executed
     */
    public static function isCapable($systemFunc, $forceRefresh = false)
    {
        global $NEXTDOM_COMPATIBILIY_CONFIG;
        if ($systemFunc == 'sudo') {
            if (!$forceRefresh) {
                $cache = CacheManager::byKey('nextdom::isCapable::sudo');
                if ($cache->getValue(0) == 1) {
                    return true;
                }
            }
            $result = (shell_exec('sudo -l > /dev/null 2>&1; echo $?') == 0) ? true : false;
            CacheManager::set('nextdom::isCapable::sudo', $result);
            return $result;
        }
        $hardware = self::getHardwareName();
        if (!isset($NEXTDOM_COMPATIBILIY_CONFIG[$hardware])) {
            return false;
        }
        if (in_array($systemFunc, $NEXTDOM_COMPATIBILIY_CONFIG[$hardware])) {
            return true;
        }
        return false;
    }

    /**
     * Benchmark cache
     *
     * @return array
     * @throws \Exception
     */
    public static function benchmark()
    {
        $result = array();

        $param = array('cache_write' => 5000, 'cache_read' => 5000, 'database_write_delete' => 1000, 'database_update' => 1000, 'database_replace' => 1000, 'database_read' => 50000, 'subprocess' => 200);

        $starttime = getmicrotime();
        for ($i = 0; $i < $param['cache_write']; $i++) {
            CacheManager::set('nextdom_benchmark', $i);
        }
        $result['cache_write_' . $param['cache_write']] = getmicrotime() - $starttime;

        $starttime = getmicrotime();
        for ($i = 0; $i < $param['cache_read']; $i++) {
            $cache = CacheManager::byKey('nextdom_benchmark');
            $cache->getValue();
        }
        $result['cache_read_' . $param['cache_read']] = getmicrotime() - $starttime;

        $starttime = getmicrotime();
        for ($i = 0; $i < $param['database_write_delete']; $i++) {
            $sql = 'DELETE FROM config
                    WHERE `key`="nextdom_benchmark"
                    AND plugin="core"';
            try {
                \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ROW);
            } catch (CoreException $e) {

            }
            $sql = 'INSERT INTO config
                    SET `key`="nextdom_benchmark",plugin="core",`value`="' . $i . '"';
            try {
                \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ROW);
            } catch (CoreException $e) {

            }
        }
        $result['database_write_delete_' . $param['database_write_delete']] = getmicrotime() - $starttime;

        $sql = 'INSERT INTO config
                SET `key`="nextdom_benchmark",plugin="core",`value`="0"';
        try {
            \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ROW);
        } catch (CoreException $e) {
        }
        $starttime = getmicrotime();
        for ($i = 0; $i < $param['database_update']; $i++) {
            $sql = 'UPDATE `config`
                    SET `value`=:value
                    WHERE `key`="nextdom_benchmark"
                        AND plugin="core"';
            try {
                \DB::Prepare($sql, array('value' => $i), \DB::FETCH_TYPE_ROW);
            } catch (CoreException $e) {

            }
        }
        $result['database_update_' . $param['database_update']] = getmicrotime() - $starttime;

        $starttime = getmicrotime();
        for ($i = 0; $i < $param['database_replace']; $i++) {
            \config::save('nextdom_benchmark', $i);
        }
        $result['database_replace_' . $param['database_replace']] = getmicrotime() - $starttime;

        $starttime = getmicrotime();
        for ($i = 0; $i < $param['database_read']; $i++) {
            \config::byKey('nextdom_benchmark');
        }
        $result['database_read_' . $param['database_read']] = getmicrotime() - $starttime;

        $starttime = getmicrotime();
        for ($i = 0; $i < $param['subprocess']; $i++) {
            shell_exec('echo ' . $i);
        }
        $result['subprocess_' . $param['subprocess']] = getmicrotime() - $starttime;

        $total = 0;
        foreach ($result as $value) {
            $total += $value;
        }
        $result['total'] = $total;
        return $result;
    }
}
