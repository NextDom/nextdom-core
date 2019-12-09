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

use NextDom\Enums\ConfigKey;
use NextDom\Enums\DateFormat;
use NextDom\Enums\LogTarget;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\SystemCron;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\CacheManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\CronManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\EventManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\MessageManager;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PlanManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\UpdateManager;
use NextDom\Managers\ViewManager;
use NextDom\Model\DataClass\SystemHealth;
use NextDom\Repo\RepoMarket;

/**
 * Class NextDomHelper
 * @package NextDom\Helpers
 */
class NextDomHelper
{
    /**
     *
     */
    private static $nextdomConfiguration;

    /**
     * Add an entry in the history and keep only the last 200
     * @param $data
     */
    public static function addRemoveHistory($data)
    {
        $removeHistory = [];
        $removeHistoryPath = NEXTDOM_DATA . '/data/remove_history.json';
        if (file_exists($removeHistoryPath)) {
            $removeHistory = json_decode(file_get_contents($removeHistoryPath), true);
        }
        $removeHistory[] = $data;
        $removeHistory = array_slice($removeHistory, -200, 200);
        file_put_contents($removeHistoryPath, json_encode($removeHistory));
    }

    /**
     * Get all dead commands
     *
     * @return array
     * @throws \Exception
     */
    public static function getDeadCmd()
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        $result = [];
        $cmd = ConfigManager::byKey('interact::warnme::defaultreturncmd', 'core', '');
        if ($cmd != '' && !CmdManager::byId(str_replace('#', '', $cmd))) {
            $result[] = ['detail' => 'Administration', 'help' => __('Commande retour interactions'), 'who' => $cmd];
        }
        $cmd = ConfigManager::byKey('emailAdmin', 'core', '');
        if ($cmd != '' && !CmdManager::byId(str_replace('#', '', $cmd))) {
            $result[] = ['detail' => 'Administration', 'help' => __('Commande information utilisateur'), 'who' => $cmd];
        }
        foreach ($NEXTDOM_INTERNAL_CONFIG['alerts'] as $level => $value) {
            $cmds = ConfigManager::byKey('alert::' . $level . 'Cmd', 'core', '');
            preg_match_all("/#([0-9]*)#/", $cmds, $matches);
            foreach ($matches[1] as $cmd_id) {
                if (CmdManager::byId($cmd_id)) {
                    $result[] = ['detail' => 'Administration', 'help' => __('Commande sur ') . $value['name'], 'who' => '#' . $cmd_id . '#'];
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
     * @throws \Exception
     */
    public static function health(): array
    {
        $okStr = __('common.ok');
        $nokStr = __('common.nok');

        $systemHealth = [];

        $state = true;
        $version = '';
        $uname = SystemHelper::getSystemInformations();
        if (SystemHelper::getDistrib() != 'debian') {
            $state = false;
        } else {
            $version = trim(strtolower(file_get_contents('/etc/debian_version')));
            if (version_compare($version, '8', '<')
                && strpos($version, 'jessie') === false
                && strpos($version, 'stretch') === false
                && strpos($version, 'buster') === false) {
                $state = false;
            }
        }
        $systemHealth[] = new SystemHealth(
            'fa-cogs',
            'health.os-version',
            $state,
            ($state) ? $uname . ' [' . $version . ']' : $uname,
            ($state) ? '' : __('health.os-not-supported'),
            'os::version');

        $nbNeededUpdate = UpdateManager::nbNeedUpdate();
        $state = ($nbNeededUpdate == 0) ? true : false;
        $systemHealth[] = new SystemHealth('fa-heartbeat', 'health.update-to-date', $state, ($state) ? $okStr : $nbNeededUpdate . ' ' . __('health.updates'), '', 'uptodate');

        $state = ConfigManager::byKey(ConfigKey::ENABLE_CRON, 'core', 1, true) != 0;
        $systemHealth[] = new SystemHealth('fa-calendar-alt', 'health.cron-enabled', $state, ($state) ? $okStr : $nokStr, ($state) ? '' : __('health.cron-disabled'), 'cron::enable');

        $state = !(ConfigManager::byKey(ConfigKey::ENABLE_SCENARIO) == 0 && count(ScenarioManager::all()) > 0);
        $systemHealth[] = new SystemHealth('fa-film', 'health.scenario-enabled', $state, ($state) ? $okStr : $nokStr, ($state) ? '' : __('health.scenario-disabled'), 'scenario::enable');

        $state = self::isStarted();
        $systemHealth[] = new SystemHealth('fa-play', 'health.product-started', $state, ($state) ? $okStr . ' - ' . file_get_contents(self::getStartedFilePath()) : $nokStr, '', 'isStarted');

        $state = self::isDateOk();
        $cache = CacheManager::byKey('hour');
        $lastKnowDate = $cache->getValue();
        $systemHealth[] = new SystemHealth('fa-clock', 'health.system-date', $state, ($state) ? $okStr . ' - ' . date(DateFormat::FULL) . ' (' . $lastKnowDate . ')' : date(DateFormat::FULL), ($state) ? '' : __('health.system-date-error'), 'hour');

        $state = self::isCapable('sudo', true);
        $systemHealth[] = new SystemHealth('fa-user-secret', 'health.sudo-rights', ($state) ? 1 : 2, ($state) ? $okStr : $nokStr, ($state) ? '' : __('sudo-error'), 'sudo::right');

        $systemHealth[] = new SystemHealth('fa-code-branch', 'health.product-version', true, self::getNextdomVersion(), '', 'nextdom::version');

        $state = version_compare(phpversion(), '7.0', '>=');
        $systemHealth[] = new SystemHealth('fa-code', 'health.php-version', $state, phpversion(), ($state) ? '' : __('health.php-error'), 'php::version');

        $version = DBHelper::getOne('select version()');
        $systemHealth[] = new SystemHealth('fa-database', 'health.database-version', true, $version['version()'], ($state) ? '' : __('health.php-error'), 'database::version');

        $value = self::checkSpaceLeft();
        $systemHealth[] = new SystemHealth('fa-hdd', 'health.harddisk-freespace', ($value > 10), $value . ' %', __('health.need-more-than') . ': 10%', 'space::root');

        $value = self::checkSpaceLeft(self::getTmpFolder());
        $systemHealth[] = new SystemHealth('fa-hdd', 'health.tmp-space-left', ($value > 10), $value . ' %', __('health.tmp-problem') . ': 10%', 'space::tmp');

        $values = SystemHelper::getMemInfo();
        $value = round(($values['MemAvailable'] / $values['MemTotal']) * 100);
        $systemHealth[] = new SystemHealth('fa-th', 'health.available-memory', ($value > 15), $value . ' %', __('health.need-more-than') . ': 15%', 'meminfo');

        $value = shell_exec('sudo dmesg | grep oom | grep -v deprecated | wc -l');
        $systemHealth[] = new SystemHealth('fa-th-large', 'health.enough-memory', ($value == 0), ($state == 0) ? $nokStr : $okStr, ($value == 0) ? '' : __('health.processes-killed'), 'oom');

        $value = shell_exec('sudo dmesg | grep "CRC error" | grep "mmcblk0" | grep "card status" | wc -l');
        if (!is_numeric($value)) {
            $value = 0;
        }
        $value2 = @shell_exec('sudo dmesg | grep "I/O error" | wc -l');
        if (is_numeric($value2)) {
            $value += $value2;
        }
        $systemHealth[] = new SystemHealth('fa fa-hearth', __('Erreur I/O'), $value == 0, $value, ($value == 0) ? '' : __('Il y a des erreurs disque, cela peut indiquer un soucis avec le disque ou un problème d\'alimentation'), 'io_error');

        if ($values['SwapTotal'] != 0 && $values['SwapTotal'] !== null) {
            $value = round(($values['SwapFree'] / $values['SwapTotal']) * 100);
            $systemHealth[] = new SystemHealth('fa-hdd', 'health.available-swap', ($value > 15), $value . ' %', __('health.need-more-than') . ': 15%', 'swap');
        } else {
            $systemHealth[] = new SystemHealth('fa-hdd', 'health.available-swap', 2, __('health.unknow'), '', 'swap');
        }

        $values = sys_getloadavg();
        $systemHealth[] = new SystemHealth('fa-fire', 'health.load', ($values[2] < 20), $values[0] . ' - ' . $values[1] . ' - ' . $values[2], __('health.need-less-than') . ': 20', 'load');

        $state = NetworkHelper::test('internal');
        $systemHealth[] = new SystemHealth('fa-plug', 'health.internal-network-conf', $state, ($state) ? $okStr : $nokStr, ($state) ? '' : __('health.network-config'), 'network::internal');

        $state = NetworkHelper::test('external');
        $systemHealth[] = new SystemHealth('fa-globe', 'health.external-network-conf', $state, ($state) ? $okStr : $nokStr, ($state) ? '' : __('health.network-config'), 'network::external');

        $cacheHealth = new SystemHealth('fa-inbox', 'health.cache-persistence', false, '', '', 'cache::persit');

        if (CacheManager::isPersistOk()) {
            if ((ConfigManager::byKey('cache::engine') != 'FilesystemCache') &&
                (ConfigManager::byKey('cache::engine') != 'PhpFileCache')) {
                $cacheHealth->setState(true);
                $cacheHealth['result'] = $okStr;
            } else {
                $cache_path = CacheManager::getArchivePath();
                $cache_time = date(DateFormat::FULL, filemtime($cache_path));
                $cacheHealth->setState(true);
                $cacheHealth->setResult(sprintf("%s (%s)", $okStr, $cache_time));
            }
        } else {
            $cacheHealth->setState(false)
                ->setResult($nokStr)
                ->setComment(__('health.cache-not-saved'));
        }

        $systemHealth[] = $cacheHealth;

        $state = shell_exec('systemctl show apache2 | grep  PrivateTmp | grep yes | wc -l');
        $systemHealth[] = new SystemHealth('fa-folder', 'health.apache-private-tmp', $state, ($state) ? $okStr : $nokStr, ($state) ? '' : __('health.apache-private-tmp-disabled'), 'apache2::privateTmp');

        foreach (UpdateManager::listRepo() as $repo) {
            if ($repo['enable']) {
                $targetClass = $repo['class'];
                if (class_exists($targetClass) && method_exists($targetClass, 'health')) {
                    $systemHealth += array_merge($systemHealth, $targetClass::health());
                }
            }
        }

        return $systemHealth;
    }

    /**
     * Test if NextDom is started
     *
     * @return bool True if NextDom is started
     * @throws \Exception
     */
    public static function isStarted(): bool
    {
        return file_exists(self::getStartedFilePath());
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getStartedFilePath(): string
    {
        return sprintf("%s/started", self::getTmpFolder());
    }

    /**
     * Get temporary folder and creates it if not exists
     *
     * @param string|null $subFolder Log subfolder
     *
     * @return string
     * @throws \Exception
     */
    public static function getTmpFolder($subFolder = null)
    {
        $result = '/' . trim(ConfigManager::byKey('folder::tmp'), '/');
        if ($subFolder !== null) {
            $result .= '/' . $subFolder;
        }
        if (!file_exists($result)) {
            mkdir($result, 0775, true);
        }
        return $result;
    }

    /**
     * Update time status and get it
     *
     * @return boolean Time status
     * @throws \Exception
     */
    public static function isDateOk()
    {
        if (ConfigManager::byKey('ignoreHourCheck') == 1) {
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
                LogHelper::addError('core', sprintf(__('core.incorrect-sys-date'), $minDateValue, $maxDateValue) . (new \DateTime())->format(DateFormat::FULL), 'dateCheckFailed');
                return false;
            }
        }
        return true;
    }

    /**
     * Sync time
     */
    public static function forceSyncHour()
    {
        shell_exec(SystemHelper::getCmdSudo() . 'service ntp stop;' . SystemHelper::getCmdSudo() . 'ntpdate -s ' . ConfigManager::byKey('ntp::optionalServer', 'core', '0.debian.pool.ntp.org') . ';' . SystemHelper::getCmdSudo() . 'service ntp start');
    }

    /**
     * Test if NextDom can call system function
     *
     * @param string $systemFunc Function to test
     * @param bool $forceRefresh Force refresh in configuration
     *
     * @return bool True if $systemFunc can be executed
     * @throws \Exception
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
            $result = shell_exec('sudo -l > /dev/null 2>&1; echo $?') == 0;
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
     * Get hostname
     *
     * @return string
     * @throws \Exception
     */
    public static function getHardwareName()
    {
        if (ConfigManager::byKey(ConfigKey::HARDWARE_NAME) != '') {
            return ConfigManager::byKey(ConfigKey::HARDWARE_NAME);
        }
        $result = 'diy';
        $uname = SystemHelper::getSystemInformations();
        if (strpos(shell_exec('sudo cat /proc/1/cgroup'), 'docker') !== false) {
            $result = 'docker';
        } else if (file_exists('/usr/bin/raspi-config')) {
            $result = 'rpi';
            $hardware_revision = strtolower(shell_exec('cat /proc/cpuinfo | grep Revision'));
            global $NEXTDOM_RPI_HARDWARE;
            foreach ($NEXTDOM_RPI_HARDWARE as $key => $values) {
                foreach ($values as $value) {
                    if (strpos($hardware_revision, $value) !== false) {
                        $result = $key;
                    }
                }
            }
        } else if (strpos($uname, 'cubox') !== false || strpos($uname, 'imx6') !== false || file_exists('/media/boot/multiboot/meson64_odroidc2.dtb.linux')) {
            $result = 'miniplus';
        }
        if (file_exists('/media/boot/multiboot/meson64_odroidc2.dtb.linux')) {
            $result = 'smart';
        }
        ConfigManager::save(ConfigKey::HARDWARE_NAME, $result);
        return ConfigManager::byKey(ConfigKey::HARDWARE_NAME);
    }

    /**
     * Get Nextdom version
     *
     * @return string
     */
    public static function getNextdomVersion()
    {
        if (file_exists(NEXTDOM_DATA . '/config/Nextdom_version')) {
            return trim(file_get_contents(NEXTDOM_DATA . '/config/Nextdom_version'));
        }
        return '';
    }

    /**
     * Check space left
     *
     * @param null $directory
     * @return float
     */
    public static function checkSpaceLeft($directory = null): float
    {
        if ($directory == null) {
            $pathToCheck = NEXTDOM_ROOT;
        } else {
            $pathToCheck = $directory;
        }
        return round(disk_free_space($pathToCheck) / disk_total_space($pathToCheck) * 100);
    }

    /**
     * Get informations about system installation
     */
    public static function sick()
    {
        $cmd = NEXTDOM_ROOT . '/scripts/sick.php';
        $cmd .= ' >> ' . LogHelper::getPathToLog('sick') . ' 2>&1';
        SystemHelper::php($cmd);
    }

    /**
     * Test if NextDom running right
     *
     * @return bool
     * @throws \Exception
     */
    public static function isOk()
    {
        if (!self::isStarted()) {
            return false;
        }
        if (!self::isDateOk()) {
            return false;
        }
        if (ConfigManager::byKey(ConfigKey::ENABLE_SCENARIO) == 0 && count(ScenarioManager::all()) > 0) {
            return false;
        }
        if (!self::isCapable('sudo')) {
            return false;
        }
        if (ConfigManager::byKey(ConfigKey::ENABLE_CRON, 'core', 1, true) == 0) {
            return false;
        }
        return true;
    }

    /**
     * Start update
     *
     * @param array $options Options list of /install/update.php script
     * @throws \Exception
     */
    public static function update($options = [])
    {
        LogHelper::clear('update');
        $params = '';
        foreach ($options as $key => $value) {
            $params .= '"' . $key . '"="' . $value . '" ';
        }
        SystemHelper::php(NEXTDOM_ROOT . '/install/update.php ' . $params . ' >> ' . LogHelper::getPathToLog('update') . ' &');
    }

    /**
     * Get configuration informations or global configuration
     *
     * @param string $askedKey
     * @param mixed $defaultValue
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getConfiguration(string $askedKey = '', $defaultValue = false)
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        if ($askedKey == '') {
            return $NEXTDOM_INTERNAL_CONFIG;
        }
        if (!is_array(self::$nextdomConfiguration)) {
            self::$nextdomConfiguration = [];
        }
        // @TODO: Bizarre
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
     * @TODO: ???
     *
     * @param string $configKey
     * @param mixed $configValue
     * @return array|mixed|string
     * @throws \Exception
     */
    public static function checkValueInConfiguration($configKey, $configValue)
    {
        if (!is_array(self::$nextdomConfiguration)) {
            self::$nextdomConfiguration = [];
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
            $config = ConfigManager::byKey($configKey);
            return ($config == '') ? $configValue : $config;
        }
    }

    /**
     * Get Jeedom version
     *
     * @return string
     */
    public static function getJeedomVersion()
    {
        if (file_exists(NEXTDOM_DATA . '/config/Jeedom_version')) {
            return trim(file_get_contents(NEXTDOM_DATA . '/config/Jeedom_version'));
        }
        return '';
    }

    /**
     * Stop all cron tasks and scenarios
     * @param bool $isBackupProcess if true stop all except backup cron
     * @throws \Exception
     */
    public static function stopSystem($isBackupProcess)
    {
        // $okStr = __('common.ok');
        // echo __('core.disable-tasks');
        ConfigManager::save('enableCron', 0);
        foreach (CronManager::all() as $cron) {
            if($isBackupProcess && $cron->getClass() === SystemCron::NEXTDOM_BACKUP[0] && $cron->getFunction() === SystemCron::NEXTDOM_BACKUP[1] ){
                LogHelper::addInfo(LogTarget::MIGRATION, "Stopping all cron in backup context. Backup's cron won't be stop");
            } else if ($cron->running()) {
                try {
                    $cron->halt();
                } catch (\Exception $e) {
                    sleep(5);
                    $cron->halt();
                }

            }
        }
        // echo " $okStr\n";

        /*         * **********arrêt des crons********************* */

        if (CronManager::jeeCronRun()) {
            $pid = CronManager::getPidFile();
            SystemHelper::kill($pid);
        }

        /*         * *********Arrêt des scénarios**************** */

        ConfigManager::save('enableScenario', 0);
        foreach (ScenarioManager::all() as $scenario) {
            try {
                $scenario->stop();
            } catch (\Exception $e) {
                sleep(5);
                $scenario->stop();
            }
        }
        SystemHelper::stopService('cron');
    }

    /**
     * Task started minutes
     */
    public static function cron()
    {
        if (!self::isStarted()) {
            echo date(DateFormat::FULL) . ' starting NextDom';
            LogHelper::addDebug(LogTarget::STARTING, __('Démarrage de nextdom'));
            try {
                LogHelper::addDebug(LogTarget::STARTING, __('Arrêt des crons'));
                foreach (CronManager::all() as $cron) {
                    if ($cron->running() && $cron->getClass() != 'nextdom' && $cron->getFunction() != 'cron') {
                        try {
                            $cron->halt();
                        } catch (\Exception $e) {
                            LogHelper::addError(LogTarget::STARTING, __('Erreur sur l\'arrêt d\'une tâche cron : ') . LogHelper::exception($e));
                        }
                    }
                }
            } catch (\Exception $e) {
                LogHelper::addError(LogTarget::STARTING, __('Erreur sur l\'arrêt des tâches crons : ') . LogHelper::exception($e));
            }

            try {
                LogHelper::addDebug(LogTarget::STARTING, __('Restauration du cache'));
                CacheManager::restore();
            } catch (\Exception $e) {
                LogHelper::addError(LogTarget::STARTING, __('Erreur sur la restauration du CacheManager : ') . LogHelper::exception($e));
            }

            try {
                LogHelper::addDebug(LogTarget::STARTING, __('Nettoyage du cache des péripheriques USB'));
                $cache = CacheManager::byKey('nextdom::usbMapping');
                $cache->remove();
            } catch (\Exception $e) {
                LogHelper::addError(LogTarget::STARTING, __('Erreur sur le nettoyage du CacheManager des péripheriques USB : ') . LogHelper::exception($e));
            }

            try {
                LogHelper::addDebug(LogTarget::STARTING, __('Nettoyage du cache des péripheriques Bluetooth'));
                $cache = CacheManager::byKey('nextdom::bluetoothMapping');
                $cache->remove();
            } catch (\Exception $e) {
                LogHelper::addError(LogTarget::STARTING, __('Erreur sur le nettoyage du CacheManager des péripheriques Bluetooth : ') . LogHelper::exception($e));
            }

            try {
                LogHelper::addDebug(LogTarget::STARTING, __('Démarrage des processus Internet de NextDom'));
                self::startSystem();
            } catch (\Exception $e) {
                LogHelper::addError(LogTarget::STARTING, __('Erreur sur le démarrage interne de NextDom : ') . LogHelper::exception($e));
            }

            try {
                LogHelper::addDebug(LogTarget::STARTING, __('Ecriture du fichier ') . self::getStartedFilePath());
                if (file_put_contents(self::getStartedFilePath(), date(DateFormat::FULL)) === false) {
                    LogHelper::addError(LogTarget::STARTING, __('Impossible d\'écrire ' . self::getStartedFilePath()));
                }
            } catch (\Exception $e) {
                LogHelper::addError(LogTarget::STARTING, __('Impossible d\'écrire ' . self::getStartedFilePath() . ' : ') . LogHelper::exception($e));
            }

            if (!file_exists(self::getStartedFilePath())) {
                LogHelper::addCritical(LogTarget::STARTING, __('Impossible d\'écrire ' . self::getStartedFilePath() . ' pour une raison inconnue. NextDom ne peut démarrer'));
                return;
            }

            try {
                LogHelper::addDebug(LogTarget::STARTING, __('Vérification de la configuration réseau interne'));
                if (!NetworkHelper::test('internal')) {
                    NetworkHelper::checkConf('internal');
                }
            } catch (\Exception $e) {
                LogHelper::addError(LogTarget::STARTING, __('Erreur sur la configuration réseau interne : ') . LogHelper::exception($e));
            }

            try {
                LogHelper::addDebug(LogTarget::STARTING, __('Envoi de l\'événement de démarrage'));
                self::event('start');
            } catch (\Exception $e) {
                LogHelper::addError(LogTarget::STARTING, __('Erreur sur l\'envoi de l\'événement de démarrage : ') . LogHelper::exception($e));
            }

            try {
                LogHelper::addDebug(LogTarget::STARTING, __('Démarrage des plugins'));
                PluginManager::start();
            } catch (\Exception $e) {
                LogHelper::addError(LogTarget::STARTING, __('Erreur sur le démarrage des plugins : ') . LogHelper::exception($e));
            }

            try {
                if (ConfigManager::byKey('market::enable') == 1) {
                    LogHelper::addDebug(LogTarget::STARTING, __('Test de connexion au market'));
                    RepoMarket::test();
                }
            } catch (\Exception $e) {
                LogHelper::addError(LogTarget::STARTING, __('Erreur sur la connexion au market : ') . LogHelper::exception($e));
            }
            LogHelper::addDebug(LogTarget::STARTING, __('Démarrage de nextdom fini avec succès'));
            EventManager::add('refresh');
        }
        self::isDateOk();
    }

    /**
     * Start all cron tasks and scenarios
     *
     * @throws \Exception
     */
    public static function startSystem()
    {
        ConfigManager::save(ConfigKey::ENABLE_SCENARIO, 1);
        ConfigManager::save(ConfigKey::ENABLE_CRON, 1);
        // Create log folder
        self::getTmpFolder();
        if (!is_dir('/tmp/jeedom')) {
            system('ln -s ' . ConfigManager::byKey(ConfigKey::TMP_FOLDER) . ' /tmp/jeedom');
        }
        SystemHelper::startService('cron');
    }

    /**
     * Check an event
     *
     * @param \event|string $event
     * @param bool $forceSyncMode
     * @throws \Exception
     */
    public static function event($event, $forceSyncMode = false)
    {
        ScenarioManager::check($event, $forceSyncMode);
    }

    /**
     * Task started every 5 minutes
     */
    public static function cron5()
    {
        try {
            NetworkHelper::cron5();
        } catch (\Exception $e) {
            LogHelper::addError('network', 'network::cron : ' . $e->getMessage());
        }
        try {
            foreach (UpdateManager::listRepo() as $name => $repo) {
                $repoClass = 'Repo' . $name;
                if (class_exists($repoClass) && method_exists($repoClass, 'cron5') && ConfigManager::byKey($name . '::enable') == 1) {
                    $repoClass::cron5();
                }
            }
        } catch (\Exception $e) {
            LogHelper::addError(LogTarget::NEXTDOM, $e->getMessage());
        }
        try {
            EqLogicManager::checkAlive();
        } catch (\Exception $e) {

        }
    }

    public static function cron10()
    {
        try {
            foreach (UpdateManager::listRepo() as $name => $repo) {
                $repoClass = 'Repo' . $name;
                if (class_exists($repoClass) && method_exists($repoClass, 'cron10') && ConfigManager::byKey($name . '::enable') == 1) {
                    $repoClass::cron10();
                }
            }
        } catch (\Exception $e) {
            LogHelper::addError(LogTarget::NEXTDOM, $e->getMessage());
        }
    }

    /**
     * Task started every hours
     */
    public static function cronHourly()
    {
        try {
            CacheManager::set('hour', date(DateFormat::FULL));
        } catch (\Exception $e) {
            LogHelper::addError('nextdom', $e->getMessage());
        }
        try {
            if ((ConfigManager::byKey('update::lastCheck') == '' || (strtotime('now') - strtotime(ConfigManager::byKey('update::lastCheck'))) > (23 * 3600))) {
                UpdateManager::checkAllUpdate();
                $updates = UpdateManager::byStatus('update');
                $toUpdate = '';
                if (count($updates) > 0) {
                    foreach ($updates as $update) {
                        $toUpdate .= $update->getLogicalId() . ',';
                    }
                }
                $updates = UpdateManager::byStatus('update');
                if (count($updates) > 0) {
                    MessageManager::add('update', __('De nouvelles mises à jour sont disponibles : ') . trim($toUpdate, ','), '', 'newUpdate');
                }
            }
        } catch (\Exception $e) {
            LogHelper::addError('nextdom', $e->getMessage());
        }
        try {
            foreach (UpdateManager::listRepo() as $name => $repo) {
                $repoClass = 'Repo' . $name;
                if (class_exists($repoClass) && method_exists($repoClass, 'cronHourly') && ConfigManager::byKey($name . '::enable') == 1) {
                    $repoClass::cronHourly();
                }
            }
        } catch (\Exception $e) {
            LogHelper::addError('nextdom', $e->getMessage());
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
            LogHelper::chunk();
            CronManager::clean();
            ReportHelper::clean();
            DBHelper::optimize();
            CacheManager::clean();
            foreach (UpdateManager::listRepo() as $repoCode => $repoData) {
                $classCode = 'repo_' . $repoCode;
                $fullClassName = $repoCode['class'];
                if (class_exists($fullClassName) && method_exists($fullClassName, 'cronDaily') && ConfigManager::byKey($classCode . '::enable') == 1) {
                    $fullClassName::cronDaily();
                }
            }

        } catch (\Exception $e) {
            LogHelper::addError('nextdom', $e->getMessage());
        }
    }

    /**
     * @TODO: ????
     * @param array $_replaces
     * @throws \Exception
     */
    public static function replaceTag(array $_replaces)
    {
        $datas = [];
        foreach ($_replaces as $key => $value) {
            $datas = array_merge($datas, CmdManager::searchConfiguration($key));
            $datas = array_merge($datas, EqLogicManager::searchConfiguration($key));
            $datas = array_merge($datas, JeeObjectManager::searchConfiguration($key));
            $datas = array_merge($datas, ScenarioManager::searchByUse([['action' => '#' . $key . '#']]));
            $datas = array_merge($datas, ScenarioExpressionManager::searchExpression($key, $key, false));
            $datas = array_merge($datas, ScenarioExpressionManager::searchExpression('variable(' . str_replace('#', '', $key) . ')'));
            $datas = array_merge($datas, ScenarioExpressionManager::searchExpression('variable', str_replace('#', '', $key), true));
        }
        if (count($datas) > 0) {
            foreach ($datas as $data) {
                Utils::a2o($data, json_decode(str_replace(array_keys($_replaces), $_replaces, json_encode(Utils::o2a($data))), true));
                $data->save();
            }
        }
    }

    /**
     * @TODO: ???
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
     * @TODO: ??
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
        $alias = [
            'dview' => 'dashboard',
            'dplan' => 'dashboard',
        ];
        return (isset($alias[$version])) ? $alias[$version] : $version;
    }

    /**
     * @TODO: ?? Ca fait plein de choses
     *
     * @param $input
     *
     * @return array
     * @throws \Exception
     */
    public static function toHumanReadable($input)
    {
        return ScenarioManager::toHumanReadable(EqLogicManager::toHumanReadable(CmdManager::cmdToHumanReadable($input)));
    }

    /**
     * @TODO: ?? Ca aussi ça fait plein de choses
     *
     * @param $input
     *
     * @return array|mixed(
     * @throws \Exception
     */
    public static function fromHumanReadable($input)
    {
        return ScenarioManager::fromHumanReadable(EqLogicManager::fromHumanReadable(CmdManager::humanReadableToCmd($input)));
    }

    /**
     * @TODO: Ca évalue des choses dans les expressions
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
            $result = Utils::evaluate($input);
            if (is_bool($result) || is_numeric($result)) {
                return $result;
            }
            return $input;
        } catch (\Exception $exc) {
            return $input;
        }
    }

    /**
     * @TODO: Calcul les stats de quelquechose
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
     * Get type of entity in string
     *
     * @param string $testString
     *
     * @return array
     * @throws \Exception
     */
    public static function getTypeUse($testString = '')
    {
        $results = [NextDomObj::CMD => [], NextDomObj::SCENARIO => [], NextDomObj::EQLOGIC => [], NextDomObj::DATASTORE => [], NextDomObj::PLAN => [], NextDomObj::VIEW => []];
        // Look for human readable strings
        preg_match_all('/#(eqLogic|scenario)?(\d+)#/', $testString, $humanReadableResults);
        self::addTypeUseResults($humanReadableResults, $results);
        // Look in json string
        preg_match_all('/"(scenario|view|plan|eqLogic)(?:_id)?":"(\d+)"/', $testString, $jsonResults);
        self::addTypeUseResults($jsonResults, $results);
        preg_match_all('/variable\((.*?)\)/', $testString, $dataStoreResults);
        foreach ($dataStoreResults[1] as $variable) {
            if (isset($results[NextDomObj::DATASTORE][$variable])) {
                continue;
            }
            $dataStore = DataStoreManager::byTypeLinkIdKey(NextDomObj::SCENARIO, -1, trim($variable));
            if (!is_object($dataStore)) {
                continue;
            }
            $results[NextDomObj::DATASTORE][$variable] = $dataStore;
        }

        return $results;
    }

    /**
     * @param $matches
     * @param $result
     * @throws \Exception
     */
    private static function addTypeUseResults($matches, &$result)
    {
        for ($matchIndex = 0; $matchIndex < count($matches[0]); ++$matchIndex) {
            $typeName = $matches[1][$matchIndex];
            if (empty($typeName)) {
                $typeName = NextDomObj::CMD;
            }
            $typeId = $matches[2][$matchIndex];
            $target = null;
            if (isset($result[$typeName][$typeId])) {
                continue;
            }
            switch ($typeName[0]) {
                case 'c':
                    $target = CmdManager::byId($typeId);
                    break;
                case 'e':
                    $target = EqLogicManager::byId($typeId);
                    break;
                case 's':
                    $target = ScenarioManager::byId($typeId);
                    break;
                case 'p':
                    $target = PlanHeaderManager::byId($typeId);
                    break;
                case 'v':
                    $target = ViewManager::byId($typeId);
                    break;
                default:
                    $target = null;
                    break;
            }
            if (!is_object($target)) {
                continue;
            }
            $result[$typeName][$typeId] = $target;

        }
    }

    /**
     * @param string $testString
     * @return array
     * @throws \Exception
     */
    public static function getTypeUseOld($testString = '')
    {
        $result = [NextDomObj::CMD => [], NextDomObj::SCENARIO => [], NextDomObj::EQLOGIC => [], NextDomObj::DATASTORE => [], NextDomObj::PLAN => [], NextDomObj::VIEW => []];
        // Test commands human readable
        preg_match_all("/#([0-9]*)#/", $testString, $matches);
        foreach ($matches[1] as $cmdId) {
            if (isset($result['cmd'][$cmdId])) {
                continue;
            }
            $cmd = CmdManager::byId($cmdId);
            if (!is_object($cmd)) {
                continue;
            }
            $result[NextDomObj::CMD][$cmdId] = $cmd;
        }
        // Test scenarios from parameters
        preg_match_all('/"scenario_id":"([0-9]*)"/', $testString, $matches);
        foreach ($matches[1] as $scenario_id) {
            if (isset($result['scenario'][$scenario_id])) {
                continue;
            }
            $scenario = ScenarioManager::byId($scenario_id);
            if (!is_object($scenario)) {
                continue;
            }
            $result[NextDomObj::SCENARIO][$scenario_id] = $scenario;
        }
        // Test scenario human readable
        preg_match_all("/#scenario([0-9]*)#/", $testString, $matches);
        foreach ($matches[1] as $scenario_id) {
            if (isset($result[NextDomObj::SCENARIO][$scenario_id])) {
                continue;
            }
            $scenario = ScenarioManager::byId($scenario_id);
            if (!is_object($scenario)) {
                continue;
            }
            $result[NextDomObj::SCENARIO][$scenario_id] = $scenario;
        }
        // Test eqLogic human readable
        preg_match_all("/#eqLogic([0-9]*)#/", $testString, $matches);
        foreach ($matches[1] as $eqLogicId) {
            if (isset($result[NextDomObj::EQLOGIC][$eqLogicId])) {
                continue;
            }
            $eqLogic = EqLogicManager::byId($eqLogicId);
            if (!is_object($eqLogic)) {
                continue;
            }
            $result[NextDomObj::EQLOGIC][$eqLogicId] = $eqLogic;
        }
        // Test eqLogic from parameters
        preg_match_all('/"eqLogic":"([0-9]*)"/', $testString, $matches);
        foreach ($matches[1] as $eqLogicId) {
            if (isset($result[NextDomObj::EQLOGIC][$eqLogicId])) {
                continue;
            }
            $eqLogic = EqLogicManager::byId($eqLogicId);
            if (!is_object($eqLogic)) {
                continue;
            }
            $result[NextDomObj::EQLOGIC][$eqLogicId] = $eqLogic;
        }
        // Test variable
        preg_match_all('/variable\((.*?)\)/', $testString, $matches);
        foreach ($matches[1] as $variable) {
            if (isset($result[NextDomObj::DATASTORE][$variable])) {
                continue;
            }
            $dataStore = DataStoreManager::byTypeLinkIdKey('scenario', -1, trim($variable));
            if (!is_object($dataStore)) {
                continue;
            }
            $result[NextDomObj::DATASTORE][$variable] = $dataStore;
        }
        // Test view id from parameters
        preg_match_all('/"view_id":"([0-9]*)"/', $testString, $matches);
        foreach ($matches[1] as $viewId) {
            if (isset($result[NextDomObj::VIEW][$viewId])) {
                continue;
            }
            $view = ViewManager::byId($viewId);
            if (!is_object($view)) {
                continue;
            }
            $result[NextDomObj::VIEW][$viewId] = $view;
        }
        // Test plan_id from parameters
        preg_match_all('/"plan_id":"([0-9]*)"/', $testString, $matches);
        foreach ($matches[1] as $planId) {
            if (isset($result[NextDomObj::PLAN][$planId])) {
                continue;
            }
            $plan = PlanHeaderManager::byId($planId);
            if (!is_object($plan)) {
                continue;
            }
            $result[NextDomObj::PLAN][$planId] = $plan;
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
     * @TODO: ???
     *
     * @param string $command Command to execute after stop preparation
     * @param string $errorMessage Message to show if actions failed
     *
     * @throws CoreException
     */
    private static function stopSystemAndExecuteCommand($command, $errorMessage)
    {
        PluginManager::stop();
        CacheManager::persist();
        if (self::isCapable('sudo')) {
            exec(SystemHelper::getCmdSudo() . $command);
        } else {
            throw new CoreException($errorMessage);
        }
    }

    /**
     * Reboot the system
     */
    public static function rebootSystem()
    {
        self::stopSystemAndExecuteCommand('reboot', __('Vous pouvez lancer le redémarrage du système'));
    }

    /**
     * Get hardware key
     *
     * @return bool|string
     * @throws \Exception
     */
    public static function getHardwareKey()
    {
        $result = ConfigManager::byKey(ConfigKey::NEXTDOM_INSTALL_KEY);
        if ($result == '') {
            $result = substr(Utils::sha512(microtime() . ConfigManager::genKey()), 0, 63);
            ConfigManager::save(ConfigKey::NEXTDOM_INSTALL_KEY, $result);
        }
        return $result;
    }

    /**
     * Benchmark cache
     *
     * @return array
     * @throws \Exception
     */
    public static function benchmark()
    {
        $result = [];

        $param = ['cache_write' => 5000, 'cache_read' => 5000, 'database_write_delete' => 1000, 'database_update' => 1000, 'database_replace' => 1000, 'database_read' => 50000, 'subprocess' => 200];

        $starttime = Utils::getMicrotime();
        for ($i = 0; $i < $param['cache_write']; $i++) {
            CacheManager::set('nextdom_benchmark', $i);
        }
        $result['cache_write_' . $param['cache_write']] = Utils::getMicrotime() - $starttime;

        $starttime = Utils::getMicrotime();
        for ($i = 0; $i < $param['cache_read']; $i++) {
            $cache = CacheManager::byKey('nextdom_benchmark');
            $cache->getValue();
        }
        $result['cache_read_' . $param['cache_read']] = Utils::getMicrotime() - $starttime;

        $starttime = Utils::getMicrotime();
        for ($i = 0; $i < $param['database_write_delete']; $i++) {
            $sql = 'DELETE FROM config
                    WHERE `key`="nextdom_benchmark"
                    AND plugin="core"';
            try {
                DBHelper::exec($sql);
            } catch (\Exception $e) {

            }
            $sql = 'INSERT INTO config
                    SET `key`="nextdom_benchmark",plugin="core",`value`="' . $i . '"';
            try {
                DBHelper::exec($sql);
            } catch (\Exception $e) {

            }
        }
        $result['database_write_delete_' . $param['database_write_delete']] = Utils::getMicrotime() - $starttime;

        $sql = 'INSERT INTO config
                SET `key`="nextdom_benchmark",plugin="core",`value`="0"';
        try {
            DBHelper::exec($sql);
        } catch (\Exception $e) {
        }
        $starttime = Utils::getMicrotime();
        for ($i = 0; $i < $param['database_update']; $i++) {
            $sql = 'UPDATE `config`
                    SET `value`=:value
                    WHERE `key` = "nextdom_benchmark"
                        AND plugin = "core"';
            try {
                DBHelper::exec($sql, ['value' => $i]);
            } catch (\Exception $e) {

            }
        }
        $result['database_update_' . $param['database_update']] = Utils::getMicrotime() - $starttime;

        $starttime = Utils::getMicrotime();
        for ($i = 0; $i < $param['database_replace']; $i++) {
            ConfigManager::save('nextdom_benchmark', $i);
        }
        $result['database_replace_' . $param['database_replace']] = Utils::getMicrotime() - $starttime;

        $starttime = Utils::getMicrotime();
        for ($i = 0; $i < $param['database_read']; $i++) {
            ConfigManager::byKey('nextdom_benchmark');
        }
        $result['database_read_' . $param['database_read']] = Utils::getMicrotime() - $starttime;

        $starttime = Utils::getMicrotime();
        for ($i = 0; $i < $param['subprocess']; $i++) {
            shell_exec('echo ' . $i);
        }
        $result['subprocess_' . $param['subprocess']] = Utils::getMicrotime() - $starttime;

        $total = 0;
        foreach ($result as $value) {
            $total += $value;
        }
        $result['total'] = $total;
        return $result;
    }
}
