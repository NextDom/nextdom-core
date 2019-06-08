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

require_once __DIR__ . "/../../src/core.php";

use NextDom\Helpers\FileSystemHelper;

echo '<link rel="stylesheet" href="/vendor/node_modules/@fortawesome/fontawesome-free/css/all.css">' . "\n";
echo '<link rel="stylesheet" href="/vendor/node_modules/font-awesome/css/font-awesome.css">' . "\n";

$iconsRootDirectory = NEXTDOM_ROOT . '/public/icon/';

foreach (FileSystemHelper::ls($iconsRootDirectory, '*') as $dir) {
    if (is_dir($iconsRootDirectory . $dir) && file_exists($iconsRootDirectory . $dir . '/style.css')) {
        echo '<link rel="stylesheet" href="/public/icon/' . $dir . 'style.css?md5=' . md5($iconsRootDirectory . $dir . '/style.css') . '">' . "\n";
    }
}
