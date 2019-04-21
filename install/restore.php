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
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\ScriptHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\InteractDefManager;
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

/**
 * Import SQL in MySQL da
 *
 * @param string $sqlFilePath Path of the SQL file to import
 */
function mySqlImport($sqlFilePath)
{
    global $CONFIG;
    shell_exec('mysql --host=' . $CONFIG['db']['host'] . ' --port=' . $CONFIG['db']['port'] . ' --user=' . $CONFIG['db']['username'] . ' --password=' . $CONFIG['db']['password'] . ' ' . $CONFIG['db']['dbname'] .  ' --force < ' . $sqlFilePath . ' > /dev/null 2>&1');
}

/**
 * Migrate the database to the new version
 *
 * @throws Exception
 */
function migrateDb()
{
    mySqlImport(__DIR__ . '/migrate/migrate.sql');

    foreach (InteractDefManager::all() as $interactDef) {
        $interactDef->setEnable(1);
        $interactDef->save();
    }
}

/**
 * Show title
 *
 * @param string $title Title to show
 */
function title(string $title)
{
    echo "[ $title ]\n";
}

/**
 * Show subtitle
 *
 * @param string $subTitle Subtitle to show
 */
function subTitle(string $subTitle)
{
    echo "*************** $subTitle ***************\n";
}

/**
 * Show step information
 * @param string $stepTitle Step title to show
 */
function step(string $stepTitle)
{
    echo "$stepTitle... ";
}

/**
 * Show ok message
 */
function ok()
{
    echo " OK\n";
}

/**
 * Show not ok message
 */
function nok()
{
    echo " NOK\n";
}

/**
 * Show error message
 *
 * @param CoreException|Exception $exceptionData Data of the exception
 */
function showError($exceptionData)
{
    echo "*** ERROR *** " . $exceptionData->getMessage() . "\n";
}

define('TMP_BACKUP', '/tmp/nextdombackup');

require_once __DIR__ . '/../src/core.php';

ScriptHelper::cliOrCrash();
ScriptHelper::parseArgumentsToGET();

$startTime = strtotime('now');

title('START RESTORE / MIGRATION');

try {
    subTitle('Starting procedure at ' . date('Y-m-d H:i:s'));

    try {
        step('Sends the start event of the restore/migration');
        NextDomHelper::event('begin_restore', true);
        ok();
    } catch (CoreException $e) {
        nok();
        showError($e);
    }

    $backupFile = getBackupFilePath();
    if (!file_exists($backupFile)) {
        throw new CoreException("Backup file not found : " . $backupFile);
    }
    echo "File used for restoration : " . $backupFile . "\n";

    try {
        step('Checking rights');
        NextDomHelper::cleanFileSystemRight();
        ok();
    } catch (\Exception $e) {
        nok();
        LogHelper::add('restore', 'error', $e->getMessage());
        showError($e);
    }

    try {
        NextDomHelper::stopSystem();
    } catch (\Exception $e) {
        nok();
        LogHelper::add('restore', 'error', $e->getMessage());
        showError($e);
    }

    step('Extract the backup');
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
    ok();

    step('Delete the backup database');
    if (!file_exists(TMP_BACKUP . '/DB_backup.sql')) {
        throw new CoreException('Unable to find the backup database file : DB_backup.sql');
    } else {
        shell_exec('sed -i -e s/jeedom/nextdom/g ' . TMP_BACKUP . '/DB_backup.sql');
    }
    $tables = \DB::Prepare("SHOW TABLES", array(), \DB::FETCH_TYPE_ALL);
    ok();

    step('Disables backup constraints');
    \DB::Prepare("SET foreign_key_checks = 0", array(), \DB::FETCH_TYPE_ROW);
    ok();

    foreach ($tables as $table) {
        $table = array_values($table);
        $table = $table[0];
        step('Delete the table : ' . $table . '');
        \DB::Prepare('DROP TABLE IF EXISTS `' . $table . '`', array(), \DB::FETCH_TYPE_ROW);
        ok();
    }

    step('Restoring database');
    mySqlImport(TMP_BACKUP . '/DB_backup.sql');
    ok();

    step('Updating database');
    migrateDb();
    ok();

    step('Enables constraints');
    try {
        \DB::Prepare('SET foreign_key_checks = 1', array(), \DB::FETCH_TYPE_ROW);
    } catch (\Exception $e) {

    }
    ok();

    $jeedomConfigFilePath = NEXTDOM_ROOT . '/core/config/jeedom.config.php';
    if (file_exists($jeedomConfigFilePath)) {
        if (copy($jeedomConfigFilePath, '/tmp/nextdom.config.php')) {
            echo 'Cannot copy ' . $jeedomConfigFilePath . "\n";
        }
    }
    if (!file_exists(NEXTDOM_ROOT . '/core/config/common.config.php')) {
        step('Restoring database configuration file');
        copy(TMP_BACKUP . '/common.config.php', NEXTDOM_ROOT . '/core/config/common.config.php');
        ok();
    }

    step('Restoring rights');
    system('chmod 1777 /tmp -R');
    ok();

    step('Restoring cache');
    if (file_exists(TMP_BACKUP . '/cache.tar.gz')) {
        system('cp -fr ' . TMP_BACKUP . '/cache.tar.gz ' . NEXTDOM_ROOT . '/var');
    } elseif (file_exists(TMP_BACKUP . '/var/cache.tar.gz')) {
        system('cp -fr ' . TMP_BACKUP . '/var/cache.tar.gz ' . NEXTDOM_ROOT . '/var');
    }
    try {
        CacheManager::restore();
    } catch (\Exception $e) {

    }
    ok();

    subTitle('Restoring plugins');
    system('cp -fr ' . TMP_BACKUP . '/plugins/* ' . NEXTDOM_ROOT . '/plugins/');

    foreach (PluginManager::listPlugin(true) as $plugin) {
        $pluginId = $plugin->getId();
        $dependencyInfo = $plugin->getDependencyInfo(true);
        if (method_exists($pluginId, 'restore')) {
            step('Plugin restoration : ' . $pluginId . '');
            $pluginId::restore();
            ok();
        }
        step('Plugin dependencies reinitialization: ' . $pluginId . '');
        $cache = CacheManager::byKey('dependancy' . $plugin->getId());
        $cache->remove();
        CacheManager::set('dependancy' . $plugin->getId(), "nok");
        ok();
    }

    step('Update database post plugins');
    migrateDb();
    ok();

    ConfigManager::save('hardware_name', '');
    $cache = CacheManager::byKey('nextdom::isCapable::sudo');
    $cache->remove();

    try {
        require_once NEXTDOM_ROOT . '/install/consistency.php';
        step('Check consistency');
        ok();
    } catch (\Exception $ex) {
        step('Check consistency');
        nok();
        LogHelper::add('restore', 'error', $ex->getMessage());
        showError($e);
    }

    step('Restoring rights');
    shell_exec('chmod 775 -R ' . NEXTDOM_ROOT);
    shell_exec('chown -R www-data:www-data ' . NEXTDOM_ROOT);
    shell_exec('chmod 775 -R ' . NEXTDOM_LOG);
    shell_exec('chown -R www-data:www-data ' . NEXTDOM_LOG);
    shell_exec('chmod 777 -R /tmp/');
    shell_exec('chown www-data:www-data -R /tmp/');
    ok();

    try {
        NextDomHelper::startSystem();
    } catch (\Exception $e) {
        LogHelper::add('restore', 'error', $e->getMessage());
        showError($e);
    }

    try {
        step('Sends the end event of the restore/migration');
        NextDomHelper::event('end_restore');
        ok();
    } catch (\Exception $e) {
        nok();
        LogHelper::add('restore', 'error', $e->getMessage());
        showError($e);
    }
    step('Clear cache');
    CacheManager::flush();
    ok();
    $duration = strtotime('now') - $startTime;
    echo 'Time of restoration : ' . $duration . "s\n";
    subTitle('End of process at ' . date('Y-m-d H:i:s'));
    title('END RESTORE / MIGRATION');
    echo " > SUCCESS\n";
    /* DON'T DELETE NEXT LINE */
    echo "Closing with success\n";
} catch (\Exception $e) {
    title('END RESTORE / MIGRATION');
    echo '>>> ERROR : ' . br2nl($e->getMessage()) . "\n";
    echo 'Details : ' . print_r($e->getTrace(), true) . "\n";
    NextDomHelper::startSystem();
    /* DON'T DELETE NEXT LINE */
    echo "Closing with error\n";
    throw $e;
}
