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

namespace NextDom;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Api;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Router;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;

try {
    require_once __DIR__ . "/../../src/core.php";
    AuthentificationHelper::init();

    // Access for authenticated user or by API key
    if (!AuthentificationHelper::isConnected() && !Api::apiAccess(Utils::init('apikey'))) {
        Router::showError401AndDie();
    }

    $baseFilePath = Utils::init('pathfile');

    if (strpos($baseFilePath, 'log') === 0) {
        $filePath = realpath(NEXTDOM_LOG . '/' . substr($baseFilePath, 4));
    } elseif (strpos($baseFilePath, 'data') === 0) {
        $filePath = realpath(NEXTDOM_DATA . '/' . $baseFilePath);
    }

    // Bad path
    if ($filePath === false || !Utils::checkPath($filePath)) {
        Router::showError401AndDie();
    }

    // Block PHP files download
    if (strpos($filePath, '.php') !== false) {
        Router::showError401AndDie();
    }

    // Block some kind of files for non-admin users
    if (!AuthentificationHelper::isConnectedWithRights('admin')) {
        $adminFiles = ['backup', '.sql', 'scenario', '.tar', '.gz'];
        foreach ($adminFiles as $adminFile) {
            if (strpos($filePath, $adminFile) !== false) {
                Router::showError401AndDie();
            }
        }
    }

    // Special access
    if (strpos($filePath, NEXTDOM_LOG) === false && strpos($filePath, NEXTDOM_DATA . '/data') === false) {
        // For camera
        $cameraPath = ConfigManager::byKey('recordDir', 'camera');
        if ($cameraPath != '' && substr($cameraPath, 0, 1) == '/') {
            $cameraPath = realpath($cameraPath);
            if (strpos($filePath, $cameraPath) === false) {
                Router::showError401AndDie();
            }
            // Backups
        } elseif (strpos($filePath, NEXTDOM_DATA . '/backup') === false) {
            Router::showError401AndDie();
        }
    }

    $archivePath = NextDomHelper::getTmpFolder('downloads') . '/archive.tar.gz';

    if (strpos($filePath, '*') === false) {
        // Download single file
        if (!file_exists($filePath)) {
            throw new CoreException(__('scripts.file-not-found') . $filePath);
        }
    } elseif (is_dir(str_replace('*', '', $filePath))) {
        // Download directory content
        if (!isConnect('admin')) {
            Router::showError401AndDie();
        }
        system('cd ' . dirname($filePath) . ';tar cfz ' . $archivePath . ' * > /dev/null 2>&1');
        $filePath = $archivePath;
    } else {
        if (!isConnect('admin')) {
            Router::showError401AndDie();
        }
        $pattern = array_pop(explode('/', $filePath));
        system('cd ' . dirname($filePath) . ';tar cfz ' . $archivePath . ' ' . $pattern . '> /dev/null 2>&1');
        $filePath = $archivePath;
    }

    $pathParts = pathinfo($filePath);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $pathParts['basename']);
    readfile($filePath);

    if (file_exists($archivePath)) {
        unlink($archivePath);
    }
    exit;
} catch (\Throwable $t) {
    echo $t->getMessage();
}
