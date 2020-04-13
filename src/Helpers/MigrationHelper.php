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


namespace NextDom\Helpers;

use NextDom\Enums\DateFormat;
use NextDom\Enums\FoldersReferential;
use NextDom\Enums\LogTarget;
use NextDom\Enums\PlanDisplayType;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\BackupManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\ConsistencyManager;
use NextDom\Managers\CronManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\Plan3dManager;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PlanManager;
use NextDom\Managers\UpdateManager;
use NextDom\Managers\UserManager;
use NextDom\Model\Entity\Cron;
use NextDom\Model\Entity\PlanHeader;

/**
 * Class MigrationHelper
 * @package NextDom\Helpers
 */
class MigrationHelper
{
    /**
     * Log message
     *
     * @param $targetLogFile
     * @param $message
     * @throws \Exception
     */
    private static function logMessage($targetLogFile, $message) {
        if ($targetLogFile == LogTarget::MIGRATION) {
            LogHelper::addInfo($targetLogFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }
    }

    /**
     * Main migrate process
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    public static function migrate($logFile = LogTarget::MIGRATION)
    {
        $migrate = false;

        if ($logFile == LogTarget::MIGRATION) {
            LogHelper::clear($logFile);
        }

        //get current version
        $currentVersion = explode('.', NextDomHelper::getNextdomVersion());
        $currentVersion = array_map('intval', $currentVersion);

        // get previous version
        if (ConfigManager::byKey('lastUpdateVersion') == null) {
            $migrate = true;
            $previousVersion = [0, 0, 0];
        } else {
            $previousVersion = explode('.', ConfigManager::byKey('lastUpdateVersion'));
            //compare versions
            if ($currentVersion !== null && $previousVersion !== null) {
                 $migrate = self::compareDigit(count($currentVersion), count($previousVersion), $previousVersion, $currentVersion, 0);
            }
        }
        $previousVersion = array_map('intval', $previousVersion);
        $message = 'Migration/Update process from --> ' . implode('.', $previousVersion);
        self::logMessage($logFile, $message);

        $message = 'Migration/Update process to --> ' . implode('.', $currentVersion);
        self::logMessage($logFile, $message);

        // call migrate functions
        if ($migrate === true) {
            $previousVersion[2]++;
            while ($previousVersion[0] <= $currentVersion[0]) {
                while ($previousVersion[1] <= 10) {
                    while ($previousVersion[2] <= 10) {
                        if (method_exists(get_class(), 'migrate_' . $previousVersion[0] . '_' . $previousVersion[1] . '_' . $previousVersion[2])) {
                            $migrateMethod = 'migrate_' . $previousVersion[0] . '_' . $previousVersion[1] . '_' . $previousVersion[2];
                            $message = 'Start migration process for ' . $migrateMethod;
                            self::logMessage($logFile, $message);
                            try {
                                self::$migrateMethod($logFile);
                            } catch (\Exception $exception) {
                                throw new CoreException();
                            }
                            $message = 'Done migration process for ' . $migrateMethod;
                            self::logMessage($logFile, $message);
                            ConfigManager::save('lastUpdateVersion', $previousVersion[0] . '.' . $previousVersion[1] . '.' . $previousVersion[2], 'core');

                            $message = 'Save migration process for ' . $migrateMethod;
                            self::logMessage($logFile, $message);
                        }
                        $previousVersion[2] += 1;
                    }
                    $previousVersion[2] = 0;
                    $previousVersion[1] += 1;
                }
                $previousVersion[1] = 0;
                $previousVersion[0] += 1;
            }
        }
        self::fixHtaccess();
        self::replaceJeedomInDatabase($logFile);
        ConfigManager::save('lastUpdateVersion', $currentVersion[0] . '.' . $currentVersion[1] . '.' . $currentVersion[2], 'core');
    }

    /**
     * Compare digit version
     * @param int $currentVersionSize
     * @param int $previousVersionSize
     * @param array $previousVersion
     * @param array $currentVersion
     * @param int $index
     * @return bool
     */
    private static function compareDigit(int $currentVersionSize, int $previousVersionSize, array $previousVersion, array $currentVersion, int $index): bool
    {
        $migrate = false;
        if ($index > 3) {
            return $migrate;
        }
        if ($currentVersionSize > $index && $previousVersionSize > $index) {
            if ($previousVersion[$index] < $currentVersion[$index]) {
                $migrate = true;
            } else {
                $migrate = self::compareDigit($currentVersionSize, $previousVersionSize, $previousVersion, $currentVersion, $index + 1);
            }
        }
        return $migrate;
    }

    /**
     * Fix htaccess problems in data folder
     *
     * @param string $logFile
     */
    private static function fixHtaccess($logFile = LogTarget::MIGRATION)
    {

        exec("find " . NEXTDOM_DATA . " -name '.htaccess' -exec sed -i s/\\|jpeg\\|/\\|jpe\\?g\\|/g {} +");
    }

    /**
     * Replace jeedom to nextdom in database
     *
     * @param string $logFile log name file to display information
     *
     * @throws \Exception
     */
    private static function replaceJeedomInDatabase($logFile = LogTarget::MIGRATION)
    {
        self::logMessage($logFile, 'Replace jeedom in database');

        //Update Config table
        $allConfigKeys = ConfigManager::searchKey('', 'core');
        foreach ($allConfigKeys as $keyData) {
            if (strpos($keyData['key'], 'jeedom') !== false) {
                $configValue = ConfigManager::byKey($keyData['key'], 'core');
                ConfigManager::save(str_replace('jeedom', 'nextdom', $keyData['key']), $configValue, 'core');
                ConfigManager::remove($keyData['key'], 'core');
            }
        }
        ConfigManager::save('nextdom::firstUse', 0, 'core');

        // Update Crons table
        $sql = 'UPDATE `cron`
                   SET `class` = "nextdom"
                 WHERE `class` = "jeedom"';
        try {
            DBHelper::exec($sql);
        } catch (\Exception $e) {

        }

        // Check doublon on update
        foreach (ConsistencyManager::getDefaultCrons() as $cronClass => $cronData) {
            foreach ($cronData as $cronName => $cronConfig) {
                $sql = 'SELECT ' . DBHelper::buildField(CronManager::CLASS_NAME) . '
                        FROM ' . CronManager::DB_CLASS_NAME . '
                        WHERE `class` = :class
                        AND `function` = :function';
                $params = [
                    'class' => $cronClass,
                    'function' => $cronName,
                ];
                /** @var Cron[] $result */
                $result = DBHelper::getAllObjects($sql, $params, CronManager::CLASS_NAME);
                if (count($result) > 1) {
                    $result[1]->remove();
                }
            }
        }

        // Update jeedom version
        foreach (UpdateManager::all() as $update) {
            if ($update->getType() == 'core' && $update->getName() == 'jeedom' && $update->getLogicalId() == 'jeedom') {
                $update->setName('nextdom');
                $update->setLogicalId('nextdom');
                $update->save();
            }
        }
    }

    /***************************************************************** 0.0.0 Migration process *****************************************************************/
    /**
     * 0.0.0 Migration process
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function migrate_0_0_0($logFile = LogTarget::MIGRATION)
    {

        $migrateFile = sprintf("%s/install/migrate/migrate_0_0_0.sql", NEXTDOM_ROOT);

        BackupManager::loadSQLFromFile($migrateFile);

        self::logMessage($logFile, 'Database basic update');

        foreach (InteractDefManager::all() as $interactDef) {
            $interactDef->setEnable(1);
            $interactDef->save();
        }
        self::logMessage($logFile, 'Interact definition update');

    }
    /***********************************************************************************************************************************************************/

    /***************************************************************** 0.7.1 Migration process *****************************************************************/

    /**
     * Migration to pass during migrate_themes_to_data (should be done in 0.3.0)
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function movePersonalFoldersAndFilesToData($logFile = LogTarget::MIGRATION)
    {
        self::logMessage($logFile, 'Update theme folder');

        FileSystemHelper::mkdirIfNotExists(NEXTDOM_DATA . '/data/custom/', 0775, true);
        $dir = new \RecursiveDirectoryIterator(NEXTDOM_ROOT, \FilesystemIterator::SKIP_DOTS);

        // Flatten the recursive iterator, folders come before their files
        $it = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

        // Maximum depth is 1 level deeper than the base folder
        $it->setMaxDepth(0);

        try {
            self::logMessage($logFile, 'Start moving files and folders process to ' . NEXTDOM_DATA);

            // Basic loop displaying different messages based on file or folder
            foreach ($it as $fileInfo) {
                if ($fileInfo->isDir() || $fileInfo->isFile()) {
                    if (!in_array($fileInfo->getFilename(), FoldersReferential::NEXTDOMFOLDERS)
                        && !in_array($fileInfo->getFilename(), FoldersReferential::NEXTDOMFILES)
                        && !in_array($fileInfo->getFilename(), FoldersReferential::JEEDOMFOLDERS)
                        && !in_array($fileInfo->getFilename(), FoldersReferential::JEEDOMFILES)
                        && !is_link($fileInfo->getFilename())) {

                        $fileToReplace = $fileInfo->getFilename();
                        self::logMessage($logFile, 'Moving ' . NEXTDOM_ROOT . '/' . $fileToReplace);
                        FileSystemHelper::mv(NEXTDOM_ROOT . '/' . $fileToReplace, sprintf("%s/%s", NEXTDOM_DATA . '/data/custom/', $fileToReplace));

                        self::migratePlanPath($logFile, $fileToReplace, '', 'data/custom/');
                    }
                }
            }

        } catch (\Exception $exception) {
            throw(new CoreException());
        }
        try {
            $dir = new \RecursiveDirectoryIterator(NEXTDOM_DATA . '/data/custom/', \FilesystemIterator::SKIP_DOTS);

            // Flatten the recursive iterator, folders come before their files
            $it = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

            // Maximum depth is 1 level deeper than the base folder
            $it->setMaxDepth(0);
            // Basic loop displaying different messages based on file or folder
            foreach ($it as $fileInfo) {
                if (!is_link($fileInfo->getFilename())) {

                    $fileToReplace = $fileInfo->getFilename();
                    self::migratePlanPath($logFile, $fileToReplace, '', 'data/custom/');
                }
            }
        } catch (\Exception $exception) {
            self::logMessage($logFile, $exception.getMessage());
            throw(new CoreException());
        }

        try {
            $dir = new \RecursiveDirectoryIterator(NEXTDOM_ROOT . '/public/img/', \FilesystemIterator::SKIP_DOTS);

            // Flatten the recursive iterator, folders come before their files
            $it = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

            // Maximum depth is 1 level deeper than the base folder
            $it->setMaxDepth(0);
            // Basic loop displaying different messages based on file or folder
            foreach ($it as $fileInfo) {
                if (!is_link($fileInfo->getFilename()) && Utils::startsWith($fileInfo->getFilename(), 'plan')) {

                    $fileToReplace = $fileInfo->getFilename();
                    self::migratePlanPath($logFile, $fileToReplace, 'public/img/', 'data/plan/');
                    self::migratePlanPath($logFile, $fileToReplace, 'core/img/', 'data/plan/');
                }
            }
        } catch (\Exception $exception) {
            self::logMessage($logFile, $exception.getMessage());
            throw(new CoreException());
        }

        try {
            if (file_exists(NEXTDOM_DATA . '/data/custom/plans')) {
                $dir = new \RecursiveDirectoryIterator(NEXTDOM_DATA . '/data/custom/plans', \FilesystemIterator::SKIP_DOTS);

                // Flatten the recursive iterator, folders come before their files
                $it = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

                // Maximum depth is 1 level deeper than the base folder
                $it->setMaxDepth(0);
                // Basic loop displaying different messages based on file or folder
                foreach ($it as $fileInfo) {
                    if (!is_link($fileInfo->getFilename()) && Utils::startsWith($fileInfo->getFilename(), 'plan_')) {
                        $fileToReplace = $fileInfo->getFilename();
                        self::migratePlanPath($logFile, $fileToReplace, 'public/img/', 'data/plan/');
                        self::migratePlanPath($logFile, $fileToReplace, 'core/img/', 'data/plan/');
                        self::migratePlanPath($logFile, $fileToReplace, 'data/custom/plans/', 'data/plan/');
                        self::logMessage($logFile, 'File' . NEXTDOM_DATA . '/data/custom/plans/' . $fileInfo->getFilename());
                        $dirname = dirname(NEXTDOM_DATA . '/data/plan/' . $fileInfo->getFilename());
                        if (!is_dir($dirname)) {
                            mkdir($dirname, 0775, true);
                        }
                        FileSystemHelper::rcopy(NEXTDOM_DATA . '/data/custom/plans/' . $fileInfo->getFilename(), NEXTDOM_DATA . '/data/plan/' . $fileInfo->getFilename());
                    }
                }


                self::migratePlanPath($logFile, '', 'public/img/', 'data/plan/');
                self::migratePlanPath($logFile, '', 'core/img/', 'data/plan/');
            }
        } catch (\Exception $exception) {
            self::logMessage($logFile, $exception.getMessage());
            throw(new CoreException());
        }

        self::logMessage($logFile, 'Migrate theme to data folder is done');
    }


    /**
     * Update plan and plan3d table to change path given in parameter
     * @param string $logFile
     * @param string $fileToReplace
     * @param string $oldReferencePath
     * @param string $newReferencePath
     * @throws CoreException
     * @throws \ReflectionException
     * @throws \NextDom\Exceptions\OperatingSystemException
     */
    private static function migratePlanPath($logFile, $fileToReplace, $oldReferencePath, $newReferencePath)
    {
        if (empty($oldReferencePath)) {
            $oldReferencePath = '';
        }
        self::logMessage($logFile, 'Migrate ' . $oldReferencePath . $fileToReplace . ' to ' . $newReferencePath . $fileToReplace);

        foreach (PlanManager::all() as $plan) {
            foreach (PlanDisplayType::getValues() as $displayType) {

                $html = $plan->getDisplay($displayType);
                if ($html !== null) {
                    if ($displayType == PlanDisplayType::PATH && !empty($oldReferencePath)) {
                        $html = str_replace($oldReferencePath . $fileToReplace, $newReferencePath . $fileToReplace, $html);
                    } else {
                        $html = str_replace('"' . $oldReferencePath . $fileToReplace, '"' . $newReferencePath . $fileToReplace, $html);
                    }
                    $plan->setDisplay($displayType, $html);
                    $plan->save();
                }
            }
        }

        foreach (Plan3dManager::all() as $plan3d) {

            foreach (PlanDisplayType::getValues() as $displayType) {

                $html = $plan3d->getDisplay($displayType);
                if ($html !== null) {
                    if ($displayType == PlanDisplayType::PATH) {
                        $html = str_replace($oldReferencePath . $fileToReplace, $newReferencePath . $fileToReplace, $html);
                    } else {
                        $html = str_replace('"' . $oldReferencePath . $fileToReplace, '"' . $newReferencePath . $fileToReplace, $html);
                    }
                    $plan3d->setDisplay($displayType, $html);
                    $plan3d->save();
                }
            }
        }
    }

    /**
     * Migration removing user.function.class.php (should be done in 0.5.2)
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function migrate_removeFunctionClass($logFile = LogTarget::MIGRATION)
    {
        if (is_dir(NEXTDOM_DATA . '/data/php')) {
            FileSystemHelper::rrmfile(NEXTDOM_DATA . '/data/php/user.function.class.php');
            FileSystemHelper::rrmfile(NEXTDOM_DATA . '/data/php/user.function.class.sample.php');
        }
        self::logMessage($logFile, 'user.function files removed');
    }

    /**
     * Migration modifying swapiness (should be done in 0.6.1)
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function migrate_swapiness($logFile = LogTarget::MIGRATION)
    {
        try {
            exec("sudo sed -i '/vm.swappiness=/d' /etc/sysctl.d/99-sysctl.conf");
            exec("sudo echo 'vm.swappiness=10' >> /etc/sysctl.d/99-sysctl.conf");
        } catch (\Exception $exception) {
            self::logMessage($logFile, $exception.getMessage());
            throw(new CoreException());
        }
        self::logMessage($logFile, 'Swapiness configuration update');
    }

    /**
     * Migration adding icon field to message table (should be done in 0.6.2)
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function migrate_updateHash($logFile = LogTarget::MIGRATION)
    {
        foreach (UserManager::all() as $user) {
            if($user->getProfils() != 'admin' || $user->getOptions('doNotRotateHash',0) == 1){
                continue;
            }
            $user->setHash('');
            $user->getHash();
            $user->setOptions('hashGenerated',date(DateFormat::FULL));
            $user->save();
        }

        $sql = "SELECT concat('ALTER TABLE ', TABLE_NAME, ' DROP FOREIGN KEY ', CONSTRAINT_NAME, ';')
                FROM information_schema.key_column_usage
                WHERE CONSTRAINT_SCHEMA = 'nextdom'
                AND REFERENCED_TABLE_NAME IS NOT NULL;";
        $result = DBHelper::getAll($sql);
        foreach ($result as $value) {
            try {
                DBHelper::exec(array_values($value)[0]);
            } catch (\Exception $exception) {
                self::logMessage($logFile, $exception.getMessage());
                throw(new CoreException());
            }
        }
    }

    /**
     * Migration adding icon field to message table (should be done in 0.7.0)
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function migrate_messageToAddIcon($logFile = LogTarget::MIGRATION)
    {
        try {
            DBHelper::exec("ALTER message ADD icon MEDIUMTEXT");
        } catch (\Exception $exception) {
            self::logMessage($logFile, $exception.getMessage());
            throw(new CoreException());
        }
    }

    /**
     * Migration changing engine of interactQuery table (should be done in 0.7.1)
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function migrate_interactQueryEngine($logFile = LogTarget::MIGRATION)
    {
        try {
            DBHelper::exec("ALTER TABLE `interactQuery` ENGINE=InnoDB;"); //Peut-être fait plus tot.
        } catch (\Exception $exception) {
            self::logMessage($logFile, $exception.getMessage());
            throw(new CoreException());
        }
    }

    /**
     * 0.7.1 Migration process (apply all migration process from 0.3.0 not done yet)
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function migrate_0_7_1($logFile = LogTarget::MIGRATION)
    {
        self::movePersonalFoldersAndFilesToData($logFile);
        self::migrate_removeFunctionClass($logFile);
        self::migrate_swapiness($logFile);
        self::migrate_updateHash($logFile);
        self::migrate_messageToAddIcon($logFile);
        self::migrate_interactQueryEngine($logFile);
    }

    /***************************************************************** 0.8.0 Migration process *****************************************************************/
    /**
     * 0.8.0 Migration process
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function migrate_0_8_0($logFile = LogTarget::MIGRATION)
    {
        $dir = NEXTDOM_DATA . '/';
        $planHeaderList = PlanHeaderManager::all();
        foreach ($planHeaderList as $planHeader) {
            if (!is_file($dir.$planHeader->getImgLink()) && !empty($planHeader->getImage('data'))) {
                $dirname = dirname($dir . $planHeader->getImgLink());
                if (!is_dir($dirname)) {
                    mkdir($dirname, 0775, true);
                }
                self::logMessage($logFile, "Create " . $planHeader->getImgLink());
                file_put_contents($dir . $planHeader->getImgLink(), base64_decode($planHeader->getImage('data')));
                $planHeader->setImage('data', '');
                $planHeader->save();
            }
        }
        self::logMessage($logFile, 'Create background plan, and delete data in DB.');
        // delete /data/custom/plans/
        $custom_plans = $dir . 'data/custom/plans/';
        FileSystemHelper::rrmdir($custom_plans);

        DBHelper::exec("ALTER TABLE `cmd` add `html` mediumtext COLLATE utf8_unicode_ci;");
        DBHelper::exec("ALTER TABLE `type` DROP COLUMN `scenario`;");
        DBHelper::exec("RENAME TABLE `widgets` TO `widget`");
        $createWidget = "CREATE TABLE IF NOT EXISTS `widget` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `type` varchar(27) COLLATE utf8_unicode_ci DEFAULT NULL,
              `subtype` varchar(27) COLLATE utf8_unicode_ci DEFAULT NULL,
              `template` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `display` text COLLATE utf8_unicode_ci,
              `replace` text COLLATE utf8_unicode_ci,
              `test` text COLLATE utf8_unicode_ci,
              PRIMARY KEY (`id`),
              UNIQUE KEY `unique` (`type`,`subtype`,`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        DBHelper::exec($createWidget);
    }
}
