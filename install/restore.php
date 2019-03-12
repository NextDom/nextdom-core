<?php
/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

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

use NextDom\Helpers\NextDomHelper;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\CacheManager;
use NextDom\Managers\PluginManager;
use NextDom\Helpers\Utils;

define('TMP_BACKUP', '/tmp/nextdombackup');

if (php_sapi_name() != 'cli' || isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['argc'])) {
    header("Statut: 404 Page non trouvée");
    header('HTTP/1.0 404 Not Found');
    $_SERVER['REDIRECT_STATUS'] = 404;
    echo "<h1>404 Non trouvé</h1>";
    echo "La page que vous demandez ne peut être trouvée.";
    exit();
}

echo  "[ START RESTORE / MIGRATION ]" . "\n";
$starttime = strtotime('now');
if (isset($argv)) {
    foreach ($argv as $arg) {
        $argList = explode('=', $arg);
        if (isset($argList[0]) && isset($argList[1])) {
            $_GET[$argList[0]] = $argList[1];
        }
    }
}

try {
    require_once __DIR__ . '/../core/php/core.inc.php';
    echo "*************** " . "Starting procedure at " . date('Y-m-d H:i:s') . " ***************\n";

    try {
        echo "Sends the start event of the restore/migration...";
        NextDomHelper::event('begin_restore', true);
        echo " OK" . "\n";
    } catch (\Exception $e) {
        echo " NOK" . "\n";
        echo '*** ' . "ERROR" . '*** ' . $e->getMessage() . "\n";
    }

    global $CONFIG;
    global $BACKUP_FILE;

    if (isset($BACKUP_FILE)) {
        $_GET['backup'] = $BACKUP_FILE;
    }

    $backup = Utils::init('backup');
    if ($backup == '') {
        $backupPath = ConfigManager::byKey('backup::path');
        if (substr($backupPath, 0, 1) != '/') {
            $fullBackupPath = NEXTDOM_ROOT . '/' . $backupPath;
        } else {
            $fullBackupPath = $backupPath;
        }
        if (!file_exists($fullBackupPath)) {
            mkdir($fullBackupPath);
        }
        $backup = null;
        $mtime = null;
        foreach (scandir($fullBackupPath) as $file) {
            if ($file != "." && $file != ".." && $file != ".htaccess" && strpos($file, '.tar.gz') !== false) {
                $s = stat($fullBackupPath . '/' . $file);
                if ($backup === null || $mtime === null) {
                    $backup = $fullBackupPath . '/' . $file;
                    $mtime = $s['mtime'];
                }
                if ($mtime < $s['mtime']) {
                    $backup = $fullBackupPath . '/' . $file;
                    $mtime = $s['mtime'];
                }
            }
        }
    }
    if (substr($backup, 0, 1) != '/') {
        $backup = NEXTDOM_ROOT . '/' . $backup;
    }
    if (!file_exists($backup)) {
        throw new \Exception("Backup file not found : " . $backup);
    }
    echo "File used for restoration : " . $backup . "\n";

    try {
        echo "Checking rights...";
        NextDomHelper::cleanFileSystemRight();
        echo " OK" . "\n";
    } catch (\Exception $e) {
        echo " NOK" . "\n";
        log::add('restore', 'error', $e->getMessage());
        echo '*** ' . "ERROR" . '*** ' . $e->getMessage() . "\n";
    }

    try {
        NextDomHelper::stopSystem();
    } catch (\Exception $e) {
        echo " NOK" . "\n";
        log::add('restore', 'error', $e->getMessage());
        echo '*** ' . "ERROR" . '*** ' . $e->getMessage() . "\n";
    }

    echo "Unzip the backup...";
    $excludes = array(
        'AlternativeMarketForJeedom',
        'musicast'
    );
    $exclude = '';
    foreach ($excludes as $folder) {
        $exclude .= ' --exclude="' . $folder . '"';
    }
    $rc = 0;
    system('mkdir -p '.TMP_BACKUP);
    system('cd '.TMP_BACKUP.'; rm * -rf; tar xfz "' . $backup . '" ' . $exclude);
    echo " OK" . "\n";

    echo "Delete the backup database...";
    if (!file_exists(TMP_BACKUP."/DB_backup.sql")) {
        throw new \Exception("Unable to find the backup database file : " . 'DB_backup.sql');
    } else {
        shell_exec("sed -i -e s/jeedom/nextdom/g ".TMP_BACKUP."/DB_backup.sql");
    }
    $tables = \DB::Prepare("SHOW TABLES", array(), \DB::FETCH_TYPE_ALL);
    echo " OK" . "\n";

    echo "Disables constraints...";
    \DB::Prepare("SET foreign_key_checks = 0", array(), \DB::FETCH_TYPE_ROW);
    echo " OK" . "\n";
    foreach ($tables as $table) {
        $table = array_values($table);
        $table = $table[0];
        echo "Delete the table : " . $table . '...';
        \DB::Prepare('DROP TABLE IF EXISTS `' . $table . '`', array(), \DB::FETCH_TYPE_ROW);
        echo " OK" . "\n";
    }

    echo "Restoring database...";
    shell_exec("mysql --host=" . $CONFIG['db']['host'] . " --port=" . $CONFIG['db']['port'] . " --user=" . $CONFIG['db']['username'] . " --password=" . $CONFIG['db']['password'] . " " . $CONFIG['db']['dbname'] . " < ".TMP_BACKUP."/DB_backup.sql");
    echo " OK" . "\n";

    echo "Updating database...";
    shell_exec('php ' . NEXTDOM_ROOT . '/install/migrate/migrate.php');
    echo " OK" . "\n";

    echo "Enables constraints...";
    try {
        \DB::Prepare("SET foreign_key_checks = 1", array(), \DB::FETCH_TYPE_ROW);
    } catch (\Exception $e) {

    }
    echo " OK" . "\n";

    if (file_exists(NEXTDOM_ROOT . '/core/config/jeedom.config.php')) {
        if (copy(NEXTDOM_ROOT . '/core/config/jeedom.config.php', '/tmp/nextdom.config.php')) {
            echo "Cannot copy " . NEXTDOM_ROOT . "/core/config/nextdom.config.php\n";
        }
    }
    if (!file_exists(NEXTDOM_ROOT . '/core/config/common.config.php')) {
        echo "Restoring database configuration file...";
        copy(TMP_BACKUP . '/common.config.php', NEXTDOM_ROOT . '/core/config/common.config.php');
        echo " OK" . "\n";
    }

    echo "Restoring rights...";
    system('chmod 1777 /tmp -R');
    echo " OK" . "\n";

    echo "Restoring cache...";
    if (file_exists(TMP_BACKUP . '/cache.tar.gz')) {
        system('cp -fr ' . TMP_BACKUP . '/cache.tar.gz ' . NEXTDOM_ROOT . '/var' );
    }
    elseif (file_exists(TMP_BACKUP . '/var/cache.tar.gz')) {
        system('cp -fr ' . TMP_BACKUP . '/var/cache.tar.gz ' . NEXTDOM_ROOT . '/var' );
    }
    try {
        CacheManager::restore();
    } catch (\Exception $e) {

    }
    echo " OK" . "\n";

    echo "Restoring plugins...";
    system('cp -fr ' . TMP_BACKUP . '/plugins/* ' . NEXTDOM_ROOT . '/plugins' );

    foreach (PluginManager::listPlugin(true) as $plugin) {
        $plugin_id = $plugin->getId();
        $dependancy_info = $plugin->getDependencyInfo(true);
        if (method_exists($plugin_id, 'restore')) {
            echo "Plugin restoration : " . $plugin_id . '...';
            $plugin_id::restore();
            echo " OK" . "\n";
        }
        echo "Reinitialization plugin dependencies : " . $plugin_id . '...';
        $cache = CacheManager::byKey('dependancy' . $plugin->getId());
        $cache->remove();
        CacheManager::set('dependancy' . $plugin   ->getId(), "nok");
        echo " OK" . "\n";
    }
    echo "Restoring plugins...";;
    echo " OK" . "\n";

    echo "Update database post plugins...";
    shell_exec('php ' . NEXTDOM_ROOT . '/install/migrate/migrate.php');
    echo " OK" . "\n";

    ConfigManager::save('hardware_name', '');
    $cache = CacheManager::byKey('nextdom::isCapable::sudo');
    $cache->remove();

    try {
        require_once NEXTDOM_ROOT . '/install/consistency.php';
        echo "Check consistency..." . " OK" . "\n";
    } catch (\Exception $ex) {
        echo "Check consistency..." . " NOK" . "\n";
        log::add('restore', 'error', $ex->getMessage());
        echo '*** ' . "ERROR" . '*** ' . $ex->getMessage() . "\n";
    }

    echo "Restoring rights...";
    shell_exec('chmod 775 -R ' . NEXTDOM_ROOT );
    shell_exec('chown -R www-data:www-data ' . NEXTDOM_ROOT );
    shell_exec('chmod 775 -R '.NEXTDOM_LOG );
    shell_exec('chown -R www-data:www-data '.NEXTDOM_LOG);
    shell_exec('chmod 777 -R /tmp/');
    shell_exec('chown www-data:www-data -R /tmp/');
    echo " OK" . "\n";

    try {
        NextDomHelper::startSystem();
    } catch (\Exception $e) {
        log::add('restore', 'error', $e->getMessage());
        echo '*** ' . "ERROR" . '*** ' . $e->getMessage() . "\n";
    }

    try {
        echo "Sends the end event of the restore/migration...";
        NextDomHelper::event('end_restore');
        echo " OK" . "\n";
    } catch (\Exception $e) {
        echo " NOK" . "\n";
        log::add('restore', 'error', $e->getMessage());
        echo '*** ' . "ERROR" . '*** ' . $e->getMessage() . "\n";
    }
    echo "Time of restoration : " . (strtotime('now') - $starttime) . "s\n";
    echo "*************** " . "End of procedure at " . date('Y-m-d H:i:s') . "***************\n";
    echo "[ END RESTORE / MIGRATION ]";
    echo " > SUCCESS" . "\n";
    /* Ne pas supprimer la ligne suivante */
    echo "Closing with success" . "\n";
} catch (\Exception $e) {
    echo "[ END RESTORE / MIGRATION ]";
    echo "\n > ERROR : " . br2nl($e->getMessage()) . "\n";
    echo "Details : " . print_r($e->getTrace(), true) . "\n";
    NextDomHelper::startSystem();
    /* Ne pas supprimer la ligne suivante */
    echo "Closing with error" . "\n";
    throw $e;
}
