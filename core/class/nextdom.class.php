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


/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once __DIR__ . '/../../core/php/core.inc.php';

use NextDom\Managers\ScenarioManager;

class nextdom
{
    /**
     * @var TODO: ??
     */
    private static $nextdomConfiguration;

    /**
     * TODO: ???
     *
     * @param $event
     */
    public static function addTimelineEvent($event)
    {
        file_put_contents(__DIR__ . '/../../data/timeline.json', json_encode($event) . "\n", FILE_APPEND);
    }

    /**
     * TODO; ???
     *
     * @return array
     */
    public static function getTimelineEvent(): array
    {
        $path = __DIR__ . '/../../data/timeline.json';
        if (!file_exists($path)) {
            $result = array();
        } else {
            // TODO: CHMOD 777
            \com_shell::execute(\system::getCmdSudo() . 'chmod 777 ' . $path . ' > /dev/null 2>&1;echo "$(tail -n ' . \config::byKey('timeline::maxevent') . ' ' . $path . ')" > ' . $path);
            $lines = explode("\n", trim(file_get_contents($path)));
            $result = array();
            foreach ($lines as $line) {
                $result[] = json_decode($line, true);
            }
        }
        return $result;
    }

    /**
     * TODO: ???
     */
    public static function removeTimelineEvent()
    {
        $path = __DIR__ . '/../../data/timeline.json';
        // TODO: chmod 777
        \com_shell::execute(\system::getCmdSudo() . 'chmod 777 ' . $path . ' > /dev/null 2>&1;');
        unlink($path);
    }

    /**
     * TODO ???
     * @param $data
     */
    public static function addRemoveHistory($data)
    {
        try {
            $remove_history = array();
            if (file_exists(NEXTDOM_ROOT . '/data/remove_history.json')) {
                $remove_history = json_decode(file_get_contents(NEXTDOM_ROOT . '/data/remove_history.json'), true);
            }
            $remove_history[] = $data;
            $remove_history = array_slice($remove_history, -200, 200);
            file_put_contents(NEXTDOM_ROOT . '/data/remove_history.json', json_encode($remove_history));
        } catch (Exception $e) {
        }
    }

    /**
     * TODO: ???
     * @return array
     */
    public static function deadCmd()
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        $return = array();
        $cmd = \config::byKey('interact::warnme::defaultreturncmd', 'core', '');
        if ($cmd != '') {
            if (!\cmd::byId(str_replace('#', '', $cmd))) {
                $return[] = array('detail' => 'Administration', 'help' => __('Commande retour interactions'), 'who' => $cmd);
            }
        }
        $cmd = \config::byKey('emailAdmin', 'core', '');
        if ($cmd != '') {
            if (!\cmd::byId(str_replace('#', '', $cmd))) {
                $return[] = array('detail' => 'Administration', 'help' => __('Commande information utilisateur'), 'who' => $cmd);
            }
        }
        foreach ($NEXTDOM_INTERNAL_CONFIG['alerts'] as $level => $value) {
            $cmds = \config::byKey('alert::' . $level . 'Cmd', 'core', '');
            preg_match_all("/#([0-9]*)#/", $cmds, $matches);
            foreach ($matches[1] as $cmd_id) {
                if (!cmd::byId($cmd_id)) {
                    $return[] = array('detail' => 'Administration', 'help' => __('Commande sur ') . $value['name'], 'who' => '#' . $cmd_id . '#');
                }
            }
        }
        return $return;
    }

    /**
     * Obtenir l'état du système
     *
     * @return array Informations sur l'état du système
     *
     * @throws Exception
     */
    public static function health(): array
    {
        $okStr = __('str.OK');
        $nokStr = __('str.NOK');

        $systemHealth = array();
        $nbNeededUpdate = \update::nbNeedUpdate();
        $state = ($nbNeededUpdate == 0) ? true : false;
        $systemHealth[] = array(
            'name' => __('health.update-to-date'),
            'state' => $state,
            'result' => ($state) ? $okStr : $nbNeededUpdate,
            'comment' => '',
        );

        $state = (\config::byKey('enableCron', 'core', 1, true) != 0) ? true : false;
        $systemHealth[] = array(
            'name' => __('health.cron-enabled'),
            'state' => $state,
            'result' => ($state) ? $okStr : $nokStr,
            'comment' => ($state) ? '' : __('health.cron-disabled'),
        );

        $state = (\config::byKey('enableScenario') == 0 && count(ScenarioManager::all()) > 0) ? false : true;
        $systemHealth[] = array(
            'name' => __('health.scenario-enabled'),
            'state' => $state,
            'result' => ($state) ? $okStr : $nokStr,
            'comment' => ($state) ? '' : __('health.scenario-disabled'),
        );

        $state = self::isStarted();
        $systemHealth[] = array(
            'name' => __('health.NextDom-started'),
            'state' => $state,
            'result' => ($state) ? $okStr . ' ' . file_get_contents(self::getTmpFolder() . '/started') : $nokStr,
            'comment' => '',
        );

        $state = self::isDateOk();
        $cache = \cache::byKey('hour');
        $lastKnowDate = $cache->getValue();
        $systemHealth[] = array(
            'name' => __('health.system-date'),
            'state' => $state,
            'result' => ($state) ? $okStr . ' ' . date('Y-m-d H:i:s') . ' (' . $lastKnowDate . ')' : date('Y-m-d H:i:s'),
            'comment' => ($state) ? '' : __('health.system-date-error'),
        );

        $state = self::isCapable('sudo', true);
        $systemHealth[] = array(
            'name' => __('health.sudo-rights'),
            'state' => ($state) ? 1 : 2,
            'result' => ($state) ? $okStr : $nokStr,
            'comment' => ($state) ? '' : __('sudo-error'),
        );

        $systemHealth[] = array(
            'name' => __('health.NextDom-version'),
            'state' => true,
            'result' => self::version(),
            'comment' => '',
        );

        $state = version_compare(phpversion(), '5.5', '>=');
        $systemHealth[] = array(
            'name' => __('health.php-version'),
            'state' => $state,
            'result' => phpversion(),
            'comment' => ($state) ? '' : __('health.php-error'),
        );

        $state = true;
        $version = '';
        $uname = shell_exec('uname -a');
        if (\system::getDistrib() != 'debian') {
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
            'name' => __('os-version'),
            'state' => $state,
            'result' => ($state) ? $uname . ' [' . $version . ']' : $uname,
            'comment' => ($state) ? '' : __('Vous n\'êtes pas sur un OS officiellement supporté par l\'équipe NextDom (toute demande de support pourra donc être refusée). Les OS officiellement supporté sont Debian Jessie et Debian Strech (voir <a href="https://jeedom.github.io/documentation/compatibility/fr_FR/index" target="_blank">ici</a>)'),
        );

        $version = \DB::Prepare('select version()', array(), \DB::FETCH_TYPE_ROW);
        $systemHealth[] = array(
            'name' => __('health.database-version'),
            'state' => true,
            'result' => $version['version()'],
            'comment' => '',
        );

        $value = self::checkSpaceLeft();
        $systemHealth[] = array(
            'name' => __('health.harddisk-freespace'),
            'state' => ($value > 10),
            'result' => $value . ' %',
            'comment' => '',
        );

        $values = getSystemMemInfo();
        $value = round(($values['MemAvailable'] / $values['MemTotal']) * 100);
        $systemHealth[] = array(
            'name' => __('Mémoire disponible'),
            'state' => ($value > 15),
            'result' => $value . ' %',
            'comment' => '',
        );

        $value = shell_exec('sudo dmesg | grep oom | wc -l');
        $systemHealth[] = array(
            'name' => __('health.enough-memory'),
            'state' => ($value == 0),
            'result' => $value,
            'comment' => ($value == 0) ? '' : __('health.processes-killed'),
        );

        if ($values['SwapTotal'] != 0 && $values['SwapTotal'] !== null) {
            $value = round(($values['SwapFree'] / $values['SwapTotal']) * 100);
            $systemHealth[] = array(
                'name' => __('health.available-swap'),
                'state' => ($value > 15),
                'result' => $value . ' %',
                'comment' => '',
            );
        } else {
            $systemHealth[] = array(
                'name' => __('health.available-swap'),
                'state' => 2,
                'result' => __('health.unknow'),
                'comment' => '',
            );
        }

        $values = sys_getloadavg();
        $systemHealth[] = array(
            'name' => __('health.load'),
            'state' => ($values[2] < 20),
            'result' => $values[0] . ' - ' . $values[1] . ' - ' . $values[2],
            'comment' => '',
        );

        $state = \network::test('internal');
        $systemHealth[] = array(
            'name' => __('health.internal-network-conf'),
            'state' => $state,
            'result' => ($state) ? $okStr : $nokStr,
            'comment' => ($state) ? '' : __('health.network-config'),
        );

        $state = \network::test('external');
        $systemHealth[] = array(
            'name' => __('health.external-network-conf'),
            'state' => $state,
            'result' => ($state) ? $okStr : $nokStr,
            'comment' => ($state) ? '' : __('health.network-config'),
        );

        $cache_health = array('comment' => '', 'name' => __('health.cache-persistence'));
        if (cache::isPersistOk()) {
            if (\config::byKey('cache::engine') != 'FilesystemCache' && \config::byKey('cache::engine') != 'PhpFileCache') {
                $cache_health['state'] = true;
                $cache_health['result'] = $okStr;
            } else {
                $filename = __DIR__ . '/../../cache.tar.gz';
                $cache_health['state'] = true;
                $cache_health['result'] = $okStr . ' (' . date('Y-m-d H:i:s', filemtime($filename)) . ')';
            }
        } else {
            $cache_health['state'] = false;
            $cache_health['result'] = $nokStr;
            $cache_health['comment'] = __('health.cache-not-saved');
            $state = \network::test('external');
        }
        $systemHealth[] = $cache_health;

        $state = shell_exec('systemctl show apache2 | grep  PrivateTmp | grep yes | wc -l');
        $systemHealth[] = array(
            'name' => __('health.apache-private-tmp'),
            'state' => $state,
            'result' => ($state) ? $okStr : $nokStr,
            'comment' => ($state) ? '' : __('health.apache-private-tmp-disabled'),
        );

        foreach (update::listRepo() as $repo) {
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
     * TODO: ????
     */
    public static function sick()
    {
        $cmd = __DIR__ . '/../../sick.php';
        $cmd .= ' >> ' . \log::getPathToLog('sick') . ' 2>&1';
        \system::php($cmd);
    }

    /**
     * Obtenir la clé API de Jeedom ou d'un plugin
     *
     * @param string $plugin Code du plugin ou core par défaut
     *
     * @return string Clé de l'API
     */
    public static function getApiKey(string $plugin = 'core'): string
    {
        if ($plugin == 'apipro') {
            if (\config::byKey('apipro') == '') {
                \config::save('apipro', \config::genKey());
            }
            return \config::byKey('apipro');
        }
        if ($plugin == 'apimarket') {
            if (config::byKey('apimarket') == '') {
                config::save('apimarket', config::genKey());
            }
            return config::byKey('apimarket');
        }
        if (\config::byKey('api', $plugin) == '') {
            \config::save('api', \config::genKey(), $plugin);
        }
        return \config::byKey('api', $plugin);
    }

    /**
     * TODO: ???
     *
     * @param string $mode
     *
     * @return bool
     */
    public static function apiModeResult(string $mode = 'enable'): bool
    {
        $result = true;
        switch ($mode) {
            case 'disable':
                $result = false;
                break;
            case 'whiteip':
                $ip = getClientIp();
                $find = false;
                $whiteIps = explode(';', \config::byKey('security::whiteips'));
                if (\config::byKey('security::whiteips') != '' && count($whiteIps) > 0) {
                    foreach ($whiteIps as $whiteip) {
                        if (netMatch($whiteip, $ip)) {
                            $find = true;
                        }
                    }
                    if (!$find) {
                        $result = false;
                    }
                }
                break;
            case 'localhost':
                if (getClientIp() != '127.0.0.1') {
                    $result = false;
                }
                break;
        }
        return $result;
    }

    /**
     * TODO:
     *
     * @param string $defaultApiKey
     * @param string $plugin
     * @return bool
     */
    public static function apiAccess(string $defaultApiKey = '', string $plugin = 'core')
    {
        $defaultApiKey = trim($defaultApiKey);
        if ($defaultApiKey == '') {
            return false;
        }
        if ($plugin != 'core' && $plugin != 'proapi' && !self::apiModeResult(\config::byKey('api::' . $plugin . '::mode', 'core', 'enable'))) {
            return false;
        }
        $apikey = self::getApiKey($plugin);
        if ($defaultApiKey != '' && $apikey == $defaultApiKey) {
            return true;
        }
        $user = \user::byHash($defaultApiKey);
        if (is_object($user)) {
            if ($user->getOptions('localOnly', 0) == 1 && !self::apiModeResult('whiteip')) {
                return false;
            }
            GLOBAL $_USER_GLOBAL;
            $_USER_GLOBAL = $user;
            \log::add('connection', 'info', __('core.api-connection') . $user->getLogin());
            return true;
        }
        return false;
    }

    /**
     * TODO: isOk ????
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
     * Obtenir la liste des périphériques USB
     *
     * @param string $name
     * @param bool $getGPIO
     *
     * @return array|mixed|string
     */
    public static function getUsbMapping($name = '', $getGPIO = false)
    {
        $cache = \cache::byKey('nextdom::usbMapping');
        if (!is_json($cache->getValue()) || $name == '') {
            $usbMapping = array();
            foreach (\ls('/dev/', 'ttyUSB*') as $usb) {
                $vendor = '';
                $model = '';
                $devsList = shell_exec('/sbin/udevadm info --name=/dev/' . $usb . ' --query=all');
                foreach (explode("\n", $devsList) as $line) {
                    if (strpos($line, 'E: ID_MODEL=') !== false) {
                        $model = trim(str_replace(array('E: ID_MODEL=', '"'), '', $line));
                    }
                    if (strpos($line, 'E: ID_VENDOR=') !== false) {
                        $vendor = trim(str_replace(array('E: ID_VENDOR=', '"'), '', $line));
                    }
                }
                if ($vendor == '' && $model == '') {
                    $usbMapping['/dev/' . $usb] = '/dev/' . $usb;
                } else {
                    $deviceName = trim($vendor . ' ' . $model);
                    $number = 2;
                    while (isset($usbMapping[$deviceName])) {
                        $deviceName = trim($vendor . ' ' . $model . ' ' . $number);
                        $number++;
                    }
                    $usbMapping[$deviceName] = '/dev/' . $usb;
                }
            }
            if ($getGPIO) {
                if (file_exists('/dev/ttyAMA0')) {
                    $usbMapping['Raspberry pi'] = '/dev/ttyAMA0';
                }
                if (file_exists('/dev/ttymxc0')) {
                    $usbMapping['NextDom board'] = '/dev/ttymxc0';
                }
                if (file_exists('/dev/S2')) {
                    $usbMapping['Banana PI'] = '/dev/S2';
                }
                if (file_exists('/dev/ttyS2')) {
                    $usbMapping['Banana PI (2)'] = '/dev/ttyS2';
                }
                if (file_exists('/dev/ttyS0')) {
                    $usbMapping['Cubiboard'] = '/dev/ttyS0';
                }
                if (file_exists('/dev/ttyS3')) {
                    $usbMapping['Orange PI'] = '/dev/ttyS3';
                }
                if (file_exists('/dev/ttyS1')) {
                    $usbMapping['Odroid C2'] = '/dev/ttyS1';
                }
                foreach (ls('/dev/', 'ttyACM*') as $value) {
                    $usbMapping['/dev/' . $value] = '/dev/' . $value;
                }
            }
            \cache::set('nextdom::usbMapping', json_encode($usbMapping));
        } else {
            $usbMapping = json_decode($cache->getValue(), true);
        }
        if ($name != '') {
            if (isset($usbMapping[$name])) {
                return $usbMapping[$name];
            }
            $usbMapping = self::getUsbMapping('', true);
            if (isset($usbMapping[$name])) {
                return $usbMapping[$name];
            }
            if (file_exists($name)) {
                return $name;
            }
            return '';
        }
        return $usbMapping;
    }

    /**
     * Obtenir la liste des périphériques Bluetooth
     *
     * @param string $name
     * @return array|mixed|string
     */
    public static function getBluetoothMapping($name = '')
    {
        $cache = \cache::byKey('nextdom::bluetoothMapping');
        if (!is_json($cache->getValue()) || $name == '') {
            $bluetoothMapping = array();
            foreach (explode("\n", shell_exec('hcitool dev')) as $line) {
                if (strpos($line, 'hci') === false || trim($line) == '') {
                    continue;
                }
                $infos = explode("\t", $line);
                $bluetoothMapping[$infos[2]] = $infos[1];
            }
            \cache::set('nextdom::bluetoothMapping', json_encode($bluetoothMapping));
        } else {
            $bluetoothMapping = json_decode($cache->getValue(), true);
        }
        if ($name != '') {
            if (isset($bluetoothMapping[$name])) {
                return $bluetoothMapping[$name];
            }
            $bluetoothMapping = self::getBluetoothMapping('', true);
            if (isset($bluetoothMapping[$name])) {
                return $bluetoothMapping[$name];
            }
            if (file_exists($name)) {
                return $name;
            }
            return '';
        }
        return $bluetoothMapping;
    }

    /**
     * Lancer une sauvegarde du système
     *
     * @param bool $taskInBackground Lancer la sauvegarde en tâche de fond.
     */
    public static function backup(bool $taskInBackground = false)
    {
        if ($taskInBackground) {
            \log::clear('backup');
            $cmd = NEXTDOM_ROOT . '/install/backup.php';
            $cmd .= ' >> ' . \log::getPathToLog('backup') . ' 2>&1 &';
            \system::php($cmd, true);
        } else {
            require_once NEXTDOM_ROOT . '/install/backup.php';
        }
    }

    /**
     * Obtenir la liste des sauvegardes
     *
     * @return array Liste des sauvegardes
     */
    public static function listBackup(): array
    {
        if (substr(\config::byKey('backup::path'), 0, 1) != '/') {
            $backup_dir = NEXTDOM_ROOT . '/' . \config::byKey('backup::path');
        } else {
            $backup_dir = \config::byKey('backup::path');
        }
        $backups = \ls($backup_dir, '*.tar.gz', false, array('files', 'quiet', 'datetime_asc'));
        $return = array();
        foreach ($backups as $backup) {
            $return[$backup_dir . '/' . $backup] = $backup;
        }
        return $return;
    }

    /**
     * Supprimer une sauvegarde
     *
     * @param string $backupFilePath Chemin du fichier de sauvegarde
     *
     * @throws Exception
     */
    public static function removeBackup(string $backupFilePath)
    {
        if (file_exists($backupFilePath)) {
            unlink($backupFilePath);
        } else {
            throw new Exception('Impossible de trouver le fichier : ' . $backupFilePath);
        }
    }

    /**
     * Restaure une sauvegarde
     *
     * @param string $backupFilePath Chemin de la sauvegarde
     *
     * @param bool $taskInBackground Lancer en tâche de fond
     */
    public static function restore(string $backupFilePath = '', bool $taskInBackground = false)
    {
        if ($taskInBackground) {
            \log::clear('restore');
            $cmd = NEXTDOM_ROOT . '/install/restore.php "backup=' . $backupFilePath . '"';
            $cmd .= ' >> ' . \log::getPathToLog('restore') . ' 2>&1 &';
            \system::php($cmd, true);
        } else {
            global $BACKUP_FILE;
            $BACKUP_FILE = $backupFilePath;
            require_once NEXTDOM_ROOT . '/install/restore.php';
        }
    }

    public static function migrate(string $backupFilePath = '', bool $taskInBackground = false)
    {
        if ($taskInBackground) {
            \log::clear('migrate');
            $cmd = NEXTDOM_ROOT . '/install/migrate_jeedom_to_nextdom.php "backup=' . $backupFilePath . '"';
            $cmd .= ' >> ' . \log::getPathToLog('migrate') . ' 2>&1 &';
            \system::php($cmd, true);
            \system::php(NEXTDOM_ROOT.'/todo.php');
        } else {
            global $BACKUP_FILE;
            $BACKUP_FILE = $backupFilePath;
            require_once NEXTDOM_ROOT . '/install/migrate_jeedom_to_nextdom.php';
        }
    }

    /**
     * Lance une mise à jour
     *
     * @param array $options Liste des options
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
        \system::php($cmd);
    }

    /**
     * Obtenir la \configuration TODO ???
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
        self::$nextdomConfiguration[$askedKey] = self::checkValueInconfiguration($askedKey, $result);

        return self::$nextdomConfiguration[$askedKey];
    }

    /**
     * TODO ???
     * @param $_key
     * @param $_value
     * @return array|mixed|string
     */
    private static function checkValueInconfiguration($_key, $_value)
    {
        if (!is_array(self::$nextdomConfiguration)) {
            self::$nextdomConfiguration = array();
        }
        if (isset(self::$nextdomConfiguration[$_key])) {
            return self::$nextdomConfiguration[$_key];
        }
        if (is_array($_value)) {
            foreach ($_value as $key => $value) {
                $_value[$key] = self::checkValueInconfiguration($_key . ':' . $key, $value);
            }
            self::$nextdomConfiguration[$_key] = $_value;
            return $_value;
        } else {
            $config = \config::byKey($_key);
            return ($config == '') ? $_value : $config;
        }
    }

    /**
     * Obtenir la version de NextDom
     *
     * @return string
     */
    public static function version()
    {
        if (file_exists(NEXTDOM_ROOT . '/core/config/version')) {
            return trim(file_get_contents(NEXTDOM_ROOT . '/core/config/version'));
        }
        return '';
    }

    /**
     * Arrêter toutes les tâches cron et les scénarios
     */
    public static function stop()
    {
        $okStr = __('str.OK');
        echo __('core.disable-tasks');
        \config::save('enableCron', 0);
        foreach (\cron::all() as $cron) {
            if ($cron->running()) {
                try {
                    $cron->halt();
                    echo '.';
                } catch (\Exception $e) {
                    sleep(5);
                    $cron->halt();
                } catch (\Error $e) {
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
            \system::kill($pid);
            echo " $okStr\n";
        }

        /*         * *********Arrêt des scénarios**************** */

        echo __('core.disable-all-scenarios');
        \config::save('enableScenario', 0);
        foreach (ScenarioManager::all() as $scenario) {
            try {
                $scenario->stop();
                echo '.';
            } catch (\Exception $e) {
                sleep(5);
                $scenario->stop();
            } catch (\Error $e) {
                sleep(5);
                $scenario->stop();
            }
        }
        echo " $okStr\n";
    }

    /**
     * Activer les tâches cron et les scénarios
     *
     * @throws Exception
     */
    public static function start()
    {
        $okStr = __('str.OK');

        try {
            echo __('core.enable-all-scenarios');
            \config::save('enableScenario', 1);
            echo " $okStr\n";
            echo __('core.enable-tasks');
            \config::save('enableCron', 1);
            echo " $okStr\n";
        } catch (\Exception $e) {
            if (!isset($_GET['mode']) || $_GET['mode'] != 'force') {
                throw $e;
            } else {
                echo '***ERROR*** ' . $e->getMessage();
            }
        } catch (\Error $e) {
            if (!isset($_GET['mode']) || $_GET['mode'] != 'force') {
                throw $e;
            } else {
                echo '***ERROR*** ' . $e->getMessage();
            }
        }
    }

    /**
     * Test si NextDom est démarré
     *
     * @return bool Etat de NextDom
     */
    public static function isStarted(): bool
    {
        return file_exists(self::getTmpFolder() . '/started');
    }

    /**
     * Test si la date est bonne
     *
     * @return boolean Etat de l'heure
     */
    public static function isDateOk()
    {
        if (\config::byKey('ignoreHourCheck') == 1) {
            return true;
        }
        $cache = \cache::byKey('hour');
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
     * Vérifier un évènement
     *
     * @param $event
     * @param bool $forceSyncMode
     */
    public static function event($event, $forceSyncMode = false)
    {
        ScenarioManager::check($event, $forceSyncMode);
    }

    /**
     * Tâche lancée toutes les 5 minutes
     */
    public static function cron5()
    {
        try {
            \network::cron5();
        } catch (\Exception $e) {
            \log::add('network', 'error', 'network::cron : ' . $e->getMessage());
        } catch (\Error $e) {
            \log::add('network', 'error', 'network::cron : ' . $e->getMessage());
        }
        try {
            foreach (\update::listRepo() as $name => $repo) {
                $class = 'repo_' . $name;
                if (class_exists($class) && method_exists($class, 'cron5') && \config::byKey($name . '::enable') == 1) {
                    $class::cron5();
                }
            }
        } catch (\Exception $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        } catch (\Error $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        }
        try {
            \eqLogic::checkAlive();
        } catch (\Exception $e) {

        } catch (\Error $e) {

        }
    }

    /**
     * Tâche lancée toutes les minutes
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
                        } catch (\Exception $e) {
                            \log::add('starting', 'error', __('Erreur sur l\'arrêt d\'une tâche cron : ') . \log::exception($e));
                        } catch (\Error $e) {
                            \log::add('starting', 'error', __('Erreur sur l\'arrêt d\'une tâche cron : ') . \log::exception($e));
                        }
                    }
                }
            } catch (\Exception $e) {
                \log::add('starting', 'error', __('Erreur sur l\'arrêt des tâches crons : ') . \log::exception($e));
            } catch (\Error $e) {
                \log::add('starting', 'error', __('Erreur sur l\'arrêt des tâches crons : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Restauration du cache'));
                \cache::restore();
            } catch (\Exception $e) {
                \log::add('starting', 'error', __('Erreur sur la restauration du \cache : ') . \log::exception($e));
            } catch (\Error $e) {
                \log::add('starting', 'error', __('Erreur sur la restauration du \cache : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Nettoyage du cache des péripheriques USB'));
                $cache = \cache::byKey('nextdom::usbMapping');
                $cache->remove();
            } catch (\Exception $e) {
                \log::add('starting', 'error', __('Erreur sur le nettoyage du \cache des péripheriques USB : ') . \log::exception($e));
            } catch (\Error $e) {
                \log::add('starting', 'error', __('Erreur sur le nettoyage du \cache des péripheriques USB : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Nettoyage du cache des péripheriques Bluetooth'));
                $cache = \cache::byKey('nextdom::bluetoothMapping');
                $cache->remove();
            } catch (\Exception $e) {
                \log::add('starting', 'error', __('Erreur sur le nettoyage du \cache des péripheriques Bluetooth : ') . \log::exception($e));
            } catch (\Error $e) {
                \log::add('starting', 'error', __('Erreur sur le nettoyage du \cache des péripheriques Bluetooth : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Démarrage des processus Internet de NextDom'));
                self::start();
            } catch (\Exception $e) {
                \log::add('starting', 'error', __('Erreur sur le démarrage interne de NextDom : ') . \log::exception($e));
            } catch (\Error $e) {
                \log::add('starting', 'error', __('Erreur sur le démarrage interne de NextDom : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Ecriture du fichier ') . self::getTmpFolder() . '/started');
                if (file_put_contents(self::getTmpFolder() . '/started', date('Y-m-d H:i:s')) === false) {
                    \log::add('starting', 'error', __('Impossible d\'écrire ' . self::getTmpFolder() . '/started'));
                }
            } catch (\Exception $e) {
                \log::add('starting', 'error', __('Impossible d\'écrire ' . self::getTmpFolder() . '/started : ') . \log::exception($e));
            } catch (\Error $e) {
                \log::add('starting', 'error', __('Impossible d\'écrire ' . self::getTmpFolder() . '/started : ') . \log::exception($e));
            }

            if (!file_exists(self::getTmpFolder() . '/started')) {
                \log::add('starting', 'critical', __('Impossible d\'écrire ' . self::getTmpFolder() . '/started pour une raison inconnue. NextDom ne peut démarrer'));
                return;
            }

            try {
                \log::add('starting', 'debug', __('Vérification de la \configuration réseau interne'));
                if (!network::test('internal')) {
                    \network::checkConf('internal');
                }
            } catch (\Exception $e) {
                \log::add('starting', 'error', __('Erreur sur la \configuration réseau interne : ') . \log::exception($e));
            } catch (\Error $e) {
                \log::add('starting', 'error', __('Erreur sur la \configuration réseau interne : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Envoi de l\'événement de démarrage'));
                self::event('start');
            } catch (\Exception $e) {
                \log::add('starting', 'error', __('Erreur sur l\'envoi de l\'événement de démarrage : ') . \log::exception($e));
            } catch (\Error $e) {
                \log::add('starting', 'error', __('Erreur sur l\'envoi de l\'événement de démarrage : ') . \log::exception($e));
            }

            try {
                \log::add('starting', 'debug', __('Démarrage des plugins'));
                plugin::start();
            } catch (\Exception $e) {
                \log::add('starting', 'error', __('Erreur sur le démarrage des plugins : ') . \log::exception($e));
            } catch (\Error $e) {
                \log::add('starting', 'error', __('Erreur sur la démarrage des plugins : ') . \log::exception($e));
            }

            try {
                if (\config::byKey('market::enable') == 1) {
                    \log::add('starting', 'debug', __('Test de connexion au market'));
                    repo_market::test();
                }
            } catch (\Exception $e) {
                \log::add('starting', 'error', __('Erreur sur la connexion au market : ') . \log::exception($e));
            } catch (\Error $e) {
                \log::add('starting', 'error', __('Erreur sur la connexion au market : ') . \log::exception($e));
            }
            \log::add('starting', 'debug', __('Démarrage de nextdom fini avec succès'));
            event::add('refresh');
        }
        self::isDateOk();
    }

    /**
     * Tâche exécutée tous les jours
     */
    public static function cronDaily()
    {
        try {
            ScenarioManager::cleanTable();
            ScenarioManager::consystencyCheck();
            \log::chunk();
            cron::clean();
            report::clean();
            \DB::optimize();
            \cache::clean();
        } catch (\Exception $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        } catch (\Error $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        }
    }

    /**
     * Tâche exécutée toutes les heures
     */
    public static function cronHourly()
    {
        try {
            \cache::set('hour', date('Y-m-d H:i:s'));
        } catch (\Exception $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        } catch (\Error $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        }
        try {
            if (\config::byKey('update::autocheck', 'core', 1) == 1 && (\config::byKey('update::lastCheck') == '' || (strtotime('now') - strtotime(\config::byKey('update::lastCheck'))) > (23 * 3600))) {
                \update::checkAllUpdate();
                $updates = \update::byStatus('update');
                if (count($updates) > 0) {
                    $toUpdate = '';
                    foreach ($updates as $update) {
                        $toUpdate .= $update->getLogicalId() . ',';
                    }
                }
                $updates = \update::byStatus('update');
                if (count($updates) > 0) {
                    message::add('update', __('De nouvelles mises à jour sont disponibles : ') . trim($toUpdate, ','), '', 'newUpdate');
                }
            }
        } catch (\Exception $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        } catch (\Error $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        }
        try {
            foreach (update::listRepo() as $name => $repo) {
                $class = 'repo_' . $name;
                if (class_exists($class) && method_exists($class, 'cronHourly') && \config::byKey($name . '::enable') == 1) {
                    $class::cronHourly();
                }
            }
        } catch (\Exception $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        } catch (\Error $e) {
            \log::add('nextdom', 'error', $e->getMessage());
        }
    }

    /**
     * TODO: ????
     * @param array $_replaces
     * @throws Exception
     */
    public static function replaceTag(array $_replaces)
    {
        $datas = array();
        foreach ($_replaces as $key => $value) {
            $datas = array_merge($datas, \cmd::searchConfiguration($key));
            $datas = array_merge($datas, \eqLogic::searchConfiguration($key));
            $datas = array_merge($datas, \object::searchConfiguration($key));
            $datas = array_merge($datas, ScenarioManager::searchByUse(array(array('action' => '#' . $key . '#'))));
            $datas = array_merge($datas, scenarioExpression::searchExpression($key, $key, false));
            $datas = array_merge($datas, scenarioExpression::searchExpression('variable(' . str_replace('#', '', $key) . ')'));
            $datas = array_merge($datas, scenarioExpression::searchExpression('variable', str_replace('#', '', $key), true));
        }
        if (count($datas) > 0) {
            foreach ($datas as $data) {
                \utils::a2o($data, json_decode(str_replace(array_keys($_replaces), $_replaces, json_encode(utils::o2a($data))), true));
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
     * TODO: ??
     *
     * @param $cmd
     *
     * @return string
     */
    public static function retrievePidThread(string $cmd): string
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
     *
     * @throws Exception
     */
    public static function toHumanReadable($input)
    {
        return ScenarioManager::toHumanReadable(eqLogic::toHumanReadable(cmd::cmdToHumanReadable($input)));
    }

    /**
     * TODO: ?? Ca aussi ça fait plein de choses
     *
     * @param $input
     *
     * @return array|mixed
     */
    public static function fromHumanReadable($input)
    {
        return ScenarioManager::fromHumanReadable(eqLogic::fromHumanReadable(cmd::humanReadableToCmd($input)));
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
            $input = scenarioExpression::setTags($input, $scenario, true);
            $result = evaluate($input);
            if (is_bool($result) || is_numeric($result)) {
                return $result;
            }
            return $input;
        } catch (\Exception $exc) {
            return $input;
        }
    }

    /**
     * TODO: Calcul les stats de quelquechose
     *
     * @param $calcul
     * @param $values
     *
     * @return float|int|null
     */
    public static function calculStat($calcul, $values)
    {
        switch ($calcul) {
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
     *
     * @throws Exception
     */
    public static function getTypeUse($_string = '')
    {
        $return = array('cmd' => array(), 'scenario' => array(), 'eqLogic' => array(), 'dataStore' => array(), 'plan' => array(), 'view' => array());
        preg_match_all("/#([0-9]*)#/", $_string, $matches);
        foreach ($matches[1] as $cmd_id) {
            if (isset($return['cmd'][$cmd_id])) {
                continue;
            }
            $cmd = cmd::byId($cmd_id);
            if (!is_object($cmd)) {
                continue;
            }
            $return['cmd'][$cmd_id] = $cmd;
        }
        preg_match_all('/"scenario_id":"([0-9]*)"/', $_string, $matches);
        foreach ($matches[1] as $scenario_id) {
            if (isset($return['scenario'][$scenario_id])) {
                continue;
            }
            $scenario = ScenarioManager::byId($scenario_id);
            if (!is_object($scenario)) {
                continue;
            }
            $return['scenario'][$scenario_id] = $scenario;
        }
        preg_match_all("/#scenario([0-9]*)#/", $_string, $matches);
        foreach ($matches[1] as $scenario_id) {
            if (isset($return['scenario'][$scenario_id])) {
                continue;
            }
            $scenario = ScenarioManager::byId($scenario_id);
            if (!is_object($scenario)) {
                continue;
            }
            $return['scenario'][$scenario_id] = $scenario;
        }
        preg_match_all("/#eqLogic([0-9]*)#/", $_string, $matches);
        foreach ($matches[1] as $eqLogic_id) {
            if (isset($return['eqLogic'][$eqLogic_id])) {
                continue;
            }
            $eqLogic = eqLogic::byId($eqLogic_id);
            if (!is_object($eqLogic)) {
                continue;
            }
            $return['eqLogic'][$eqLogic_id] = $eqLogic;
        }
        preg_match_all('/"eqLogic":"([0-9]*)"/', $_string, $matches);
        foreach ($matches[1] as $eqLogic_id) {
            if (isset($return['eqLogic'][$eqLogic_id])) {
                continue;
            }
            $eqLogic = eqLogic::byId($eqLogic_id);
            if (!is_object($eqLogic)) {
                continue;
            }
            $return['eqLogic'][$eqLogic_id] = $eqLogic;
        }
        preg_match_all('/variable\((.*?)\)/', $_string, $matches);
        foreach ($matches[1] as $variable) {
            if (isset($return['dataStore'][$variable])) {
                continue;
            }
            $dataStore = dataStore::byTypeLinkIdKey('scenario', -1, trim($variable));
            if (!is_object($dataStore)) {
                continue;
            }
            $return['dataStore'][$variable] = $dataStore;
        }
        preg_match_all('/"view_id":"([0-9]*)"/', $_string, $matches);
        foreach ($matches[1] as $view_id) {
            if (isset($return['view'][$view_id])) {
                continue;
            }
            $view = view::byId($view_id);
            if (!is_object($view)) {
                continue;
            }
            $return['view'][$view_id] = $view;
        }
        preg_match_all('/"plan_id":"([0-9]*)"/', $_string, $matches);
        foreach ($matches[1] as $plan_id) {
            if (isset($return['plan'][$plan_id])) {
                continue;
            }
            $plan = planHeader::byId($plan_id);
            if (!is_object($plan)) {
                continue;
            }
            $return['plan'][$plan_id] = $plan;
        }
        return $return;
    }

    /**
     * Eteint le système hôte
     *
     * @throws Exception
     */
    public static function haltSystem()
    {
        \plugin::stop();
        \cache::persist();
        if (self::isCapable('sudo')) {
            exec(\system::getCmdSudo() . 'shutdown -h now');
        } else {
            throw new Exception(__('Vous pouvez arrêter le système'));
        }
    }

    /**
     * Redémarre le système hôte
     *
     * @throws Exception
     */
    public static function rebootSystem()
    {
        plugin::stop();
        \cache::persist();
        if (self::isCapable('sudo')) {
            exec(\system::getCmdSudo() . 'reboot');
        } else {
            throw new Exception(__('Vous pouvez lancer le redémarrage du système'));
        }
    }

    /**
     * Force la synchronisation de l'heure
     */
    public static function forceSyncHour()
    {
        shell_exec(\system::getCmdSudo() . 'service ntp stop;' . \system::getCmdSudo() . 'ntpdate -s ' . \config::byKey('ntp::optionalServer', 'core', '0.debian.pool.ntp.org') . ';' . \system::getCmdSudo() . 'service ntp start');
    }

    /**
     * Nettoyer les droits des fichiers système
     */
    public static function cleanFileSytemRight()
    {
        $processUser = \system::get('www-uid');
        $processGroup = \system::get('www-gid');
        if ($processUser == '') {
            $processUser = posix_getpwuid(posix_geteuid());
            $processUser = $processUser['name'];
        }
        if ($processGroup == '') {
            $processGroup = posix_getgrgid(posix_getegid());
            $processGroup = $processGroup['name'];
        }
        $path = __DIR__ . '/../../*';
        exec(\system::getCmdSudo() . 'chown -R ' . $processUser . ':' . $processGroup . ' ' . $path . ';' . \system::getCmdSudo() . 'chmod 775 -R ' . $path);
    }

    /**
     * Vérifier l'espace disponible
     *
     * @return float
     */
    public static function checkSpaceLeft(): float
    {
        return round(disk_free_space(NEXTDOM_ROOT) / disk_total_space(NEXTDOM_ROOT) * 100);
    }

    /**
     * Obtenir le répertoire temporaire
     *
     * @param null $plugin
     *
     * @return string
     */
    public static function getTmpFolder($plugin = null)
    {
        $result = '/' . trim(\config::byKey('folder::tmp'), '/');
        if ($plugin !== null) {
            $result .= '/' . $plugin;
        }
        if (!file_exists($result)) {
            mkdir($result, 0777, true);
        }
        return $result;
    }

    /**
     * Obtenir une clé d'identifiant du système hôte
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
     * Obtenir le nom du système hôte
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
     * Test si NextDom est en capacité d'effectuer une action.
     *
     * @param string $systemFunc Fonction système à tester
     * @param bool $forceRefresh Forcer le rafraichissement
     *
     * @return bool
     */
    public static function isCapable($systemFunc, $forceRefresh = false)
    {
        global $NEXTDOM_COMPATIBILIY_CONFIG;
        if ($systemFunc == 'sudo') {
            if (!$forceRefresh) {
                $cache = \cache::byKey('nextdom::isCapable::sudo');
                if ($cache->getValue(0) == 1) {
                    return true;
                }
            }
            $result = (shell_exec('sudo -l > /dev/null 2>&1; echo $?') == 0) ? true : false;
            \cache::set('nextdom::isCapable::sudo', $result);
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
     * Evalue la performance
     *
     * @return array
     */
    public static function benchmark()
    {
        $result = array();

        $param = array('cache_write' => 5000, 'cache_read' => 5000, 'database_write_delete' => 1000, 'database_update' => 1000, 'database_replace' => 1000, 'database_read' => 50000, 'subprocess' => 200);

        $starttime = getmicrotime();
        for ($i = 0; $i < $param['cache_write']; $i++) {
            \cache::set('nextdom_benchmark', $i);
        }
        $result['cache_write_' . $param['cache_write']] = getmicrotime() - $starttime;

        $starttime = getmicrotime();
        for ($i = 0; $i < $param['cache_read']; $i++) {
            $cache = \cache::byKey('nextdom_benchmark');
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
            } catch (\Exception $e) {

            }
            $sql = 'INSERT INTO config
                    SET `key`="nextdom_benchmark",plugin="core",`value`="' . $i . '"';
            try {
                \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ROW);
            } catch (\Exception $e) {

            }
        }
        $result['database_write_delete_' . $param['database_write_delete']] = getmicrotime() - $starttime;

        $sql = 'INSERT INTO config
                SET `key`="nextdom_benchmark",plugin="core",`value`="0"';
        try {
            \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ROW);
        } catch (\Exception $e) {

        }
        $starttime = getmicrotime();
        for ($i = 0; $i < $param['database_update']; $i++) {
            $sql = 'UPDATE config
                    SET `value`=:value
                    WHERE `key`="nextdom_benchmark"
                        AND plugin="core"';
            try {
                \DB::Prepare($sql, array('value' => $i), \DB::FETCH_TYPE_ROW);
            } catch (\Exception $e) {

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
