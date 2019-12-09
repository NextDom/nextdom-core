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

use NextDom\Helpers\LogHelper;
use NextDom\Helpers\Samba;
use NextDom\Interfaces\BaseRepo;
use NextDom\Managers\BackupManager;
use NextDom\Managers\ConfigManager;

/**
 * Access to samba functionality
 *
 * @package NextDom\Repo
 */
class RepoSamba implements BaseRepo
{
    /**
     * @var string General name
     */
    public static $_name = 'Samba';
    public static $_icon = 'fas fa-server';
    public static $_description = 'repo.samba.description';

    /**
     * @var array Availability
     */
    public static $_scope = [
        'plugin' => true,
        'backup' => true,
        'hasConfiguration' => true,
        'core' => true,
    ];

    /**
     * @var array Configuration fields
     */
    public static $_configuration = [
        'parameters_for_add' => [
            'path' => [
                'name' => 'Chemin',
                'type' => 'input',
            ],
        ],
        'configuration' => [
            'backup::ip' => [
                'name' => 'repo.samba.conf.ip.name',
                'type' => 'input',
                'placeholder' => 'repo.samba.conf.ip.placeholder',
            ],
            'backup::username' => [
                'name' => 'repo.samba.conf.user.name',
                'type' => 'input',
                'placeholder' => 'repo.samba.conf.user.placeholder',
            ],
            'backup::password' => [
                'name' => 'repo.samba.conf.password',
                'type' => 'password',
            ],
            'backup::share' => [
                'name' => 'repo.samba.conf.share.name',
                'type' => 'input',
                'placeholder' => 'repo.samba.conf.share.placeholder',
            ],
            'backup::folder' => [
                'name' => 'repo.samba.conf.folder.name',
                'type' => 'input',
                'placeholder' => 'repo.samba.conf.folder.placeholder',
            ],
        ],
    ];

    /**
     * Send backup to the share
     *
     * @param string $backupPath Path of the backup
     *
     * @throws \Exception
     */
    public static function backup_send($backupPath)
    {
        $backupFolder = ConfigManager::byKey('samba::backup::folder');
        $pathinfo = pathinfo($backupPath);
        $filename = Samba::cleanName($pathinfo['basename']);
        $backupDest = $backupFolder . '/' . $filename;

        $sambaConnection = Samba::createFromConfig('backup');
        $sambaConnection->put($backupPath, $backupDest);
        LogHelper::addInfo("system", "Backup to remote samba server done.");
        self::cleanBackups();
    }

    /**
     * Remove old backups on the server
     *
     * @throws \Exception
     */
    private static function cleanBackups()
    {
        $backupConfig = ConfigManager::byKeys(['samba::backup::folder', 'backup::keepDays']);
        $maxMtime = strtotime('- ' . $backupConfig['backup::keepDays'] . ' days');

        $sambaConnection = Samba::createFromConfig('backup');
        $folderContent = $sambaConnection->getFiles($backupConfig['samba::backup::folder']);
        foreach ($folderContent as $currentFile) {
            if ($currentFile->getMTime() < $maxMtime) {
                $sambaConnection->del($currentFile->getPath());
            }
        }
    }

    /**
     * Get list of backups
     *
     * @return array List of backups
     *
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function backup_list()
    {
        $result = [];
        $backupFolder = ConfigManager::byKey('samba::backup::folder');

        $sambaConnection = Samba::createFromConfig("backup");
        $folderContent = $sambaConnection->getFiles($backupFolder, "mtime", "desc");
        foreach ($folderContent as $currentFile) {
            $result[] = $currentFile->getName();
        }
        return $result;
    }

    /**
     * Start restore process
     *
     * @param string $file Selected restore file
     *
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function backup_restore($file)
    {
        $backupDir = BackupManager::getBackupDirectory();
        $backupFile = $backupDir . '/' . $file;

        $sambaConnection = Samba::createFromConfig("backup");
        $backupFolder = ConfigManager::byKey('samba::backup::folder');
        $sambaConnection->get($backupFolder . '/' . $file, $backupFile);
        BackupManager::restore($backupFile, true);
    }
}
