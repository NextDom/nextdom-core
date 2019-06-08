<?php

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

/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Repo;

use NextDom\Helpers\Samba;
use NextDom\Managers\BackupManager;
use NextDom\Managers\ConfigManager;

class RepoSamba
{
    public static $_name = 'Samba';

    public static $_scope = array(
        'plugin' => true,
        'backup' => true,
        'hasConfiguration' => true,
        'core' => true,
    );

    public static $_configuration = array(
        'parameters_for_add' => array(
            'path' => array(
                'name' => 'Chemin',
                'type' => 'input',
            ),
        ),
        'configuration' => array(
            'backup::ip' => array(
                'name' => '[Backup] IP',
                'type' => 'input',
                'placeholder' => '192.168.1.57 or mafreebox.free.fr',
            ),
            'backup::username' => array(
                'name' => '[Backup] Utilisateur',
                'type' => 'input',
                'placeholder' => 'my-samba-user',
            ),
            'backup::password' => array(
                'name' => '[Backup] Mot de passe',
                'type' => 'password',
            ),
            'backup::share' => array(
                'name' => '[Backup] Partage',
                'type' => 'input',
                'placeholder' => 'share-name',
            ),
            'backup::folder' => array(
                'name' => '[Backup] Chemin',
                'type' => 'input',
                'placeholder' => '/example/path/',
            ),
        ),
    );

    public static function backup_send($path)
    {
        $backupFolder = ConfigManager::byKey('samba::backup::folder');
        $pathinfo = pathinfo($path);
        $filename = Samba::cleanName($pathinfo['basename']);
        $backupDest = sprintf("%s/%s", $backupFolder, $filename);

        $samba = Samba::createFromConfig("backup");
        $samba->put($path, $backupDest);
        self::cleanBackups();
    }

    private static function cleanBackups()
    {
        $backupFolder = ConfigManager::byKey('samba::backup::folder');
        $maxMtime = strtotime(sprintf('- %s days', ConfigManager::byKey('backup::keepDays')));

        $samba = Samba::createFromConfig("backup");
        $files = $samba->getFiles($backupFolder);
        foreach ($files as $c_file) {
            if ($c_file->getMTime() < $maxMtime) {
                $samba->del($c_file->getPath());
            }
        }
    }

    public static function backup_list()
    {
        $result = [];
        $backupFolder = ConfigManager::byKey('samba::backup::folder');

        $samba = Samba::createFromConfig("backup");
        $files = $samba->getFiles($backupFolder, "mtime", "desc");
        foreach ($files as $c_file) {
            $result[] = $c_file->getName();
        }
        return $result;
    }

    public static function backup_restore($file)
    {
        $backupDir = BackupManager::getBackupDirectory();
        $backupFile = sprintf("%s/%s", $backupDir, $file);

        $samba = Samba::createFromConfig("backup");
        $samba->get($file, $backupFile);
        BackupManager::restore($backupFile, true);
    }
}
