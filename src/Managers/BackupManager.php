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

namespace NextDom\Managers;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Helpers\Utils;
use splitbrain\PHPArchive\Tar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class BackupManager
{
    /**
     * Runs the backup procedure
     *
     * Last output should not be removed since it act as a marker in ajax calls
     *
     * @return bool true if no error
     */
    public static function createBackup()
    {
        $backupDir  = self::getBackupDirectory();
        $backupName = self::getBackupFilename();
        $backupPath = sprintf("%s/%s", $backupDir, $backupName);
        $sqlPath    = sprintf("%s/DB_backup.sql", $backupDir);
        $cachePath  = CacheManager::getCachePath();
        $startTime  = strtotime('now');
        $status     = "success";

        try {
            printf("*********** starting backup procedure at %s ***********\n", date('Y-m-d H:i:s'));
            NextDomHelper::event('begin_backup', true);
            printf("starting plugin backup...");
            self::backupPlugins();
            printf("oK\n");
            printf("checking database integrity...");
            self::repairDB();
            printf("oK\n");
            printf("starting database backup...");
            self::createDBBackup($sqlPath);
            printf("oK\n");
            printf("starting cache backup...");
            CacheManager::persist();
            printf("oK\n");
            printf("creating backup archive...");
            self::createBackupArchive($backupPath, $sqlPath, $cachePath);
            printf("oK\n");
            printf("rotating backup archives...");
            self::rotateBackups($backupDir);
            printf("oK\n");
            printf("uploading backup to remote clouds...");
            self::sendRemoteBackup($backupPath);
            printf("oK\n");
            NextDomHelper::event('end_backup');
            printf(" -> STATUS: success\n");
            printf(" -> ELAPSED TIME: %s sec(s)\n", (strtotime('now') - $startTime));
            printf("*********** end of backup procedure at %s ***********\n", date('Y-m-d H:i:s'));
        } catch (\Exception $e) {
            $status = "error";
            printf("Ko\n");
            printf("> ERROR: %s\n", Utils::br2nl($e->getMessage()));
            printf("> DETAILS\n");
            printf("%s\n", print_r($e->getTrace(), true));
            LogHelper::add('backup', 'error', $e->getMessage());
        }

        // the following line acts as marker used in ajax telling that the procedure is finished
        // it should be me removed
        printf("Closing with %s\n\n", $status);
        return ($status == "success");
    }

    /**
     * Runs the restore procedure
     *
     * Last output should not be removed since it act as a marker in ajax calls
     *
     * @param bool $file path to backup archive, when empty, use last available backup
     * @return bool false when error occurs
     */
    public static function restoreBackup($file = '') {
        $backupDir  = self::getBackupDirectory();
        $startTime  = strtotime('now');
        $status     = "success";
        $tmpDir     = "";

        try {
            printf("*********** starting restore procedure at %s ***********\n", date('Y-m-d H:i:s'));
            NextDomHelper::event('begin_restore', true);

            if ($file == null) {
                $file = self::getLastBackupFilePath($backupDir, "newest");
            }
            printf("file used for restoration: %s\n", $file);

            printf("stopping nextdom system...");
            NextDomHelper::stopSystem();
            printf("oK\n");
            printf("extracting backup archive...");
            $tmpDir = self::extractArchive($file);
            printf("oK\n");
            printf("restoring mysql database...");
            self::restoreDatabase($tmpDir);
            printf("oK\n");
            printf("importing jeedom configuration...");
            self::restoreJeedomConfig($tmpDir);
            printf("oK\n");
            printf("restoring cache...");
            self::restoreCache($tmpDir);
            printf("oK\n");
            printf("restoring plugins...");
            self::restorePlugins($tmpDir);
            printf("oK\n");
            printf("migrate database...");
            self::loadSQLMigrateScript();
            printf("oK\n");
            printf("starting nextdom system...");
            NextDomHelper::startSystem();
            printf("oK\n");
            printf("updating system configuration...");
            self::updateConfig();
            printf("oK\n");
            printf("chechking system consistency...");
            ConsistencyManager::checkConsistency();
            printf("oK\n");

            SystemHelper::rrmdir($tmpDir);
            NextDomHelper::event("end_restore");
            printf(" -> STATUS: success\n");
            printf(" -> ELAPSED TIME: %s sec(s)\n", (strtotime('now') - $startTime));
            printf("*********** end of restore procedure at %s ***********\n", date('Y-m-d H:i:s'));
        } catch (\Exception $e) {
            $status = "error";
            printf("Ko\n");
            printf("> ERROR: %s\n", Utils::br2nl($e->getMessage()));
            printf("> DETAILS\n");
            printf("%s\n", print_r($e->getTrace(), true));
            LogHelper::add('restore', 'error', $e->getMessage());
            if (true == is_dir($tmpDir)) {
                SystemHelper::rrmdir($tmpDir);
            }
        }
        // the following line acts as marker used in ajax telling that the procedure is finished
        // it should be me removed
        printf("Closing with %s\n\n", $status);
        return ($status == "success");
    }

    /**
     * Creates a backup archive
     *
     * @param bool $background Lancer la sauvegarde en tâche de fond.
     * @throws \Exception
     */
    public static function backup(bool $background = false)
    {
        if ($background) {
            LogHelper::clear('backup');
            $script = sprintf("%s/install/backup.php interactive=false  > %s 2>&1 &",
                              NEXTDOM_ROOT,
                              LogHelper::getPathToLog('backup'));
            SystemHelper::php($script);
        } else {
            self::createBackup();
        }
    }

    /**
     * Restore a backup from file
     *
     * @param string $file Backup file path
     * @param bool $background Start backup task in background
     */
    public static function restore(string $file = '', bool $background = false)
    {
        if (true == $background) {
            LogHelper::clear("restore");
            $script = sprintf("%s/install/restore.php file=%s interactive=false > %s 2>&1 &",
                              NEXTDOM_ROOT,
                              $file,
                              LogHelper::getPathToLog('restore'));
            SystemHelper::php($script);
        } else {
            self::restoreBackup($file);
        }
    }


    /**
     * Creates an archive with all files that should be included in backup
     *
     * We proceed in 4 steps:
     * 1. creates a tar archive with plugins
     * 2. append mysql backup to tar archive
     * 3. append cache to  to tar archive
     * 4. compress tar archive
     *
     * This allows to untie path dependencies between source files while keeping
     * the same directory structure in the tar archive (in order to keep backward
     * compatibility with jeedom archives).
     *
     * @param string $outputPath path of generated backup archive
     * @retrun bool true archive generated successfully
     * @throws \Exception when error occured while writing the tar archive
     */
    public static function createBackupArchive($outputPath, $sqlPath, $cachePath)
    {
        $pattern = sprintf("|^%s/+|", NEXTDOM_ROOT);
        $tar  = new Tar();
        $tar->setCompression();
        $tar->create($outputPath);
        $tar->addFile($cachePath, "var/cache.tar.gz");
        $tar->addFile($sqlPath,   "DB_backup.sql");

        // iterate on dirs we want to include in archive
        $roots = array("plugins");
        foreach ($roots as $c_root) {
            $path     = sprintf("%s/%s", NEXTDOM_ROOT, $c_root);
            $dirIter = new RecursiveDirectoryIterator($path);
            $riIter  = new RecursiveIteratorIterator($dirIter);
            // iterate on files recursively found
            foreach ($riIter as $c_entry) {
                if (false == $c_entry->isFile())
                    continue;
                $dest = preg_replace($pattern, "", $c_entry->getPathname());
                $tar->addFile($c_entry->getPathname(), $dest);
            }
        }
        $tar->close();
    }

    /**
     * Creates an array with information about existing backup files
     *
     * Available fields:
     * - file: path to backup archive
     * - mtime: current file modification timestamp
     * - size: file size in bytes
     *
     * 1. get file list from globing
     * 2. generate array of object from file list
     *
     * @param string $backupDir backup root directory
     * @param string $order sort result by 'newest' or 'oldest' first
     * @throws CoreException if cannot stat one of the backup files
     * @retrun array of file object
     */
    public static function getBackupFileInfo($backupDir, $order = "newest") {
        $pattern = sprintf("%s/*.gz", $backupDir);
        // 1.
        if (false == ($entries = glob($pattern))) {
            return array();
        }

        // 2.
        $files = array_map(function($c_file) {
            if (false == ($stat = stat($c_file))) {
                throw new CoreException("unable to stat file " . $c_file);
            }
            return array("file" => $c_file, "mtime" => $stat[9], "size" => $stat[7]);
        }, $entries);

        if ($order == "newest") {
            usort($files, function($x, $y) {
                return -1 * ($x["mtime"] - $y["mtime"]);
            });
        } else {
            usort($files, function($x, $y) {
                return ($x["mtime"] - $y["mtime"]);
            });
        }

        return $files;
    }

    /**
     * Returns path to last available backup archive
     *
     * @param string $order sort result by 'newest' or 'oldest' first
     * @throws CoreException when no archive is found
     * @return string archive file path
     */
    private static function getLastBackupFilePath($backupDir, $order = "newest") {
        $files = self::getBackupFileInfo($backupDir, $order);

        if (true == empty($files)) {
            throw new CoreException("unable to find any backup file");
        }
        return $files[0]["file"];
    }

    /**
     * Removes backup archives according to backup::keepDays and backup::maxSize
     *
     * 1. since files are sorted, we can sum-up bytes until limit is reached
     *    from that point, remove all other files
     *
     * @param string $backupDir backup root directory
     * @throws \Exception
     */
    public static function rotateBackups($backupDir) {
        $maxDays         = ConfigManager::byKey('backup::keepDays');
        $maxSizeInBytes  = ConfigManager::byKey('backup::maxSize') * 1024 * 1024;
        $maxSizeInBytes  = 35 * 1024 * 1024;
        $minMtime        = time() - ($maxDays * 60 * 60 * 24);
        $totalBytes      = 0;
        $files           = self::getBackupFileInfo($backupDir, "newest");

        // 1.
        foreach ($files as $c_entry) {
            if (($totalBytes > $maxSizeInBytes) ||
                ($c_entry["mtime"] < $minMtime)) {
                unlink($c_entry["file"]);
                continue;
            }
            $totalBytes += $c_entry["size"];
        }
    }

    /**
     * Trigger remote upload for all available repos
     *
     * @param string $path path to backup archive
     * @retrun bool true is everything went fine
     */
    public static function sendRemoteBackup($path) {
        $repos = UpdateManager::listRepo();
        foreach ($repos as $c_key => $c_val) {
            if (($c_val['scope']['backup'] === false)            ||
                (ConfigManager::byKey($c_key . '::enable') == 0) ||
                (ConfigManager::byKey($c_key . '::cloudUpload') == 0)) {
                continue;
            }
            try {
                $class = sprintf("repo_%s", $c_key);
                $class::backup_send($path);
            } catch (Exception $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns backup directory according to backup::path
     *
     * When backup::path is relative, constructs directory from NEXTDOM_DATA root
     *
     * @retrun string backup root directory
     */
    public static function getBackupDirectory() {
        $dir = ConfigManager::byKey('backup::path');
        if ("/" != substr($dir, 0, 1)) {
            $dir = sprintf("%s/%s", NEXTDOM_DATA, $dir);
        }
        if (false == is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return $dir;
    }

    /**
     * Creates a backup of database to given output file path
     *
     * @throws CoreException true when mysqldump failed
     */
    public static function createDBBackup($outputFile)
    {
        global $CONFIG;

        $status  = 0;
        $format  = "mysqldump --host='%s' --port='%s' --user='%s' --password='%s' %s  > %s 2>/dev/null";
        $command = sprintf($format,
                           $CONFIG['db']['host'],
                           $CONFIG['db']['port'],
                           $CONFIG['db']['username'],
                           $CONFIG['db']['password'],
                           $CONFIG['db']['dbname'],
                           $outputFile);

        system($command, $status);
        if ($status != 0) {
            throw new CoreException("error while dumping database, exited with status " . $status);
        }
    }

    /**
     * Checks and repair database
     *
     * @throws CoreException when mysqlcheck exited with error status
     */
    public static function repairDB()
    {
        global $CONFIG;

        $status  = 0;
        $format  = "mysqlcheck --host='%s' --port='%d' --user='%s' --password='%s' %s --auto-repair --silent 2>/dev/null";
        $command = sprintf($format,
                           $CONFIG['db']['host'],
                           $CONFIG['db']['port'],
                           $CONFIG['db']['username'],
                           $CONFIG['db']['password'],
                           $CONFIG['db']['dbname']);
        system($command, $status);
        if ($status != 0) {
            throw new CoreException("error while checking database, exited with status " . $status);
        }
    }

    /**
     * Runs backup method for each active plugins if available
     *
     * @throws \Exception
     */
    public static function backupPlugins()
    {
        $plugins = PluginManager::listPlugin(true);
        foreach ($plugins as $c_plugin) {
            $pid = $c_plugin->getId();
            if (true == method_exists($pid, 'backup')) {
                $pid::backup();
            }
        }
    }

    /**
     * Computes backup filename from nextdom's name and current datetime
     *
     * @returns string backup filename
     */
    private static function getBackupFilename()
    {
        $date      = date("Y-m-d-H:i:s");
        $version   = NextDomHelper::getJeedomVersion();
        $name      = ConfigManager::byKey('name', 'core', 'NextDom');
        $cleanName = str_replace(array('&','#', "'", '"', '+', "-"), "", $name);
        $cleanName = str_replace(" ", "_", $cleanName);
        $format    = "backup-%s-%s-%s.tar.gz";

        return sprintf($format, $cleanName, $version, $date);
    }


    /**
     * Obtenir la liste des sauvegardes
     *
     * @return array Liste des sauvegardes
     * @throws \Exception
     */
    public static function listBackup(): array
    {
        $backupDir = self::getBackupDirectory();
        $backups   = self::getBackupFileInfo($backupDir, "newest");
        $results   = array();
        foreach ($backups as $c_backup) {
            $path = $c_backup["file"];
            $name = basename($path);
            $results[$path] = $name;
        }
        return $results;
    }

    /**
     * Remove a backup file
     *
     * @param string $backupFilePath Backup file path
     *
     * @throws CoreException
     */
    public static function removeBackup(string $backupFilePath)
    {
        if (file_exists($backupFilePath)) {
            unlink($backupFilePath);
        } else {
            throw new CoreException(__('Impossible de trouver le fichier : ') . $backupFilePath);
        }
    }

    /**
     * Load given file in mysql database
     *
     * @param string $file path to file to load
     * @throw CoreException when a mysql error occurs
     */
    private static function loadSQLFromFile($file)
    {
        if (false === ($content = file_get_contents($file))) {
            throw new CoreException("unable to find sql file " . $file);
        }
        try {
            $cnx = \DB::getConnection();
            $cnx->exec($content);
        } catch (\Exception $e) {
            throw new CoreException("error loading sql file " . $file . " : " . $e->getMessage());
        }
    }

    /**
     * Extracts backup archive to a temporary folder
     *
     * @param string $file path to backup archive
     * @throw CoreException when error on reading archive or creating temporary dir
     * @return string path to generated temporary directory
     */
    private static function extractArchive($file) {
        $excludeDirs = array("AlternativeMarketForJeedom", "musicast");
        $exclude = sprintf("/^(%s)$/", join("|", $excludeDirs));
        $tmpDir  = sprintf("%s-restore-%s", NEXTDOM_TMP, date('Y-m-d-H:i:s'));
        if (false == mkdir($tmpDir, $mode = 0770, true)) {
            throw new CoreException("unable to create tmp directory " . $tmpDir);
        }
        $tar = new Tar();
        $tar->open($file);
        $tar->extract($tmpDir, "", $exclude);
        return $tmpDir;
    }

    /**
     * Loads migrate script into mysql database
     *
     * @throws CoreException from RestoreManager::loadSQLFromFile
     */
    private static function loadSQLMigrateScript() {
        $migrateFile = sprintf("%s/install/migrate/migrate.sql", NEXTDOM_ROOT);

        self::loadSQLFromFile($migrateFile);
    }

    /**
     * Loads mysql dump from backup archive into database
     *
     * @param string $tmpDir extracted backup root directory
     * @throws CoreException when error occurs
     */
    private static function restoreDatabase($tmpDir) {
        $backupFile  = sprintf("%s/DB_backup.sql", $tmpDir);

        if (0 != SystemHelper::vsystem("sed -i -e 's/jeedom/nextdom/g' '%s'", $backupFile)) {
            throw new CoreException("unable to modify content of backup file " . $backupFile);
        }
        \DB::Prepare("SET foreign_key_checks = 0", array(), \DB::FETCH_TYPE_ROW);
        $tables = \DB::Prepare("SHOW TABLES", array(), \DB::FETCH_TYPE_ALL);
        foreach ($tables as $table) {
            $table = array_values($table);
            $table = $table[0];
            $statement = sprintf("DROP TABLE IF EXISTS `%s`", $table[0]);
            \DB::Prepare($statement, array(), \DB::FETCH_TYPE_ROW);
        }
        self::loadSQLFromFile($backupFile);
        self::loadSQLMigrateScript();
        \DB::Prepare("SET foreign_key_checks = 1", array(), \DB::FETCH_TYPE_ROW);
    }

    /**
     * Import common config if not already exists
     *
     * @param string $tmpDir extracted backup root directory
     */
    private static function restoreJeedomConfig($tmpDir) {
        $commonBackup  = sprintf("%s/common.config.php",              $tmpDir);
        $commonConfig  = sprintf("%s/core/config/common.config.php",  NEXTDOM_ROOT);
        $jeedomConfig  = sprintf("%s/core/config/jeedom.config.php",  NEXTDOM_ROOT);

        if (true == file_exists($jeedomConfig)) {
            @unlink($jeedomConfig);
        }
        if ((false == file_exists($commonConfig)) &&
            (true  == file_exists($commonBackup))) {
            if (false == rename($commonBackup, $commonConfig)) {
                // should at least warn, silent fail kept from install/restore.php refactoring
            }
        }
    }

    /**
     * Restore cache from backup archive
     *
     * @param string $tmpDir extracted backup root directory
     */
    private static function restoreCache($tmpDir) {
        $cachePath1 = sprintf("%s/cache.tar.gz",     $tmpDir);
        $cachePath2 = sprintf("%s/var/cache.tar.gz", $tmpDir);
        $cacheDest  = sprintf("%s/cache.tar.gz",     NEXTDOM_DATA);

        if (true == file_exists($cachePath1)) {
            rename($cachePath1, $cacheDest);
        } elseif (true == file_exists($cachePath2)) {
            rename($cachePath2, $cacheDest);
        }

        try {
            CacheManager::restore();
        } catch (\Exception $e) {
            // not a big deal if cache cannot be restored
        }
    }

    /**
     * Restore www-data owner and 775 permissions on plugin directory
     *
     * @throws CoreException on permission error
     */
    private static function restorePluginPerms() {
        $pluginRoot  = sprintf("%s/plugins", NEXTDOM_ROOT);
        $status = SystemHelper::vsystem("%s chown %s:%s -R %s",
                                        SystemHelper::getCmdSudo(),
                                        SystemHelper::getWWWUid(),
                                        SystemHelper::getWWWGid(),
                                        $pluginRoot);
        if (0 != $status) {
            throw new CoreException("unable to restore plugins filesystem owner");
        }

        SystemHelper::vsystem("%s chmod 775 -R %s",
                              SystemHelper::getCmdSudo(),
                              $pluginRoot);
        if (0 != $status) {
            throw new CoreException("unable to restore plugins filesystem rights");
        }
    }

    /**
     * Restore plugins from backup archive
     *
     * @param string $tmpDir extracted backup root directory
     * @throws CoreException
     */
    private static function restorePlugins($tmpDir) {
        $plugingDirs = glob(sprintf("%s/plugins/*", $tmpDir), GLOB_ONLYDIR);
        $pluginRoot  = sprintf("%s/plugins", NEXTDOM_ROOT);

        SystemHelper::rrmdir($pluginRoot . "/*");
        foreach ($plugingDirs as $c_dir) {
            $name = basename($c_dir);
            if (false == rename($c_dir, sprintf("%s/%s", $pluginRoot, $name))) {
                // should probably fail, keeping behavior prior to install/restore.php refactoring
            }
        }

        self::restorePluginPerms();

        $plugins = PluginManager::listPlugin(true);
        foreach ($plugins as $c_plugin) {
            // call plugin restore hook, if any
            $pluginID = $c_plugin->getId();
            $dependencyInfo = $c_plugin->getDependencyInfo(true);
            if (method_exists($pluginID, 'restore')) {
                $pluginID::restore();
            }
            // reset plugin dependencies
            $cache = CacheManager::byKey('dependancy' . $c_plugin->getId());
            $cache->remove();
            CacheManager::set('dependancy' . $c_plugin->getId(), "nok");
        }
    }

    private static function updateConfig() {
        ConfigManager::save('hardware_name', '');
        $cache = CacheManager::byKey('nextdom::isCapable::sudo');
        $cache->remove();
    }
}
