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
use NextDom\Exceptions\CoreException;
use NextDom\Managers\BackupManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\Plan3dHeaderManager;
use NextDom\Managers\Plan3dManager;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PlanManager;

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
    public static function migrate($logFile = 'migration')
    {
        $migrate = false;

        if($logFile == 'migration'){
            LogHelper::clear($logFile);
        }

        // get previous version
        if(ConfigManager::byKey('lastUpdateVersion') == null){
            $migrate = true;
            $previousVersion = [0,0,0];

        } else {
            $previousVersion = explode('.', ConfigManager::byKey('lastUpdateVersion'));
        }
        $previousVersion = array_map('intval',$previousVersion );
        $message = 'Migration/Update process from --> ' . implode('.',$previousVersion);
        if($logFile == 'migration') {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }

        //get current version
        $currentVersion = explode('.', NextDomHelper::getNextdomVersion());
        $currentVersion = array_map('intval',$currentVersion);
        $message ='Migration/Update process to --> ' . implode('.',$currentVersion);
        if($logFile == 'migration') {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }

        //compare versions
        if($migrate === true){
            if($currentVersion !== null && $previousVersion !== null){
                $migrate = self::compareDigit(count($currentVersion), count($previousVersion), $previousVersion, $currentVersion,0);
            }
        }

        // call migrate functions
        if($migrate === true) {
            while ($previousVersion[0] <= $currentVersion[0]) {
                while ($previousVersion[1] <= 10) {
                    while ($previousVersion[2] <= 10) {
                        if(method_exists(get_class(), 'migrate_' . $previousVersion[0] . '_' . $previousVersion[1] . '_' . $previousVersion[2])){
                            $migrateMethod = 'migrate_' . $previousVersion[0] . '_' . $previousVersion[1] . '_' . $previousVersion[2];
                            $message ='Start migration process for '.$migrateMethod;
                            if($logFile == 'migration') {
                                LogHelper::addInfo($logFile, $message, '');
                            } else {
                                ConsoleHelper::process($message);
                            }
                            try {
                                self::$migrateMethod($logFile);
                            } catch(\Exception $exception){
                                throw new CoreException();
                            }
                            $message ='Done migration process for '.$migrateMethod;
                            if($logFile == 'migration') {
                                LogHelper::addInfo($logFile, $message, '');
                            } else {
                                ConsoleHelper::process($message);
                            }
                            ConfigManager::save('lastUpdateVersion', $previousVersion[0].'.'.$previousVersion[1].'.'.$previousVersion[2], 'core');

                            $message ='Save migration process for '.$migrateMethod;
                            if($logFile == 'migration') {
                                LogHelper::addInfo($logFile, $message, '');
                            } else {
                                ConsoleHelper::process($message);
                            }
                        }
                        $previousVersion[2] +=1;
                    }
                    $previousVersion[2] =0;
                    $previousVersion[1] +=1;
                }
                $previousVersion[1] = 0;
                $previousVersion[0] +=1;
            }
        }
        ConfigManager::save('lastUpdateVersion', $currentVersion[0].'.'.$currentVersion[1].'.'.$currentVersion[2], 'core');
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
        if($index > 3 ){
            return $migrate;
        }
        if ($currentVersionSize > $index && $previousVersionSize > $index) {
            if ($previousVersion[$index] < $currentVersion[$index]) {
                $migrate = true;
            } else {
                $migrate = self::compareDigit($currentVersionSize,$previousVersionSize,$previousVersion,$currentVersion,$index+1);
            }
        }
        return $migrate;
    }

    /***************************************************************** 0.0.0 Migration process *****************************************************************/
    /**
     * 0.0.0 Migration process
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function migrate_0_0_0($logFile = 'migration'){

        $migrateFile = sprintf("%s/install/migrate/migrate_0_0_0.sql", NEXTDOM_ROOT);

        BackupManager::loadSQLFromFile($migrateFile);

        $message ='Database basic update';
        if($logFile == 'migration') {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }

        foreach (InteractDefManager::all() as $interactDef) {
            $interactDef->setEnable(1);
            $interactDef->save();
        }

    }
    /***************************************************************** 0.3.0 Migration process *****************************************************************/
    /**
     * 0.3.0 Migration process
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function migrate_0_3_0($logFile = 'migration')
    {
        self::movePersonalFoldersAndFilesToData($logFile);
        self::migrateUserFunctionClass($logFile);
    }

    /**
     * Migration to pass during migrate_themes_to_data
     * @param string $logFile log name file to display information
     * @throws \Exception
     */
    private static function movePersonalFoldersAndFilesToData($logFile = 'migration')
    {
        $message ='Update theme folder';
        if($logFile == 'migration') {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }

        FileSystemHelper::mkdirIfNotExists(NEXTDOM_DATA.'/data/custom/',0775,true);
        $dir = new \RecursiveDirectoryIterator(NEXTDOM_ROOT, \FilesystemIterator::SKIP_DOTS);

        // Flatten the recursive iterator, folders come before their files
        $it  = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

        // Maximum depth is 1 level deeper than the base folder
        $it->setMaxDepth(0);

        try {
            $message ='Start moving files and folders process to ' . NEXTDOM_DATA;
            if($logFile == 'migration') {
                LogHelper::addInfo($logFile, $message, '');
            } else {
                ConsoleHelper::process($message);
            }

            // Basic loop displaying different messages based on file or folder
            foreach ($it as $fileInfo) {
                if ($fileInfo->isDir() || $fileInfo->isFile()) {
                    if(!in_array($fileInfo->getFilename(), FoldersReferential::NEXTDOMFOLDERS)
                        && !in_array($fileInfo->getFilename(), FoldersReferential::NEXTDOMFILES)
                        && !in_array($fileInfo->getFilename(), FoldersReferential::JEEDOMFOLDERS)
                        && !in_array($fileInfo->getFilename(), FoldersReferential::JEEDOMFILES)
                        && !is_link( $fileInfo->getFilename()) ) {

                        $fileToReplace = $fileInfo->getFilename();
                        $message ='Moving ' . NEXTDOM_ROOT .'/'. $fileToReplace;
                        if($logFile == 'migration') {
                            LogHelper::addInfo($logFile, $message, '');
                        } else {
                            ConsoleHelper::process($message);
                        }
                        FileSystemHelper::mv(NEXTDOM_ROOT.'/'.$fileToReplace, sprintf("%s/%s", NEXTDOM_DATA.'/data/custom/', $fileToReplace));

                        self::migratePlanPath($logFile, $fileToReplace,'','data/custom/');
                    }
                }
            }

        } catch(\Exception $exception){
            trow (new CoreException());
        }
        try {
            $dir = new \RecursiveDirectoryIterator(NEXTDOM_DATA.'/data/custom/', \FilesystemIterator::SKIP_DOTS);

            // Flatten the recursive iterator, folders come before their files
            $it  = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

            // Maximum depth is 1 level deeper than the base folder
            $it->setMaxDepth(0);
            // Basic loop displaying different messages based on file or folder
            foreach ($it as $fileInfo) {
                if(!is_link( $fileInfo->getFilename()) ) {

                    $fileToReplace = $fileInfo->getFilename();

                    $message = 'Moving ' . $fileToReplace . ' to /data/custom/' . $fileToReplace;
                    if ($logFile == 'migration') {
                        LogHelper::addInfo($logFile, $message, '');
                    } else {
                        ConsoleHelper::process($message);
                    }
                    self::migratePlanPath($logFile, $fileToReplace,'','data/custom/');
                }
            }
        } catch(\Exception $exception){
            echo $exception;
            trow (new CoreException());
        }

        try {
            $dir = new \RecursiveDirectoryIterator(NEXTDOM_ROOT .'/public/img/', \FilesystemIterator::SKIP_DOTS);

            // Flatten the recursive iterator, folders come before their files
            $it  = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

            // Maximum depth is 1 level deeper than the base folder
            $it->setMaxDepth(0);
            // Basic loop displaying different messages based on file or folder
            foreach ($it as $fileInfo) {
                if(!is_link( $fileInfo->getFilename()) && Utils::startsWith($fileInfo->getFilename(),'plan')) {

                    $fileToReplace = $fileInfo->getFilename();

                    $message = 'Moving ' . $fileToReplace . ' to /data/custom/plans/' . $fileToReplace;
                    if ($logFile == 'migration') {
                        LogHelper::addInfo($logFile, $message, '');
                    } else {
                        ConsoleHelper::process($message);
                    }
                    self::migratePlanPath($logFile,  $fileToReplace, 'public/img/', 'data/custom/plans/');
                    self::migratePlanPath($logFile, $fileToReplace, 'core/img/', 'data/custom/plans/');
                }
            }
        } catch(\Exception $exception){
            echo $exception;
            trow (new CoreException());
        }

        try {
            $dir = new \RecursiveDirectoryIterator(NEXTDOM_ROOT .'/data/custom/plans', \FilesystemIterator::SKIP_DOTS);

            // Flatten the recursive iterator, folders come before their files
            $it  = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

            // Maximum depth is 1 level deeper than the base folder
            $it->setMaxDepth(0);
            // Basic loop displaying different messages based on file or folder
            foreach ($it as $fileInfo) {
                if(!is_link( $fileInfo->getFilename()) && Utils::startsWith($fileInfo->getFilename(),'plan_')) {

                    $fileToReplace = $fileInfo->getFilename();

                    $message = 'Moving ' . $fileToReplace . ' to /data/custom/plans/' . $fileToReplace;
                    if ($logFile == 'migration') {
                        LogHelper::addInfo($logFile, $message, '');
                    } else {
                        ConsoleHelper::process($message);
                    }
                    self::migratePlanPath($logFile,  $fileToReplace, 'public/img/', 'data/custom/plans/');
                    self::migratePlanPath($logFile, $fileToReplace, 'core/img/', 'data/custom/plans/');
                }
            }


            self::migratePlanPath($logFile,  'plan/', 'public/img/', 'data/custom/plans/');
            self::migratePlanPath($logFile, 'plan/', 'core/img/', 'data/custom/plans/');

        } catch(\Exception $exception){
            echo $exception;
            trow (new CoreException());
        }

        $message = 'Migrate theme to data folder is done';
        if($logFile == 'migration') {
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
        if(empty($oldReferencePath)){
            $oldReferencePath = '';
        }
        $message = 'Migrate ' . $oldReferencePath . $fileToReplace . ' to ' .$newReferencePath . $fileToReplace;
        if ($logFile == 'migration') {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }

        foreach (PlanManager::all() as $plan) {

            $html = $plan->getDisplay('text');
            if ($html !== null) {
                $html = str_replace('src="'. $oldReferencePath .$fileToReplace, 'src="' . $newReferencePath . $fileToReplace, $html);
                $html = str_replace('href="'. $oldReferencePath .$fileToReplace, 'href="' . $newReferencePath . $fileToReplace, $html);

                $plan->setDisplay('text', $html);
                $plan->save();
            }
        }

        foreach (Plan3dManager::all() as $plan3d) {

            $html = $plan3d->getDisplay('text');
            if ($html !== null) {
                $html = str_replace('src=\"'.$fileToReplace, 'src=\"' . $newReferencePath . $fileToReplace, $html);
                $html = str_replace('href=\"'.$fileToReplace, 'href=\"' . $newReferencePath . $fileToReplace, $html);

                $plan3d->setDisplay('text', $html);
                $plan3d->save();
            }
        }
    }
    /**
     * Change require_once line in /data/php/user.function.class.php
     * @param string $logFile
     */
    private static function migrateUserFunctionClass($logFile)
    {

        $fileToReplace = NEXTDOM_DATA . '/data/php/user.function.class.php';
        if(FileSystemHelper::isFileExists($fileToReplace)) {
            $fromString = 'require_once dirname(__FILE__) . \'/../../core/php/core.inc.php\';';
            $toString = 'if (file_exists(\'/usr/share/nextdom/src/core.php\')) {
                    require_once(\'/usr/share/nextdom/src/core.php\');
                    } else {
                        require_once(\'/var/www/html/src/core.php\');
                    }';

            $message = 'Apply changes to ' . $fileToReplace;
            if ($logFile == 'migration') {
                LogHelper::addInfo($logFile, $message, '');
            } else {
                ConsoleHelper::process($message);
            }
            $file_contents = file_get_contents($fileToReplace);
            $file_contents = str_replace($fromString, $toString, $file_contents);
            file_put_contents($fileToReplace, $file_contents);
        }
    }


    /***************************************************************** X.X.X Migration process *****************************************************************/

}
