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

/**
 * Restore a backup or migrate from Jeedom
 *
 * Usage :
 *  - restore.php [ backup=BACKUP_FILE ]
 *
 * Without arguments, last archive in backup directory will be used
 *
 * Parameters :
 *  - BACKUP_FILE : Name of the file to restore (Doesn't need the full path)
 */


use NextDom\Exceptions\CoreException;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\MigrationHelper;
use NextDom\Helpers\ConsoleHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\ScriptHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\PluginManager;

/**
 * Get the backup file path from params or the newer in the backup directory
 * Can be set in global $BACKUP_FILE or in cli params (backup=FILE_NAME)
 *
 * @return string|null Backup file path
 *
 * @throws Exception
 */
function getBackupFilePath(): string
{
    global $BACKUP_FILE;

    // Set backup file from global var
    if (isset($BACKUP_FILE)) {
        $_GET['backup'] = $BACKUP_FILE;
    }

    $backupFile = Utils::init('backup');
    // If backup file is not set, take the last file in the backup directory
    if ($backupFile == '') {
        $backupPath = ConfigManager::byKey('backup::path');
        if (substr($backupPath, 0, 1) != '/') {
            $fullBackupPath = NEXTDOM_ROOT . '/' . $backupPath;
        } else {
            $fullBackupPath = $backupPath;
        }
        if (!file_exists($fullBackupPath)) {
            mkdir($fullBackupPath);
        }
        $backupFile = null;
        $mtime = null;
        foreach (scandir($fullBackupPath) as $file) {
            if ($file != "." && $file != ".." && $file != ".htaccess" && strpos($file, '.tar.gz') !== false) {
                $s = stat($fullBackupPath . '/' . $file);
                if ($backupFile === null || $mtime === null) {
                    $backupFile = $fullBackupPath . '/' . $file;
                    $mtime = $s['mtime'];
                }
                if ($mtime < $s['mtime']) {
                    $backupFile = $fullBackupPath . '/' . $file;
                    $mtime = $s['mtime'];
                }
            }
        }
    }
    if (substr($backupFile, 0, 1) != '/') {
        $backupFile = NEXTDOM_ROOT . '/' . $backupFile;
    }
    return $backupFile;
}



define('TMP_BACKUP', '/tmp/nextdombackup');

require_once __DIR__ . '/../src/core.php';

ScriptHelper::cliOrCrash();
ScriptHelper::parseArgumentsToGET();

$startTime = strtotime('now');

ConsoleHelper::title('START RESTORE / MIGRATION');

try {
    ConsoleHelper::subTitle('Starting procedure at ' . date('Y-m-d H:i:s'));

    try {
        ConsoleHelper::step('Sends the start event of the restore/migration');
        NextDomHelper::event('begin_restore', true);
        ConsoleHelper::ok();
    } catch (\Exception $e) {
        ConsoleHelper::nok();
        ConsoleHelper::showError($e);
    }

    $backupFile = getBackupFilePath();
    if (!file_exists($backupFile)) {
        throw new CoreException("Backup file not found : " . $backupFile);
    }
    echo "File used for restoration : " . $backupFile . "\n";

    try {
        ConsoleHelper::step('Checking rights');
        NextDomHelper::cleanFileSystemRight();
        ConsoleHelper::ok();
    } catch (\Exception $e) {
        ConsoleHelper::nok();
        LogHelper::add('restore', 'error', $e->getMessage());
        ConsoleHelper::showError($e);
    }

    try {
        NextDomHelper::stopSystem();
    } catch (\Exception $e) {
        ConsoleHelper::nok();
        LogHelper::add('restore', 'error', $e->getMessage());
        ConsoleHelper::showError($e);
    }

    ConsoleHelper::step('Extract the backup');
    $pluginsToExclude = array(
        'AlternativeMarketForJeedom',
        'musicast'
    );
    $excludeParams = '';
    foreach ($pluginsToExclude as $folderToExclude) {
        $excludeParams .= ' --exclude="' . $folderToExclude . '"';
    }
    system('mkdir -p ' . TMP_BACKUP);
    system('cd ' . TMP_BACKUP . '; rm * -rf; tar xfz "' . $backupFile . '" ' . $excludeParams);
    ConsoleHelper::ok();

    ConsoleHelper::step('Delete the backup database');
    if (!file_exists(TMP_BACKUP . '/DB_backup.sql')) {
        throw new CoreException('Unable to find the backup database file : DB_backup.sql');
    } else {
        shell_exec('sed -i -e s/jeedom/nextdom/g ' . TMP_BACKUP . '/DB_backup.sql');
    }
    $tables = \DB::Prepare("SHOW TABLES", array(), \DB::FETCH_TYPE_ALL);
    ConsoleHelper::ok();

    ConsoleHelper::step('Disables backup constraints');
    \DB::Prepare("SET foreign_key_checks = 0", array(), \DB::FETCH_TYPE_ROW);
    ConsoleHelper::ok();

    foreach ($tables as $table) {
        $table = array_values($table);
        $table = $table[0];
        ConsoleHelper::step('Delete the table : ' . $table . '');
        \DB::Prepare('DROP TABLE IF EXISTS `' . $table . '`', array(), \DB::FETCH_TYPE_ROW);
        ConsoleHelper::ok();
    }

    ConsoleHelper::step('Restoring database');
    MigrationHelper::mySqlImport(TMP_BACKUP . '/DB_backup.sql');
    ConsoleHelper::ok();

    ConsoleHelper::step('Migration process');
    ConsoleHelper::enter();
    MigrationHelper::migrate('restore');
    ConsoleHelper::ok();

    ConsoleHelper::step('Enables constraints');
    try {
        \DB::Prepare('SET foreign_key_checks = 1', array(), \DB::FETCH_TYPE_ROW);
    } catch (\Exception $e) {

    }
    ConsoleHelper::ok();

    $jeedomConfigFilePath = NEXTDOM_ROOT . '/core/config/jeedom.config.php';
    if (file_exists($jeedomConfigFilePath)) {
        if (copy($jeedomConfigFilePath, '/tmp/nextdom.config.php')) {
            echo 'Cannot copy ' . $jeedomConfigFilePath . "\n";
        }
    }
    if (!file_exists(NEXTDOM_ROOT . '/core/config/common.config.php')) {
        ConsoleHelper::step('Restoring database configuration file');
        copy(TMP_BACKUP . '/common.config.php', NEXTDOM_ROOT . '/core/config/common.config.php');
        ConsoleHelper::ok();
    }

    ConsoleHelper::step('Restoring rights');
    system('chmod 1777 /tmp -R');
    ConsoleHelper::ok();

    ConsoleHelper::step('Restoring cache');
    if (file_exists(TMP_BACKUP . '/cache.tar.gz')) {
        system('cp -fr ' . TMP_BACKUP . '/cache.tar.gz ' . NEXTDOM_ROOT . '/var');
    } elseif (file_exists(TMP_BACKUP . '/var/cache.tar.gz')) {
        system('cp -fr ' . TMP_BACKUP . '/var/cache.tar.gz ' . NEXTDOM_ROOT . '/var');
    }
    try {
        CacheManager::restore();
    } catch (\Exception $e) {

    }
    ConsoleHelper::ok();

    ConsoleHelper::subTitle('Restoring plugins');
    system('cp -fr ' . TMP_BACKUP . '/plugins/* ' . NEXTDOM_ROOT . '/plugins/');

    foreach (PluginManager::listPlugin(true) as $plugin) {
        $pluginId = $plugin->getId();
        $dependencyInfo = $plugin->getDependencyInfo(true);
        if (method_exists($pluginId, 'restore')) {
            ConsoleHelper::step('Plugin restoration : ' . $pluginId . '');
            $pluginId::restore();
            ConsoleHelper::ok();
        }
        ConsoleHelper::step('Plugin dependencies reinitialization: ' . $pluginId . '');
        $cache = CacheManager::byKey('dependancy' . $plugin->getId());
        $cache->remove();
        CacheManager::set('dependancy' . $plugin->getId(), "nok");
        ConsoleHelper::ok();
    }

    //ConsoleHelper::step('Update database post plugins');
    //migrateDb();
    //ConsoleHelper::ok();

    ConfigManager::save('hardware_name', '');
    $cache = CacheManager::byKey('nextdom::isCapable::sudo');
    $cache->remove();

    try {
        require_once NEXTDOM_ROOT . '/install/consistency.php';
        ConsoleHelper::step('Check consistency');
        ConsoleHelper::ok();
    } catch (\Exception $ex) {
        ConsoleHelper::step('Check consistency');
        ConsoleHelper::nok();
        LogHelper::add('restore', 'error', $ex->getMessage());
        ConsoleHelper::showError($e);
    }

    ConsoleHelper::step('Restoring rights');
    shell_exec('chmod 775 -R ' . NEXTDOM_ROOT);
    shell_exec('chown -R www-data:www-data ' . NEXTDOM_ROOT);
    shell_exec('chmod 775 -R ' . NEXTDOM_LOG);
    shell_exec('chown -R www-data:www-data ' . NEXTDOM_LOG);
    shell_exec('chmod 777 -R /tmp/');
    shell_exec('chown www-data:www-data -R /tmp/');
    ConsoleHelper::ok();

    try {
        NextDomHelper::startSystem();
    } catch (\Exception $e) {
        LogHelper::add('restore', 'error', $e->getMessage());
        ConsoleHelper::showError($e);
    }

    try {
        ConsoleHelper::step('Sends the end event of the restore/migration');
        NextDomHelper::event('end_restore');
        ConsoleHelper::ok();
    } catch (\Exception $e) {
        ConsoleHelper::nok();
        LogHelper::add('restore', 'error', $e->getMessage());
        ConsoleHelper::showError($e);
    }
    $duration = strtotime('now') - $startTime;
    echo 'Time of restoration : ' . $duration . "s\n";
    ConsoleHelper::subTitle('End of process at ' . date('Y-m-d H:i:s'));
    ConsoleHelper::title('END RESTORE / MIGRATION');
    echo " > SUCCESS\n";
    /* DON'T DELETE NEXT LINE */
    echo "Closing with success\n";
} catch (\Exception $e) {
    ConsoleHelper::title('END RESTORE / MIGRATION');
    echo '>>> ERROR : ' . br2nl($e->getMessage()) . "\n";
    echo 'Details : ' . print_r($e->getTrace(), true) . "\n";
    NextDomHelper::startSystem();
    /* DON'T DELETE NEXT LINE */
    echo "Closing with error\n";
    throw $e;
}
