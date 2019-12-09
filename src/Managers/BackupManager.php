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

namespace NextDom\Managers;

use NextDom\Enums\ConfigKey;
use NextDom\Enums\DateFormat;
use NextDom\Enums\FoldersAndFilesReferential;
use NextDom\Enums\LogLevel;
use NextDom\Enums\LogTarget;
use NextDom\Enums\NextDomFolder;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\NextDomFile;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\ConsoleHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\MigrationHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Helpers\Utils;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use splitbrain\PHPArchive\Tar;

/**
 * Class BackupManager
 * @package NextDom\Managers
 */
class BackupManager
{
    private static $logLevel = null;

    /**
     * Creates a backup archive
     *
     * @param bool $background Lancer la sauvegarde en tâche de fond.
     * @throws \Exception
     */
    public static function backup(bool $background = false)
    {
        if (true === $background) {
            LogHelper::clear(LogTarget::BACKUP);
            $script = sprintf("%s/install/backup.php interactive=false  > %s 2>&1 &",
                NEXTDOM_ROOT,
                LogHelper::getPathToLog(LogTarget::BACKUP));
            SystemHelper::php($script);
        } else {
            self::createBackup();
        }
    }

    /**
     * Runs the backup procedure
     *
     * Last output should not be removed since it act as a marker in ajax calls
     *
     * @return bool true if no error
     * @throws CoreException
     */
    public static function createBackup()
    {
        $backupDir = self::getBackupDirectory();

        if (FileSystemHelper::getDirectoryFreeSpace($backupDir) < 400000000) {
            throw new CoreException('Not Enough space to create local backup');
        }
        self::$logLevel = ConfigManager::byKey(ConfigKey::LOG_LEVEL, 'core', LogLevel::ERROR);
        $backupName = self::getBackupFilename();
        $backupPath = sprintf("%s/%s", $backupDir, $backupName);
        $sqlPath = sprintf("%s/DB_backup.sql", $backupDir);
        $cachePath = CacheManager::getArchivePath();
        $startTime = strtotime('now');
        $status = "success";
        try {
            ConsoleHelper::title("Create Backup Process", false);
            ConsoleHelper::subTitle("starting backup procedure at " . date(DateFormat::FULL));
            NextDomHelper::event('begin_backup', true);
            ConsoleHelper::step("stopping NextDom (cron & scenario)");
            NextDomHelper::stopSystem(true);
            ConsoleHelper::ok();
            ConsoleHelper::step("starting plugin backup");
            self::backupPlugins();
            ConsoleHelper::ok();
            ConsoleHelper::step("checking database integrity");
            self::repairDB();
            ConsoleHelper::ok();
            ConsoleHelper::step("starting database backup");
            self::createDBBackup($sqlPath);
            ConsoleHelper::ok();
            ConsoleHelper::step("starting cache backup");
            CacheManager::persist();
            ConsoleHelper::ok();
            ConsoleHelper::step("creating backup archive");
            ConsoleHelper::enter();
            self::createBackupArchive($backupPath, $sqlPath, $cachePath, LogTarget::BACKUP);
            ConsoleHelper::ok();
            ConsoleHelper::step("rotating backup archives");
            self::rotateBackups($backupDir);
            ConsoleHelper::ok();
            ConsoleHelper::step("checking remote backup systems");
            self::sendRemoteBackup($backupPath);
            ConsoleHelper::ok();
        } catch (\Exception $e) {
            $status = "error";
            ConsoleHelper::nok();
            ConsoleHelper::error($e);
            LogHelper::addError(LogTarget::BACKUP, $e->getMessage());
        } finally {
            ConsoleHelper::step("starting NextDom (cron & scenario)");
            NextDomHelper::startSystem();
            ConsoleHelper::ok();
            NextDomHelper::event('end_backup');
            ConsoleHelper::subTitle("end of backup procedure at " . date(DateFormat::FULL));
            ConsoleHelper::subTitle("elapsed time " . (strtotime('now') - $startTime));
        }

        // the following line acts as marker used in ajax telling that the procedure is finished
        // it should be me removed
        ConsoleHelper::subTitle("Closing with " . $status);
        ConsoleHelper::title("Create Backup Process", true);
        return ($status == "success");
    }

    /**
     * Returns backup directory according to backup::path
     *
     * When backup::path is relative, constructs directory from NEXTDOM_DATA root
     *
     * @throws CoreException if backup directory cannot be created
     * @retrun string backup root directory
     */
    public static function getBackupDirectory()
    {
        $dir = ConfigManager::byKey('backup::path');
        if ('/' != substr($dir, 0, 1)) {
            $dir = sprintf("%s/%s", NEXTDOM_DATA, $dir);
        }
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            throw new CoreException("unable to create backup directory " . $dir);
        }
        return $dir;
    }

    /**
     * Computes backup filename from nextdom's name and current datetime
     *
     * @param string $name current nextdom name, default given by ConfigManager
     * @return string
     * @throws \Exception
     */
    public static function getBackupFilename($name = null): string
    {
        $date = date("Y-m-d-H-i-s");
        $version = NextDomHelper::getNextdomVersion();
        $format = "backup-%s-%s-%s.tar.gz";

        if ($name === null) {
            $name = ConfigManager::byKey('name', 'core', 'NextDom');
        }
        $cleanName = str_replace(['&', '#', "'", '"', '+', '-'], '', $name);
        $cleanName = str_replace(' ', '_', $cleanName);

        return sprintf($format, $cleanName, $version, $date);
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
            if (true === method_exists($pid, 'backup')) {
                $pid::backup();
            }
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

        $status = 0;
        $format = "mysqlcheck --host='%s' --port='%d' --user='%s' --password='%s' %s --auto-repair --silent 2>/dev/null";
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
     * Creates a backup of database to given output file path
     *
     * @param $outputFile
     * @throws CoreException true when mysqldump failed
     */
    public static function createDBBackup($outputFile)
    {
        global $CONFIG;

        $status = 0;
        $format = "mysqldump --host='%s' --port='%s' --user='%s' --password='%s' %s  > %s 2>/dev/null";
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
     * @param string $sqlPath
     * @param string $cachePath
     * @param string $logFile
     *
     * @throws CoreException
     * @throws \splitbrain\PHPArchive\ArchiveCorruptedException
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     * @throws \splitbrain\PHPArchive\ArchiveIllegalCompressionException
     * @throws \splitbrain\PHPArchive\FileInfoException
     * @retrun bool true archive generated successfully
     */
    public static function createBackupArchive(string $outputPath, $sqlPath, $cachePath, $logFile)
    {

        $tar = new Tar();
        $tar->setCompression();
        $tar->create($outputPath);

        // Backup cache and SQL files
        $tar->addFile($cachePath, "var/cache.tar.gz");
        $tar->addFile($sqlPath, "DB_backup.sql");

        // Backup config and data folders
        FileSystemHelper::mkdirIfNotExists(NEXTDOM_DATA . '/data/custom', 0775, true);
        $roots = [NEXTDOM_DATA . '/data/', NEXTDOM_DATA . '/config/'];
        $pattern = NEXTDOM_DATA . '/';
        self::addPathToArchive($roots, $pattern, $tar, $logFile);

        // Backup plugins folder
        $roots = [NEXTDOM_ROOT . '/plugins/'];
        $pattern = NEXTDOM_ROOT . '/';
        self::addPathToArchive($roots, $pattern, $tar, $logFile);

        $dir = new \RecursiveDirectoryIterator(NEXTDOM_ROOT, \FilesystemIterator::SKIP_DOTS);
        // Flatten the recursive iterator, folders come before their files
        $it = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);
        // Maximum depth is 1 level deeper than the base folder
        $it->setMaxDepth(0);


        // Backup all files/folder in root folder added by user
        foreach ($it as $fileInfo) {
            if (($fileInfo->isDir() || $fileInfo->isFile()) && !in_array($fileInfo->getFilename(), FoldersAndFilesReferential::NEXTDOM_ROOT_FOLDERS)
                && !in_array($fileInfo->getFilename(), FoldersAndFilesReferential::NEXTDOM_ROOT_FILES)
                && !is_link($fileInfo->getFilename())) {
                $tar->addFile($fileInfo->getPathname(), $fileInfo->getFilename());
                if ($fileInfo->isDir()) {
                    $roots = [NEXTDOM_ROOT . '/' . $fileInfo->getFilename()];
                    self::addPathToArchive($roots, $pattern, $tar, $logFile);
                }
            }
        }

        $tar->close();
    }

    /**
     * @param array $roots
     * @param string $pattern
     * @param Tar $tar
     * @param string logFile
     */
    private static function addPathToArchive($roots, $pattern, $tar, $logFile)
    {
        foreach ($roots as $c_root) {
            $path = $c_root;
            $dirIter = new RecursiveDirectoryIterator($path);
            $riIter = new RecursiveIteratorIterator($dirIter);
            ConsoleHelper::stepLine('Add files of ' . $c_root . ' to archive');
            // iterate on files recursively found
            foreach ($riIter as $c_entry) {
                if (false === $c_entry->isFile()) {
                    continue;
                }
                $message = 'Add folder to archive : ' . $c_entry->getPathname();
                if (self::$logLevel == LogLevel::DEBUG && $logFile === LogTarget::BACKUP) {
                    LogHelper::addDebug($logFile, $message);
                }
                $dest = str_replace($pattern, "", $c_entry->getPathname());
                $tar->addFile($c_entry->getPathname(), $dest);
            }
        }
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
    public static function rotateBackups(string $backupDir)
    {
        $maxDays = ConfigManager::byKey('backup::keepDays');
        $maxSizeInBytes = ConfigManager::byKey('backup::maxSize') * 1024 * 1024;
        $minMtime = time() - ($maxDays * 60 * 60 * 24);
        $totalBytes = 0;
        $files = self::getBackupFileInfo($backupDir, "newest");

        // 1.
        foreach ($files as $c_entry) {
            if (($c_entry["mtime"] < $minMtime) ||
                ($totalBytes > $maxSizeInBytes)) {
                unlink($c_entry["file"]);
                continue;
            }
            $totalBytes += $c_entry["size"];
        }
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
     * @return array
     * @retrun array of file object
     */
    public static function getBackupFileInfo($backupDir, $order = "newest")
    {
        $pattern = sprintf("%s/*.gz", $backupDir);
        // 1.
        $entries = glob($pattern);
        if (false === $entries) {
            return [];
        }

        // 2.
        $array_map = [];
        foreach ($entries as $key => $c_file) {
            $stat = stat($c_file);
            if (false === $stat) {
                throw new CoreException("unable to stat file " . $c_file);
            }
            $array_map[$key] = ["file" => $c_file, "mtime" => $stat[9], "size" => $stat[7]];
        }
        $files = $array_map;

        if ($order == "newest") {
            usort($files, function ($x, $y) {
                return -1 * ($x["mtime"] - $y["mtime"]);
            });
        } else {
            usort($files, function ($x, $y) {
                return ($x["mtime"] - $y["mtime"]);
            });
        }

        return $files;
    }

    /**
     * Trigger remote upload for all available repos
     *
     * @param string $path path to backup archive
     * @return bool
     * @throws \Exception
     * @retrun bool true is everything went fine
     */
    public static function sendRemoteBackup(string $path)
    {
        $repos = UpdateManager::listRepo();
        foreach ($repos as $c_key => $c_val) {
            if (($c_val['scope']['backup'] === false) ||
                (ConfigManager::byKey($c_key . '::enable') == 0) ||
                (ConfigManager::byKey($c_key . '::cloudUpload') == 0)) {
                continue;
            }
            LogHelper::addInfo("system", $c_val['class']);
            try {
                $c_val['class']::backup_send($path);
            } catch (\Exception $e) {
                // Even if we have a samba exception, the backup should be available
            }
        }
        return true;
    }

    /**
     * Restore a backup from file
     *
     * @param string $file Backup file path
     * @param bool $background Start backup task in background
     * @throws \Exception
     */
    public static function restore(string $file = '', bool $background = false)
    {
        if (true === $background) {
            LogHelper::clear("restore");
            $script = sprintf("%s/install/restore.php file=%s interactive=false > %s 2>&1 &",
                NEXTDOM_ROOT,
                $file,
                LogHelper::getPathToLog(LogTarget::RESTORE));
            SystemHelper::php($script);
        } else {
            self::restoreBackup($file);
        }
    }

    /**
     * Runs the restore procedure
     *
     * Last output should not be removed since it act as a marker in ajax calls
     *
     * @param string $file path to backup archive, when empty, use last available backup
     * @return bool false when error occurs
     * @throws CoreException
     */
    public static function restoreBackup($file = '')
    {
        $backupDir = self::getBackupDirectory();
        $startTime = strtotime('now');
        $status = "success";
        $tmpDir = "";

        try {
            ConsoleHelper::title("Restore Backup Process", false);
            ConsoleHelper::subTitle("starting restore procedure at " . date(DateFormat::FULL));
            NextDomHelper::event('begin_restore', true);

            if (($file === null) || ("" === $file)) {
                $file = self::getLastBackupFilePath($backupDir, "newest");
            }
            ConsoleHelper::process("file used for restoration: " . $file);
            ConsoleHelper::ok();
            ConsoleHelper::step("stopping Nextdom system...");
            NextDomHelper::stopSystem(false);
            ConsoleHelper::ok();
            ConsoleHelper::step("extracting backup archive...");
            $tmpDir = self::extractArchive($file);
            ConsoleHelper::ok();
            ConsoleHelper::step("restoring plugins...");
            self::restorePlugins($tmpDir);
            ConsoleHelper::ok();
            ConsoleHelper::step("restoring mysql database...");
            self::restoreDatabase($tmpDir);
            ConsoleHelper::ok();
            ConsoleHelper::step("importing Jeedom configuration...");
            self::restoreJeedomConfig($tmpDir);
            ConsoleHelper::ok();
            ConsoleHelper::step("restoring custom data...\n");
            self::restoreCustomData($tmpDir, LogTarget::RESTORE);
            ConsoleHelper::ok();
            ConsoleHelper::step("migrating data...");
            MigrationHelper::migrate(LogTarget::RESTORE);
            ConsoleHelper::ok();
            ConsoleHelper::step("starting nextdom system...");
            NextDomHelper::startSystem();
            ConsoleHelper::ok();
            ConsoleHelper::step("updating system configuration...");
            self::updateConfig();
            ConsoleHelper::ok();
            ConsoleHelper::step("checking system consistency...");
            ConsistencyManager::checkConsistency();
            ConsoleHelper::ok();
            ConsoleHelper::step("init values...");
            self::initValues();
            ConsoleHelper::ok();
            ConsoleHelper::step("clearing cache...");
            self::clearCache();
            ConsoleHelper::ok();
            ConsoleHelper::step("restoring cache...");
            self::restoreCache($tmpDir);
            ConsoleHelper::ok();
            FileSystemHelper::rrmdir($tmpDir);
            NextDomHelper::event("end_restore");
            ConsoleHelper::subTitle("end of restore procedure at " . date(DateFormat::FULL));
            ConsoleHelper::subTitle("elapsed time " . (strtotime('now') - $startTime));
        } catch (\Exception $e) {
            $status = "error";
            ConsoleHelper::nok();
            ConsoleHelper::error($e);
            LogHelper::addError(LogTarget::RESTORE, $e->getMessage());
            if (true === is_dir($tmpDir)) {
                FileSystemHelper::rrmdir($tmpDir);
            }
            ConsoleHelper::step('starting Nextdom system...');
            NextDomHelper::startSystem();
            ConsoleHelper::ok();
        }
        // the following line acts as marker used in ajax telling that the procedure is finished
        // it should be me removed
        ConsoleHelper::subTitle('Closing with ' . $status);
        ConsoleHelper::title('Restore Backup Process', true);
        return ($status == 'success');
    }

    /**
     * Returns path to last available backup archive
     *
     * @param $backupDir
     * @param string $order sort result by 'newest' or 'oldest' first
     * @return string archive file path
     * @throws CoreException when no archive is found
     */
    public static function getLastBackupFilePath($backupDir, $order = "newest")
    {
        $files = self::getBackupFileInfo($backupDir, $order);

        if (empty($files)) {
            throw new CoreException('unable to find any backup file');
        }
        return $files[0]["file"];
    }

    /**
     * Extracts backup archive to a temporary folder
     *
     * @param string $file path to backup archive
     * @return string path to generated temporary directory
     * @throws CoreException
     * @throws \splitbrain\PHPArchive\ArchiveCorruptedException
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     * @throws \splitbrain\PHPArchive\ArchiveIllegalCompressionException
     * @throw CoreException when error on reading archive or creating temporary dir
     */
    private static function extractArchive($file)
    {
        $excludeDirs = ["AlternativeMarketForJeedom", "musicast"];
        $exclude = sprintf("/^(%s)$/", join("|", $excludeDirs));
        $tmpDir = sprintf("%s-restore-%s", NEXTDOM_TMP, date('Y-m-d-H:i:s'));
        if (false === mkdir($tmpDir, 0775, true)) {
            throw new CoreException("unable to create tmp directory " . $tmpDir);
        }
        if (FileSystemHelper::getDirectoryFreeSpace($tmpDir) < 400000000) {
            throw new CoreException('Not enough space to extract archive');
        }
        $tar = new Tar();
        $tar->open($file);
        $tar->extract($tmpDir, "", $exclude);
        return $tmpDir;
    }

    /**
     * Restore plugins from backup archive
     *
     * @param string $tmpDir extracted backup root directory
     * @throws CoreException
     */
    private static function restorePlugins($tmpDir)
    {
        $pluginDirs = glob(sprintf("%s/plugins/*", $tmpDir), GLOB_ONLYDIR);
        $pluginRoot = sprintf("%s/plugins", NEXTDOM_ROOT);

        FileSystemHelper::rrmdir($pluginRoot);
        FileSystemHelper::mkdirIfNotExists($pluginRoot, 0775, true);
        foreach ($pluginDirs as $c_dir) {
            if (false === FileSystemHelper::mv($c_dir, $pluginRoot)) {
                // should probably fail, keeping behavior prior to install/restore.php refactoring
            }
        }
        self::restorePublicPerms($pluginRoot);

        $plugins = PluginManager::listPlugin(true);
        foreach ($plugins as $c_plugin) {
            // call plugin restore hook, if any
            $pluginID = $c_plugin->getId();
            if (method_exists($pluginID, 'restore')) {
                $pluginID::restore();
            }
            // reset plugin dependencies
            $cache = CacheManager::byKey('dependancy' . $c_plugin->getId());
            $cache->remove();
            CacheManager::set('dependancy' . $c_plugin->getId(), "nok");
        }
    }

    /**
     * Restore www-data owner and 775 permissions on directory
     *
     * @param $folderRoot
     * @throws CoreException on permission error
     */
    private static function restorePublicPerms($folderRoot)
    {
        $status = SystemHelper::vsystem("%s chown %s:%s -R %s",
            SystemHelper::getCmdSudo(),
            SystemHelper::getWWWUid(),
            SystemHelper::getWWWGid(),
            $folderRoot);
        if (0 != $status) {
            throw new CoreException("unable to restore filesystem owner on " . $folderRoot);
        }

        SystemHelper::vsystem("%s chmod 775 -R %s",
            SystemHelper::getCmdSudo(),
            $folderRoot);
        if (0 != $status) {
            throw new CoreException("unable to restore filesystem rights" . $folderRoot);
        }
    }

    /**
     * Loads mysql dump from backup archive into database
     *
     * @param string $tmpDir extracted backup root directory
     * @throws CoreException when error occurs
     */
    private static function restoreDatabase($tmpDir)
    {
        $backupFile = sprintf("%s/DB_backup.sql", $tmpDir);

        //Just database comment changes, rest done in migrationHelper
        if (0 != SystemHelper::vsystem("sed -i -e 's/Database: jeedom/Database: nextdom/g' '%s'", $backupFile)) {
            throw new CoreException("unable to modify content of backup file " . $backupFile);
        }
        if (0 != SystemHelper::vsystem("sed -i -e 's/Definer=`jeedom`/Definer=`nextdom`/g' '%s'", $backupFile)) {
            throw new CoreException("unable to modify content of backup file " . $backupFile);
        }

        DBHelper::exec("SET foreign_key_checks = 0");
        $tables = DBHelper::getAll("SHOW TABLES");
        foreach ($tables as $table) {
            $table = array_values($table);
            $table = $table[0];
            $statement = sprintf("DROP TABLE IF EXISTS `%s`", $table);
            DBHelper::exec($statement);
        }
        self::loadSQLFromFile($backupFile);
        DBHelper::exec("SET foreign_key_checks = 1");
    }

    /**
     * Load given file in mysql database
     *
     * @param string $file path to file to load
     * @throws CoreException
     * @throw CoreException when a mysql error occurs
     */
    public static function loadSQLFromFile($file)
    {
        global $CONFIG;

        $format = "mysql --host='%s' --port='%s' --user='%s' --password='%s' --force %s < %s";
        $status = SystemHelper::vsystem($format,
            $CONFIG['db']['host'],
            $CONFIG['db']['port'],
            $CONFIG['db']['username'],
            $CONFIG['db']['password'],
            $CONFIG['db']['dbname'],
            $file);
        if ($status !== 0) {
            throw new CoreException("error loading sql file " . $file);
        }
    }

    /**
     * Import common config if not already exists
     *
     * @param string $tmpDir extracted backup root directory
     */
    private static function restoreJeedomConfig(string $tmpDir)
    {
        $commonBackup = sprintf("%s/common.config.php", $tmpDir);
        $commonConfig = sprintf("%s/core/config/common.config.php", NEXTDOM_ROOT);
        $jeedomConfig = sprintf("%s/core/config/jeedom.config.php", NEXTDOM_ROOT);

        if (true == file_exists($jeedomConfig)) {
            @unlink($jeedomConfig);
        }
        if (!file_exists($commonConfig) && file_exists($commonBackup)) {
            if (false === FileSystemHelper::mv($commonBackup, $commonConfig)) {
                // should at least warn, silent fail kept from install/restore.php refactoring
            }
        }
    }

    /**
     * Restore custom data from backup archive
     *
     * @param string $tmpDir extracted backup root directory
     * @throws CoreException
     */
    private static function restoreCustomData($tmpDir, $logFile)
    {
        $rootCustomDataDirs = glob(sprintf("%s/*", $tmpDir), GLOB_ONLYDIR);

        foreach ($rootCustomDataDirs as $c_dir) {
            $name = basename($c_dir);
            if (!in_array($name, FoldersAndFilesReferential::NEXTDOM_ROOT_FOLDERS)
                && !in_array($name, FoldersAndFilesReferential::NEXTDOM_ROOT_FILES)
                && !in_array($name, FoldersAndFilesReferential::JEEDOM_BACKUP_FOLDERS)
                && !in_array($name, FoldersAndFilesReferential::JEEDOM_BACKUP_FILES)) {
                $message = 'Restoring folder: ' . $name;
                if ($logFile == LogTarget::MIGRATION) {
                    LogHelper::addInfo($logFile, $message, '');
                } else {
                    ConsoleHelper::process($message);
                }
                if (true === FileSystemHelper::mv($c_dir, sprintf("%s/%s", NEXTDOM_ROOT, $name))) {
                    self::restorePublicPerms(NEXTDOM_ROOT);
                }
                if ($logFile != LogTarget::MIGRATION) {
                    ConsoleHelper::ok();
                }
            }
        }


        $customDataDirs = glob(sprintf("%s/data/*", $tmpDir), GLOB_ONLYDIR);
        $customDataRoot = sprintf("%s/data", NEXTDOM_DATA);

        FileSystemHelper::rrmdir($customDataRoot . "/");
        FileSystemHelper::mkdirIfNotExists($customDataRoot, 0775, true);
        foreach ($customDataDirs as $c_dir) {
            $name = basename($c_dir);
            $message = 'Restoring folder :' . $name;
            if ($logFile == LogTarget::MIGRATION) {
                LogHelper::addInfo($logFile, $message, '');
            } else {
                ConsoleHelper::process($message);
            }
            if (true === FileSystemHelper::mv($c_dir, sprintf("%s/%s", $customDataRoot, $name))) {
                self::restorePublicPerms($customDataRoot);
            }
            if ($logFile != LogTarget::MIGRATION) {
                ConsoleHelper::ok();
            }
        }

        $customPlanDirs = glob(sprintf("%s/core/img/*", $tmpDir), GLOB_ONLYDIR);
        $customPlanRoot = sprintf("%s/data/custom/plans", NEXTDOM_DATA);

        FileSystemHelper::mkdirIfNotExists($customPlanRoot, 0775, true);
        foreach ($customPlanDirs as $c_dir) {
            $name = basename($c_dir);
            if (Utils::startsWith($name, NextDomObj::PLAN)) {
                $message = 'Restoring folder :' . $name;
                if ($logFile == LogTarget::MIGRATION) {
                    LogHelper::addInfo($logFile, $message, '');
                } else {
                    ConsoleHelper::process($message);
                }
                if (true === FileSystemHelper::mv($c_dir, sprintf("%s/%s", $customPlanRoot, $name))) {
                    self::restorePublicPerms($customPlanRoot);
                }
                if ($logFile != LogTarget::MIGRATION) {
                    ConsoleHelper::ok();
                }
            }
        }


    }

    /**
     * Restore cache from backup archive
     *
     * @param string $tmpDir extracted backup root directory
     * @throws CoreException
     */
    private static function restoreCache($tmpDir)
    {

        FileSystemHelper::rrmfile(CacheManager::getArchivePath());
        FileSystemHelper::mv($tmpDir . '/' . NextDomFolder::VAR . '/' . NextDomFile::CACHE_TAR_GZ, CacheManager::getArchivePath());

        CacheManager::restore();
    }
    private static function updateConfig()
    {
        ConfigManager::save('hardware_name', '');
        $cache = CacheManager::byKey('nextdom::isCapable::sudo');
        $cache->remove();
    }

    /**
     * Init default values
     *
     * @throws \Exception
     */
    private static function initValues()
    {
        ConfigManager::save('nextdom::firstUse', 0);
    }

    private static function clearCache()
    {
        CacheManager::flush();
        exec('sh ' . NEXTDOM_ROOT . '/scripts/clear_cache.sh');
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
        $backups = self::getBackupFileInfo($backupDir, 'newest');
        $results = [];
        foreach ($backups as $c_backup) {
            $path = $c_backup['file'];
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
        if (Utils::checkPath($backupFilePath) && file_exists($backupFilePath)) {
            unlink($backupFilePath);
        } else {
            throw new CoreException(__('Impossible de trouver le fichier : ') . $backupFilePath);
        }
    }

    /**
     * Loads migrate script into mysql database
     *
     * @throws CoreException from RestoreManager::loadSQLFromFile
     */
    private static function loadSQLMigrateScript()
    {
        $migrateFile = sprintf("%s/install/migrate/migrate_0_0_0.sql", NEXTDOM_ROOT);

        self::loadSQLFromFile($migrateFile);
    }
}
