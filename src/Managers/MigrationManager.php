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
use NextDom\Helpers\LogHelper;

class MigrationManager
{
    /**
     * Migration to pass during migrate_themes_2_data
     *
     * @throws \Exception
     */
    public static function migrate_themes_2_data()
    {

        LogHelper::clear('migration');
        LogHelper::addInfo('migration','Start moving files and folders process to '.NEXTDOM_DATA,'');
        $directories = glob(NEXTDOM_ROOT . '/*' , GLOB_ONLYDIR);

        foreach ($directories as $directory) {
            if (!FoldersReferential::NEXTDOMFOLDERS.contains($directory,false)) {
                LogHelper::addInfo('migration','moving : '.$directory.' to : '.NEXTDOM_DATA,'');
                rename($directory, NEXTDOM_DATA . "/$directory");
            }
        }

        LogHelper::addInfo('migration','Start updating database process to plan table','');
    }
}
