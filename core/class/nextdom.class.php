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

require_once NEXTDOM_ROOT.'/core/php/core.inc.php';

use NextDom\Helpers\Api;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Helpers\TimeLineHelper;
use NextDom\Managers\BackupManager;
use NextDom\Managers\DevicesManager;

class nextdom
{
    public static function addTimelineEvent($event)
    {
        TimeLineHelper::addTimelineEvent($event);
    }

    public static function getTimelineEvent(): array
    {
        return TimeLineHelper::getTimelineEvent();
    }

    public static function removeTimelineEvent()
    {
        TimeLineHelper::removeTimelineEvent();
    }

    public static function addRemoveHistory($data)
    {
        NextDomHelper::addRemoveHistory($data);
    }

    public static function deadCmd()
    {
        return NextDomHelper::getDeadCmd();
    }

    public static function health(): array
    {
        return NextDomHelper::health();
    }

    public static function sick()
    {
        NextDomHelper::sick();
    }

    public static function getApiKey(string $plugin = 'core'): string
    {
        return Api::getApiKey($plugin);
    }

    public static function apiModeResult(string $mode = 'enable'): bool
    {
        return Api::apiModeResult($mode);
    }

    public static function apiAccess(string $defaultApiKey = '', string $plugin = 'core')
    {
        return Api::apiAccess($defaultApiKey, $plugin);
    }

    public static function isOk()
    {
        return NextDomHelper::isOk();
    }

    public static function getUsbMapping($name = '', $getGPIO = false)
    {
        return DevicesManager::getUsbMapping($name, $getGPIO);
    }

    public static function getBluetoothMapping($name = '')
    {
        return DevicesManager::getBluetoothMapping($name);
    }

    public static function backup(bool $taskInBackground = false)
    {
        BackupManager::backup($taskInBackground);
    }

    public static function listBackup(): array
    {
        return BackupManager::listBackup();
    }

    public static function removeBackup(string $backupFilePath)
    {
        BackupManager::removeBackup($backupFilePath);
    }

    public static function restore(string $backupFilePath = '', bool $taskInBackground = false)
    {
        BackupManager::restore($backupFilePath, $taskInBackground);
    }

    public static function update($options = [])
    {
        NextDomHelper::update($options);
    }

    public static function getConfiguration(string $askedKey = '', $defaultValue = false)
    {
        return NextDomHelper::getConfiguration($askedKey, $defaultValue);
    }

    public static function version()
    {
        return NextDomHelper::getJeedomVersion();
    }

    public static function stop()
    {
        NextDomHelper::stopSystem(false);
    }

    public static function start()
    {
        NextDomHelper::startSystem();
    }

    public static function isStarted(): bool
    {
        return NextDomHelper::isStarted();
    }

    public static function isDateOk()
    {
        return NextDomHelper::isDateOk();
    }

    public static function event($event, $forceSyncMode = false)
    {
        NextDomHelper::event($event, $forceSyncMode);
    }

    public static function cron10()
    {
        NextDomHelper::cron10();
    }

    public static function cron5()
    {
        NextDomHelper::cron5();
    }

    public static function cron()
    {
        NextDomHelper::cron();
    }

    public static function cronDaily()
    {
        NextDomHelper::cronDaily();
    }

    public static function cronHourly()
    {
        NextDomHelper::cronHourly();
    }

    public static function replaceTag(array $_replaces)
    {
        NextDomHelper::replaceTag($_replaces);
    }

    public static function checkOngoingThread(string $cmd): string
    {
        return NextDomHelper::checkOngoingThread($cmd);
    }

    public static function retrievePidThread(string $cmd)
    {
        return NextDomHelper::retrievePidThread($cmd);
    }

    public static function versionAlias($version, bool $lightMode = true)
    {
        return NextDomHelper::versionAlias($version, $lightMode);
    }

    public static function toHumanReadable($input)
    {
        return NextDomHelper::toHumanReadable($input);
    }

    public static function fromHumanReadable($input)
    {
        return NextDomHelper::fromHumanReadable($input);
    }

    public static function evaluateExpression($input, $scenario = null)
    {
        return NextDomHelper::evaluateExpression($input, $scenario);
    }

    public static function calculStat($calcul, $values)
    {
        return NextDomHelper::calculStat($calcul, $values);
    }

    public static function getTypeUse($_string = '')
    {
        return NextDomHelper::getTypeUse($_string);
    }

    public static function haltSystem()
    {
        NextDomHelper::haltSystem();
    }

    public static function rebootSystem()
    {
        NextDomHelper::rebootSystem();
    }

    public static function forceSyncHour()
    {
        NextDomHelper::forceSyncHour();
    }

    public static function checkSpaceLeft(): float
    {
        return NextDomHelper::checkSpaceLeft();
    }

    public static function getTmpFolder($_plugin = null) {
        return NextDomHelper::getTmpFolder($_plugin);
    }

    public static function getHardwareKey()
    {
        return NextDomHelper::getHardwareKey();
    }

    public static function getHardwareName()
    {
        return NextDomHelper::getHardwareName();
    }

    public static function isCapable($systemFunc, $forceRefresh = false)
    {
        return NextDomHelper::isCapable($systemFunc, $forceRefresh);
    }

    public static function benchmark()
    {
        return NextDomHelper::benchmark();
    }

    public static function cleanFileSytemRight() {
        // no operation (jeedom compatibility)
    }

    public static function consistency() {
        SystemHelper::consistency();
    }
}
