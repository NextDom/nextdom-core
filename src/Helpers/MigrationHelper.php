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
use NextDom\Enums\PlanVersion;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\BackupManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\PlanManager;

/**
 * Class MigrationHelper
 * @package NextDom\Helpers
 */
class MigrationHelper
{

    /**
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

    }

    /**
     *
     */
    private static function migrate_0_1_5($logFile = 'migration')
    {
        self::movePersonalFoldersAndFilesToData($logFile);
    }

    private static function migrate_0_0_0($logFile = 'migration'){

        $migrateFile = sConsoleHelper::step("%s/install/migrate/migrate.sql", NEXTDOM_ROOT);

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

    /**
     * Migration to pass during migrate_themes_to_data
     *
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
            foreach ($it as $fileinfo) {
                if ($fileinfo->isDir() || $fileinfo->isFile()) {
                    if(!in_array($fileinfo->getFilename(), FoldersReferential::NEXTDOMFOLDERS)
                    && !in_array($fileinfo->getFilename(), FoldersReferential::NEXTDOMFILES)) {
//                        printf("Folder - %s\n", $fileinfo->getFilename());
                        $message ='Moving ' . NEXTDOM_ROOT .'/'. $fileinfo->getFilename();
                        if($logFile == 'migration') {
                            LogHelper::addInfo($logFile, $message, '');
                        } else {
                            ConsoleHelper::process($message);
                        }
                        FileSystemHelper::rmove(NEXTDOM_ROOT.'/'.$fileinfo->getFilename(),NEXTDOM_DATA.'/data/'.$fileinfo->getFilename(), false, array(), false, array());
                    }
                }
            }

        } catch(\Exception $exception){
            trow (new CoreException());
        }
        $message ='Start updating database process to plan table';
        if($logFile == 'migration') {
            LogHelper::addInfo($logFile, $message, '');
        } else {
            ConsoleHelper::process($message);
        }

        try {
            foreach (PlanManager::all() as $plan) {
                foreach (PlanVersion::getConstants() as $linkType) {
                    $html = $plan->getHtml($linkType)['html'];
                    // Basic loop displaying different messages based on file or folder
                    foreach ($it as $fileinfo) {
                        if ($html != null && strpos($html, $fileinfo->getFilename()) === true) {
                            str_replace($fileinfo->getFilename(), 'data/' . $fileinfo->getFilename(), $html);
                            $plan->save();
                        }
                    }
                }
            }
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
     * @param int $currentVersionSize
     * @param int $previousVersionSize
     * @param string $previousVersion
     * @param string $currentVersion
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
}
