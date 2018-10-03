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

function show401Error() {
    header("HTTP/1.1 401 Unauthorized");
    die();
}

try {
    require_once __DIR__ . '/../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');
    if (!isConnect() && !nextdom::apiAccess(init('apikey'))) {
        show401Error();
    }
    unautorizedInDemo();
    $pathfile = realpath(calculPath(urldecode(init('pathfile'))));
    if ($pathfile === false) {
        show401Error();
    }
    if (strpos($pathfile, '.php') !== false) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }
    $rootPath = realpath(__DIR__ . '/../../');
    if (strpos($pathfile, $rootPath) === false) {
        if (config::byKey('recordDir', 'camera') != '' && substr(config::byKey('recordDir', 'camera'), 0, 1) == '/') {
            $cameraPath = realpath(config::byKey('recordDir', 'camera'));
            if (strpos($pathfile, $cameraPath) === false) {
                show401Error();
            }
        } else {
            show401Error();
        }
    }
    if (!isConnect('admin')) {
        $adminFiles = array('log', 'backup', '.sql', 'scenario', '.tar', '.gz');
        foreach ($adminFiles as $adminFile) {
            if (strpos($pathfile, $adminFile) !== false) {
                show401Error();
            }
        }
    }
    if (strpos($pathfile, '*') === false) {
        if (!file_exists($pathfile)) {
            throw new Exception(__('Fichier non trouvé : ', __FILE__) . $pathfile);
        }
    } elseif (is_dir(str_replace('*', '', $pathfile))) {
        if (!isConnect('admin')) {
            show401Error();
        }
        system('cd ' . dirname($pathfile) . ';tar cfz ' . nextdom::getTmpFolder('downloads') . '/archive.tar.gz * > /dev/null 2>&1');
        $pathfile = nextdom::getTmpFolder('downloads') . '/archive.tar.gz';
    } else {
        if (!isConnect('admin')) {
            show401Error();
        }
        $pattern = array_pop(explode('/', $pathfile));
        system('cd ' . dirname($pathfile) . ';tar cfz ' . nextdom::getTmpFolder('downloads') . '/archive.tar.gz ' . $pattern . '> /dev/null 2>&1');
        $pathfile = nextdom::getTmpFolder('downloads') . '/archive.tar.gz';
    }
    $path_parts = pathinfo($pathfile);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $path_parts['basename']);
    readfile($pathfile);
    if (file_exists(nextdom::getTmpFolder('downloads') . '/archive.tar.gz')) {
        unlink(nextdom::getTmpFolder('downloads') . '/archive.tar.gz');
    }
    exit;
} catch (Exception $e) {
    echo $e->getMessage();
}
