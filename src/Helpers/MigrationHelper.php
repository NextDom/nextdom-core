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
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
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
            echo "ICICICICIICI";
            $migrate = true;
            $previousVersion = [0,0,0];
            echo "ICICICICIICI2222222222222222222";

        } else {
            echo "LALALLALALALA";
            $previousVersion = explode('.', ConfigManager::byKey('lastUpdateVersion'));
        }
        $previousVersion = array_map('intval',$previousVersion );
//        LogHelper::addInfo($logFile, 'Start migration process from ' , '');
        echo 'Start migration process to --> ' . implode('.',$previousVersion);

        //get current version
        $currentVersion = explode('.', NextDomHelper::getNextdomVersion());
        $currentVersion = array_map('intval',$currentVersion);
//        LogHelper::addInfo($logFile, 'Start migration process to --> ', '');
        echo 'Start migration process to --> ' . implode('.',$currentVersion);

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
                            echo 'Start migration process for '.$migrateMethod;
//                            LogHelper::addInfo($logFile, 'Start migration process for '.$migrateMethod, '');
                            try {
                                self::$migrateMethod($logFile);
                            } catch(\Exception $exception){
                                throw new CoreException();
                            }
                            echo 'Done migration process for '.$migrateMethod;
                            ConfigManager::save('lastUpdateVersion', $previousVersion[0].'.'.$previousVersion[1].'.'.$previousVersion[2], 'core');
                            echo 'Save migration process for '.$migrateMethod;
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
        self::migrate_themes_to_data($logFile);
    }

    private static function migrate_0_0_0($logFile = 'migration'){

            self::mySqlImport(NEXTDOM_ROOT . '/install/migrate/migrate_0_0_0.sql');
            //LogHelper::addInfo($logFile, 'migrate_0_0_0', '');
            echo 'migrate_0_0_0';

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
    private static function migrate_themes_to_data($logFile = 'migration')
    {

        echo "Migration inside... ";
        $directories = glob(NEXTDOM_ROOT . '/*', GLOB_ONLYDIR);

        try {
//            LogHelper::addInfo($logFile, 'Start moving files and folders process to ' . NEXTDOM_DATA, '');
            echo 'Start moving files and folders process to ' . NEXTDOM_DATA;

            foreach ($directories as $directory) {
                if (!FoldersReferential::NEXTDOMFOLDERS . contains($directory, false)) {
//                    LogHelper::addInfo($logFile, 'moving : ' . $directory . ' to : ' . NEXTDOM_DATA, '');
                    rename($directory, NEXTDOM_DATA . "/$directory");
                }
            }
        } catch(\Exception $exception){
            echo $exception.collator_get_error_message();
            trow (new CoreException());
        }
//        LogHelper::addInfo($logFile,'Start updating database process to plan table','');
        echo 'Start updating database process to plan table';

        try {
            foreach (PlanManager::all() as $plan) {
                $html = $plan->getHtml(null);
                foreach ($directories as $directory) {
                    if ($html != null && $html . contains($directory)) {
                        $plan->getHtml(null) . preg_replace('/' . $directory, '/data/' . $directory);
                        $plan->save();
                    }
                }
            }
        } catch(\Exception $exception){
            trow (new CoreException());
        }
//        LogHelper::addInfo($logFile,'Migrate theme to data folder is done','');
        echo 'Migrate theme to data folder is done';
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

    /**
     * Import SQL in MySQL da
     *
     * @param string $sqlFilePath Path of the SQL file to import
     */
    public static function mySqlImport($sqlFilePath)
    {
        global $CONFIG;
        shell_exec('mysql --host=' . $CONFIG['db']['host'] . ' --port=' . $CONFIG['db']['port'] . ' --user=' . $CONFIG['db']['username'] . ' --password=' . $CONFIG['db']['password'] . ' ' . $CONFIG['db']['dbname'] . ' < ' . $sqlFilePath . ' > /dev/null 2>&1');
    }
}
