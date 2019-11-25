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

use Icewind\SMB\System;
use NextDom\Helpers\MigrationHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\ScriptHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\BackupManager;
use NextDom\Managers\ConsistencyManager;
use NextDom\Managers\EventManager;
use NextDom\Managers\MessageManager;
use NextDom\Managers\UpdateManager;

require_once(__DIR__ . '/../src/core.php');

$versionBeforeUpdate = '';
$versionAfterUpdate = '';

/**
 * Launch update with git
 *
 * @return True on success
 *
 * @throws \Exception
 */
function gitUpdate()
{
    // Ignore file mode change
    exec(SystemHelper::getCmdSudo() . 'git config core.fileMode false');

    // Update git
    echo __('install.update-sourcecode') . ' : ';
    exec('cd ' . NEXTDOM_ROOT . ' && ' . SystemHelper::getCmdSudo() . 'git pull', $gitPullResult, $gitPullReturn);
    if ($gitPullReturn === 0) {
        echo __('common.ok') . "\n";
        if (count($gitPullResult) > 0 && $gitPullResult[0] === 'Already up-to-date.') {
            echo __('install.already-updated') . "\n";
            //return false;
        }
    } else {
        echo __('common.nok') . "\n";
        echo __('install.update-core-error');
        return false;
    }

    echo __('install.download-dependencies') . ' : ';
    $downloadDependencies = 0;
    exec('cd ' . NEXTDOM_ROOT . ' && ' . SystemHelper::getCmdSudo() . './scripts/gen_composer_npm.sh >> /dev/null 2>&1', $output, $downloadDependencies);
    if ($downloadDependencies === 0) {
        echo __('common.ok') . "\n";
    } else {
        echo __('common.nok') . "\n";
        return false;
    }

    echo __('install.gen-assets') . ' : ';
    $genAssetsReturn = 0;
    exec('cd ' . NEXTDOM_ROOT . ' && ' . SystemHelper::getCmdSudo() . './scripts/gen_assets.sh >> /dev/null 2>&1', $output, $genAssetsReturn);
    if ($genAssetsReturn === 0) {
        echo __('common.ok') . "\n";
    } else {
        echo __('common.nok') . "\n";
        return false;
    }
    $coreUpdate = UpdateManager::byTypeAndLogicalId('core', 'nextdom');
    if (is_object($coreUpdate)) {
        exec('git rev-parse HEAD', $gitHash);
        $coreUpdate->setLocalVersion($gitHash[0]);
        exec('git rev-parse --abbrev-ref HEAD', $branch);
        $coreUpdate->setConfiguration('version', $branch[0]);
        $coreUpdate->save();
    }
    return true;
}

/**
 * Launch update via apt
 * @throws \Exception
 */
function debianUpdate()
{
    exec(SystemHelper::getCmdSudo() . 'apt update > /dev/null 2>&1');
    exec(SystemHelper::getCmdSudo() . 'apt-get install -y nextdom');
}

/**
 * Start core update
 */
function coreUpdate()
{
    // Test type of installation
    $gitInstall = is_dir(NEXTDOM_ROOT . '/.git');

    // Begin process
    NextDomHelper::stopSystem(false);
    try {
        if ($gitInstall) {
            gitUpdate();
        } else {
            debianUpdate();
        }
    } catch (\Throwable $e) {

    }

    MigrationHelper::migrate('migration');

    ConsistencyManager::checkConsistency();

    NextDomHelper::startSystem();

    UpdateManager::checkAllUpdate('core', false);
}

/**
 * Start plugins update
 *
 * @throws \Exception
 */
function pluginsUpdate()
{
    UpdateManager::updateAll();
}

/**
 * Test if update is in progress
 *
 * @return bool True if update is in progress
 *
 * @throws \Exception
 */
function updateInProgress() {
    if (count(SystemHelper::ps('install/update.php', 'sudo')) > 1) {
        echo __('install.update-in-progress-wait') . "\n";
        sleep(10);
        if (count(SystemHelper::ps('install/update.php', 'sudo')) > 1) {
            echo __('install.update-in-progress-retry') . "\n";
            return true;
        }
    }
    return false;
}

ScriptHelper::cliOrCrash();
ScriptHelper::parseArgumentsToGET();

set_time_limit(1800);

if (!updateInProgress()) {
    $processPluginsUpdate = Utils::init('plugins', 0) == '1';
    $processCoreUpdate = Utils::init('core', 0) == '1';

    if ($processCoreUpdate || $processPluginsUpdate) {
        echo "[" . __('install.begin-update') . "]\n";
        NextDomHelper::event('begin_update', true);
        // Backup before depend of user choice
        if (Utils::init('backup::before')) {
            BackupManager::createBackup();
        }

        if ($processPluginsUpdate) {
            pluginsUpdate();
        }

        if ($processCoreUpdate) {
            coreUpdate();
        }
        NextDomHelper::event('end_update');
        echo "[" . __('install.end-update') . "]\n";
    }

}
