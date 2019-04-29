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


require_once __DIR__ . '/../core/php/core.inc.php';

use NextDom\Managers\BackupManager;
use NextDom\Helpers\Utils;
use NextDom\Helpers\SystemHelper;

$args = Utils::parseArgs($argv);
if (true == array_key_exists("help", $args)) {
    echo "usage: php restoreBackup.php [help] [file=path-to-archive]";
    die(1);
}

$file         = Utils::array_key_default($args, "file", "");
$currentUser  = posix_getpwuid(posix_geteuid());
$expectedUser = SystemHelper::getWWWUid();

if ($currentUser["name"] != $expectedUser) {
    printf("error: script must be ran by '%s'");
    printf(" -> sudo -u %s php %s file=%s", $expectedUser, $argv[0], $file);
    die(1);
}

BackupManager::restoreBackup($file);
