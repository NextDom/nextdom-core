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

namespace NextDom\Managers;

use NextDom\Exceptions\CoreException;

require_once NEXTDOM_ROOT.'/core/class/cache.class.php';

class BackupManager {
    /**
     * Lancer une sauvegarde du système
     *
     * @param bool $taskInBackground Lancer la sauvegarde en tâche de fond.
     */
    public static function backup(bool $taskInBackground = false)
    {
        if ($taskInBackground) {
            \log::clear('backup');
            $cmd = NEXTDOM_ROOT . '/install/backup.php';
            $cmd .= ' >> ' . \log::getPathToLog('backup') . ' 2>&1 &';
            \system::php($cmd, true);
        } else {
            require_once NEXTDOM_ROOT . '/install/backup.php';
        }
    }

    /**
     * Obtenir la liste des sauvegardes
     *
     * @return array Liste des sauvegardes
     */
    public static function listBackup(): array
    {
        if (substr(\config::byKey('backup::path'), 0, 1) != '/') {
            $backup_dir = NEXTDOM_ROOT . '/' . \config::byKey('backup::path');
        } else {
            $backup_dir = \config::byKey('backup::path');
        }
        $backups = \ls($backup_dir, '*.tar.gz', false, array('files', 'quiet', 'datetime_asc'));
        $result = array();
        foreach ($backups as $backup) {
            $result[$backup_dir . '/' . $backup] = $backup;
        }
        return $result;
    }

    /**
     * Supprimer une sauvegarde
     *
     * @param string $backupFilePath Chemin du fichier de sauvegarde
     *
     * @throws CoreException
     */
    public static function removeBackup(string $backupFilePath)
    {
        if (file_exists($backupFilePath)) {
            unlink($backupFilePath);
        } else {
            throw new CoreException(__('Impossible de trouver le fichier : ') . $backupFilePath);
        }
    }

    /**
     * Restaure une sauvegarde
     *
     * @param string $backupFilePath Chemin de la sauvegarde
     *
     * @param bool $taskInBackground Lancer en tâche de fond
     */
    public static function restore(string $backupFilePath = '', bool $taskInBackground = false)
    {
        if ($taskInBackground) {
            \log::clear('restore');
            $cmd = NEXTDOM_ROOT . '/install/restore.php "backup=' . $backupFilePath . '"';
            $cmd .= ' >> ' . \log::getPathToLog('restore') . ' 2>&1 &';
            \system::php($cmd, true);
        } else {
            global $BACKUP_FILE;
            $BACKUP_FILE = $backupFilePath;
            require_once NEXTDOM_ROOT . '/install/restore.php';
        }
    }
}
