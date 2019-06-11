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


namespace NextDom\Managers;

use NextDom\Enums\FoldersReferential;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;

/**
 * Class MigrationHelper
 * @package NextDom\Managers
 */
class MigrationHelper
{

    /**
     * @throws \Exception
     */
    public function check()
    {
        $migrate = false;
        $previousVersion = null;
        $currentVersion = null;

        //get current version
        $currentVersion = NextDomHelper::getNextdomVersion().str_split('.');

        // get previous version
        if(ConfigManager::byKey('lastUpdateVersion') == null){
            $migrate = true;
            $previousVersion = [0,0,0];
        } else {
            $previousVersion = ConfigManager::byKey('lastUpdateVersion').str_split('.');
        }

        //compare versions
        if(!$migrate){
            if($currentVersion != null && $previousVersion != null){
                $migrate = self::compareDigit(count($currentVersion), count($previousVersion), $previousVersion, $currentVersion,0);
            }
        }

        // call migrate functions
        if($migrate) {
            LogHelper::clear('migration');
            while (intval($previousVersion[0]) <= intval($currentVersion[0])) {
                while (intval($previousVersion[1]) <= intval($currentVersion[1])) {
                    while (intval($previousVersion[2]) <= intval($currentVersion[2])) {
                        if(method_exists($this, 'migrate_' . $previousVersion[0] . '_' . $previousVersion[1] . '_' . $previousVersion[2])){
                            $migrateMethod = 'migrate_' . $previousVersion[0] . '_' . $previousVersion[1] . '_' . $previousVersion[2];
                            LogHelper::addInfo('migration', 'Start migration process for '.$migrateMethod, '');
                            try {
                                $migrateMethod();
                            } catch(Exception $exception){
                                trow (new CoreException());
                            }
                            ConfigManager::save('lastUpdateVersion', $previousVersion, 'core');
                        }
                        $previousVersion[2] = intval($previousVersion[2]) + 1;
                    }
                    $previousVersion[1] = intval($previousVersion[1]) + 1;
                }
                $previousVersion[2] = intval($previousVersion[2]) + 1;
            }
        }

    }

    /**
     *
     */
    private static function migrate_0_1_4()
    {
        self::migrate_themes_to_data();
    }

    private static function migrate_0_0_0($logFile = 'migration'){

        //$migrateFile = ConsoleHelper::step(NEXTDOM_ROOT . "/install/migrate/migrate.sql");
        $migrateFile = sprintf("%s/install/migrate/migrate.sql", NEXTDOM_ROOT);


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
    private static function migrate_themes_to_data()
    {


        $directories = glob(NEXTDOM_ROOT . '/*', GLOB_ONLYDIR);

        try {
            LogHelper::addInfo('migration', 'Start moving files and folders process to ' . NEXTDOM_DATA, '');

            // Basic loop displaying different messages based on file or folder
            foreach ($it as $fileInfo) {
                if ($fileInfo->isDir() || $fileInfo->isFile()) {
                    if(!in_array($fileInfo->getFilename(), FoldersReferential::NEXTDOMFOLDERS)
                        && !in_array($fileInfo->getFilename(), FoldersReferential::NEXTDOMFILES)
                        && !is_link( $fileInfo->getFilename()) ) {
                        $message ='Moving ' . NEXTDOM_ROOT .'/'. $fileInfo->getFilename();
                        if($logFile == 'migration') {
                            LogHelper::addInfo($logFile, $message, '');
                        } else {
                            ConsoleHelper::process($message);
                        }
                        FileSystemHelper::rmove(NEXTDOM_ROOT.'/'.$fileInfo->getFilename(),NEXTDOM_DATA.'/data/'.$fileInfo->getFilename(), false, array(), false, array());
                        FileSystemHelper::rrmdir(NEXTDOM_ROOT.'/'.$fileInfo->getFilename());
                    }
                }
            }
        } catch(Exception $exception){
            trow (new CoreException());
        }
        LogHelper::addInfo('migration','Start updating database process to plan table','');

        try {
            // Basic loop displaying different messages based on file or folder
            foreach ($it as $fileInfo) {
                if(!in_array($fileInfo->getFilename(), FoldersReferential::NEXTDOMFOLDERS)
                    && !in_array($fileInfo->getFilename(), FoldersReferential::NEXTDOMFILES)
                    && !is_link( $fileInfo->getFilename()) ) {
                    $fileToReplace = $fileInfo->getFilename();
                    $message = 'Migrate ' . $fileToReplace . ' to /data/' . $fileToReplace;
                    if ($logFile == 'migration') {
                        LogHelper::addInfo($logFile, $message, '');
                    } else {
                        ConsoleHelper::process($message);
                    }

                    foreach (PlanManager::all() as $plan) {
                        foreach (PlanVersion::getConstants() as $linkType) {

                            $html = $plan->getDisplay('text');
                            if ($html !== null) {
                                $html = str_replace($fileToReplace, 'data/' . $fileToReplace, $html);

                                $plan->setDisplay('text', $html);
                                $plan->save();
                            }
                            break;
                        }
                    }
                }
            }
        } catch(Exception $exception){
            trow (new CoreException());
        }
        LogHelper::addInfo('migration','Migrate theme to data folder is odne','');
    }

    /**
     * @param int $currentVersionSize
     * @param int $previousVersionSize
     * @param string $previousVersion
     * @param string $currentVersion
     * @param int $index
     * @return bool
     */
    private static function compareDigit(int $currentVersionSize, int $previousVersionSize, string $previousVersion, string $currentVersion, int $index): bool
    {
        $migrate = false;
        if($index >3){
            return $migrate;
        }
        if ($currentVersionSize > $index && $previousVersionSize > $index) {
            if (intval($previousVersion[$index]) < intval($currentVersion[$index])) {
                $migrate = true;
            } else {
                $migrate = self::compareDigit($currentVersionSize,$previousVersionSize,$previousVersion,$currentVersion,$index+1);
            }
        }
        return $migrate;
    }
}
