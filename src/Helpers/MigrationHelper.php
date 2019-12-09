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
use NextDom\Managers\PlanManager;
use NextDom\Managers\UpdateManager;
use NextDom\Model\Entity\Cron;

/**
 * Class MigrationHelper
 * @package NextDom\Helpers
 */
class MigrationHelper
{

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

        // get previous version
        if (ConfigManager::byKey('lastUpdateVersion') == null) {
            $migrate = true;
            $previousVersion = [0, 0, 0];

        } else {
            $previousVersion = explode('.', ConfigManager::byKey('lastUpdateVersion'));
        }
        $previousVersion = array_map('intval', $previousVersion);
        $message = 'Migration/Update process from --> ' . implode('.', $previousVersion);
        if ($logFile == LogTarget::MIGRATION) {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }

        //get current version
        $currentVersion = explode('.', NextDomHelper::getNextdomVersion());
        $currentVersion = array_map('intval', $currentVersion);
        $message = 'Migration/Update process to --> ' . implode('.', $currentVersion);
        if ($logFile == LogTarget::MIGRATION) {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }

        //compare versions
        if ($migrate === true) {
            if ($currentVersion !== null && $previousVersion !== null) {
                $migrate = self::compareDigit(count($currentVersion), count($previousVersion), $previousVersion, $currentVersion, 0);
            }
        }

        // call migrate functions
        if ($migrate === true) {
            while ($previousVersion[0] <= $currentVersion[0]) {
                while ($previousVersion[1] <= 10) {
                    while ($previousVersion[2] <= 10) {
                        if (method_exists(get_class(), 'migrate_' . $previousVersion[0] . '_' . $previousVersion[1] . '_' . $previousVersion[2])) {
                            $migrateMethod = 'migrate_' . $previousVersion[0] . '_' . $previousVersion[1] . '_' . $previousVersion[2];
                            $message = 'Start migration process for ' . $migrateMethod;
                            if ($logFile == LogTarget::MIGRATION) {
                                LogHelper::addInfo($logFile, $message, '');
                            } else {
                                ConsoleHelper::process($message);
                            }
                            try {
                                self::$migrateMethod($logFile);
                            } catch (\Exception $exception) {
                                throw new CoreException();
                            }
                            $message = 'Done migration process for ' . $migrateMethod;
                            if ($logFile == LogTarget::MIGRATION) {
                                LogHelper::addInfo($logFile, $message, '');
                            } else {
                                ConsoleHelper::process($message);
                            }
                            ConfigManager::save('lastUpdateVersion', $previousVersion[0] . '.' . $previousVersion[1] . '.' . $previousVersion[2], 'core');

                            $message = 'Save migration process for ' . $migrateMethod;
                            if ($logFile == LogTarget::MIGRATION) {
                                LogHelper::addInfo($logFile, $message, '');
                            } else {
                                ConsoleHelper::process($message);
                            }
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
     * Replace jeedom to nextdom in database
     *
     * @param string $logFile log name file to display information
     *
     * @throws \Exception
     */
    private static function replaceJeedomInDatabase($logFile = LogTarget::MIGRATION)
    {
        $message = 'Replace jeedom in database';
        if ($logFile == LogTarget::MIGRATION) {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }

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
                        WHERE class = :class
                        AND function = :function';
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

        $message = 'Database basic update';
        if ($logFile == LogTarget::MIGRATION) {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }

        foreach (InteractDefManager::all() as $interactDef) {
            $interactDef->setEnable(1);
            $interactDef->save();
        }

    }
    /***********************************************************************************************************************************************************/

    /***************************************************************** 0.3.0 Migration process *****************************************************************/
    /**
     * 0.3.0 Migration process
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function migrate_0_3_0($logFile = LogTarget::MIGRATION)
    {
        self::movePersonalFoldersAndFilesToData($logFile);
    }

    /**
     * Migration to pass during migrate_themes_to_data
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function movePersonalFoldersAndFilesToData($logFile = LogTarget::MIGRATION)
    {
        $message = 'Update theme folder';
        if ($logFile == LogTarget::MIGRATION) {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }

        FileSystemHelper::mkdirIfNotExists(NEXTDOM_DATA . '/data/custom/', 0775, true);
        $dir = new \RecursiveDirectoryIterator(NEXTDOM_ROOT, \FilesystemIterator::SKIP_DOTS);

        // Flatten the recursive iterator, folders come before their files
        $it = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

        // Maximum depth is 1 level deeper than the base folder
        $it->setMaxDepth(0);

        try {
            $message = 'Start moving files and folders process to ' . NEXTDOM_DATA;
            if ($logFile == LogTarget::MIGRATION) {
                LogHelper::addInfo($logFile, $message, '');
            } else {
                ConsoleHelper::process($message);
            }

            // Basic loop displaying different messages based on file or folder
            foreach ($it as $fileInfo) {
                if ($fileInfo->isDir() || $fileInfo->isFile()) {
                    if (!in_array($fileInfo->getFilename(), FoldersReferential::NEXTDOMFOLDERS)
                        && !in_array($fileInfo->getFilename(), FoldersReferential::NEXTDOMFILES)
                        && !in_array($fileInfo->getFilename(), FoldersReferential::JEEDOMFOLDERS)
                        && !in_array($fileInfo->getFilename(), FoldersReferential::JEEDOMFILES)
                        && !is_link($fileInfo->getFilename())) {

                        $fileToReplace = $fileInfo->getFilename();
                        $message = 'Moving ' . NEXTDOM_ROOT . '/' . $fileToReplace;
                        if ($logFile == LogTarget::MIGRATION) {
                            LogHelper::addInfo($logFile, $message, '');
                        } else {
                            ConsoleHelper::process($message);
                        }
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
            echo $exception;
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
                    self::migratePlanPath($logFile, $fileToReplace, 'public/img/', 'data/custom/plans/');
                    self::migratePlanPath($logFile, $fileToReplace, 'core/img/', 'data/custom/plans/');
                }
            }
        } catch (\Exception $exception) {
            echo $exception;
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
                        self::migratePlanPath($logFile, $fileToReplace, 'public/img/', 'data/custom/plans/');
                        self::migratePlanPath($logFile, $fileToReplace, 'core/img/', 'data/custom/plans/');
                    }
                }


                self::migratePlanPath($logFile, '', 'public/img/', 'data/custom/plans/');
                self::migratePlanPath($logFile, '', 'core/img/', 'data/custom/plans/');
            }
        } catch (\Exception $exception) {
            echo $exception;
            throw(new CoreException());
        }

        $message = 'Migrate theme to data folder is done';
        if ($logFile == LogTarget::MIGRATION) {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }
    }


    /**
     * Update plan and plan3d table to change path given in parameter
     * @param string $logFile
     * @param string $fileToReplace
     * @param string $oldReferencePath
     * @param string $newReferencePath
     * @throws CoreException
     * @throws \ReflectionException
     */
    private static function migratePlanPath($logFile, $fileToReplace, $oldReferencePath, $newReferencePath)
    {
        if (empty($oldReferencePath)) {
            $oldReferencePath = '';
        }
        $message = 'Migrate ' . $oldReferencePath . $fileToReplace . ' to ' . $newReferencePath . $fileToReplace;
        if ($logFile == LogTarget::MIGRATION) {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }

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
    /***********************************************************************************************************************************************************/

    /***************************************************************** 0.5.2 Migration process *****************************************************************/
    /**
     * 0.5.2 Migration process
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function migrate_0_5_2($logFile = LogTarget::MIGRATION)
    {
        if (is_dir(NEXTDOM_DATA . '/data/php')) {
            FileSystemHelper::rrmfile(NEXTDOM_DATA . '/data/php/user.function.class.php');
            FileSystemHelper::rrmfile(NEXTDOM_DATA . '/data/php/user.function.class.sample.php');
        }
        $message = 'user.function files removed';
        if ($logFile == LogTarget::MIGRATION) {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }
    }

    /***********************************************************************************************************************************************************/

    private static function migrate_0_6_1($logFile = LogTarget::MIGRATION)
    {
        exec("sudo sed -i '/vm.swappiness=/d' /etc/sysctl.d/99-sysctl.conf");
        exec("sudo echo 'vm.swappiness=10' >> /etc/sysctl.d/99-sysctl.conf");
    }
}
