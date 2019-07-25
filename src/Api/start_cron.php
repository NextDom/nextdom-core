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

/**
 * Execute one or all tasks
 *
 * Usage :
 *  - start_cron.php [ cron_id=CRON_ID ]
 *
 * Without arguments, all tasks will be executed.
 *
 * Parameters :
 *  - CRON_ID : Id of the task to execute
 */

namespace NextDom;

use NextDom\Enums\CronState;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\ScriptHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\CronManager;
use NextDom\Model\Entity\Cron;

const MAX_DURATION_TIME = 59;
const GARBAGE_COLLECTOR_LIMIT = 30;

require_once __DIR__ . "/../../src/core.php";

ScriptHelper::cliOrCrash();
ScriptHelper::parseArgumentsToGET();

/**
 * Set cron in error and stop the execution of the script
 *
 * @param Cron $cron Cron object
 * @param string $msg Message to log
 * @param int $startTime Start time of the cron
 * @throws \Exception
 */
function setCronErrorAndDie($cron, $msg, $startTime)
{
    $cron->setState(CronState::NOT_FOUND);
    $cron->setPID();
    $cron->setCache('runtime', strtotime('now') - $startTime);
    LogHelper::addError('cron', __($msg) . $cron->getName());
    die();
}

/**
 * Set cron error on exception
 *
 * @param Cron $cron Cron object
 * @param \Exception $e Exception informations
 * @param string $logChannel Target log channel
 * @param int $startTime Start time of the cron
 * @throws \Exception
 */
function setCronErrorOnException($cron, $e, $logChannel, $startTime)
{
    $cron->setState('error');
    $cron->setPID('');
    $cron->setCache('runtime', strtotime('now') - $startTime);
    $logicalId = ConfigManager::genKey();
    if ($e->getCode() != 0) {
        $logicalId = $cron->getName() . '::' . $e->getCode();
    }
    echo __('common.error-b') . $cron->getName() . ' : ' . LogHelper::exception($e);
    LogHelper::addError($logChannel, __('scripts.error-on') . $cron->getName() . ' : ' . LogHelper::exception($e), $logicalId);
}

/**
 * Start cron where the target is a class method
 *
 * @param Cron $cron Cron object
 * @param array $option Execution option
 * @param int $startTime Start time.
 * @throws \Exception
 */
function startCronTargetMethod($cron, $option, $startTime)
{
    $classToCall = '';
    try {
        $classToCall = $cron->getClass();
        $methodToCall = $cron->getFunction();
        if (class_exists($classToCall) && method_exists($classToCall, $methodToCall)) {
            if ($cron->getDeamon() == 0) {
                if ($option !== null) {
                    $classToCall::$methodToCall($option);
                } else {
                    $classToCall::$methodToCall();
                }
            } else {
                $gc = 0;
                while (true) {
                    $cycleStartTime = getmicrotime();
                    if ($option !== null) {
                        $classToCall::$methodToCall($option);
                    } else {
                        $classToCall::$methodToCall();
                    }
                    $gc++;
                    if ($gc > GARBAGE_COLLECTOR_LIMIT) {
                        gc_collect_cycles();
                        $gc = 0;
                    }
                    if ($cron->getDeamonSleepTime() > 1) {
                        sleep($cron->getDeamonSleepTime());
                    } else {
                        $cycleDuration = getmicrotime() - $cycleStartTime;
                        if ($cycleDuration < $cron->getDeamonSleepTime()) {
                            usleep(round(($cron->getDeamonSleepTime() - $cycleDuration) * 1000000));
                        }
                    }
                }
            }
        } else {
            setCronErrorAndDie($cron, 'scripts.cron-class-or-function-not-found', $startTime);
        }
    } catch (\Exception $e) {
        setCronErrorOnException($cron, $e, $classToCall, $startTime);
    }
}

/**
 * Start cron where the target is a global function
 *
 * @param Cron $cron Cron object
 * @param array $option Execution option
 * @param int $startTime Start time.
 * @throws \Exception
 */
function startCronTargetFunction($cron, $option, $startTime)
{
    $functionToCall = '';
    try {
        $functionToCall = $cron->getFunction();
        if (function_exists($functionToCall)) {
            if ($cron->getDeamon() == 0) {
                if ($option !== null) {
                    $functionToCall($option);
                } else {
                    $functionToCall();
                }
            } else {
                $gc = 0;
                while (true) {
                    $cycleStartTime = getmicrotime();
                    if ($option !== null) {
                        $functionToCall($option);
                    } else {
                        $functionToCall();
                    }
                    $gc++;
                    if ($gc > GARBAGE_COLLECTOR_LIMIT) {
                        gc_collect_cycles();
                        $gc = 0;
                    }
                    $cycleDuration = getmicrotime() - $cycleStartTime;
                    if ($cron->getDeamonSleepTime() > 1) {
                        sleep($cron->getDeamonSleepTime());
                    } else {
                        if ($cycleDuration < $cron->getDeamonSleepTime()) {
                            usleep(round(($cron->getDeamonSleepTime() - $cycleDuration) * 1000000));
                        }
                    }
                }
            }
        } else {
            setCronErrorAndDie($cron, __('common.error-b') . __('common.not-found'), $startTime);
        }
    } catch (\Exception $e) {
        setCronErrorOnException($cron, $e, $functionToCall, $startTime);
    }
}

/**
 * Start single cron job
 *
 * @param int $cronId Id of the cron
 * @throws CoreException
 * @throws \ReflectionException
 */
function startSingleCron($cronId)
{
    if (NextDomHelper::isStarted() && ConfigManager::byKey('enableCron', 'core', 1, true) == 0) {
        die(__('scripts.cron-disable'));
    }
    $cron = CronManager::byId($cronId);
    if (!is_object($cron)) {
        die();
    }
    $datetime = date('Y-m-d H:i:s');
    $startTime = strtotime('now');

    $cron->setState('run');
    $cron->setPID(getmypid());
    $cron->setLastRun($datetime);
    $option = $cron->getOption();
    if ($cron->getClass() != '') {
        startCronTargetMethod($cron, $option, $startTime);
    } else {
        startCronTargetFunction($cron, $option, $startTime);
    }
    if ($cron->getOnce() == 1) {
        $cron->remove(false);
    } else {
        if ($cron->refresh()) {
            $cron->setState(CronState::STOP);
            $cron->setPID();
            $cron->setCache('runtime', strtotime('now') - $startTime);
        }
    }
}

/**
 * Start all crons jobs
 */
function startAllCrons()
{
    if (CronManager::jeeCronRun()) {
        die();
    }
    $started = NextDomHelper::isStarted();

    set_time_limit(MAX_DURATION_TIME);
    CronManager::setPidFile();

    if ($started && ConfigManager::byKey('enableCron', 'core', 1, true) == 0) {
        die(__('scripts.cron-disable'));
    }
    foreach (CronManager::all() as $cron) {
        try {
            if ($cron->getDeamon() == 1) {
                $cron->refresh();
                continue;
            }
            if (!$started && $cron->getClass() != 'nextdom' && $cron->getFunction() != 'cron') {
                continue;
            }
            if (!$cron->refresh()) {
                continue;
            }
            $duration = strtotime('now') - strtotime($cron->getLastRun());
            if ($cron->isEnabled() && $cron->getState() != CronState::RUN && $cron->getState() != CronState::STARTING && $cron->getState() != CronState::STOPPING) {
                if ($cron->isDue()) {
                    $cron->start();
                }
            }
            // Stop cron task if timeout is reached
            if ($cron->getState() == CronState::RUN && ($duration / 60) >= $cron->getTimeout()) {
                $cron->stop();
            }
            switch ($cron->getState()) {
                case CronState::STARTING:
                    $cron->run();
                    break;
                case CronState::STOPPING:
                    $cron->halt();
                    break;
            }
        } catch (\Exception $e) {
            if ($cron->getOnce() != 1) {
                $cron->setState('error');
                $cron->setPID('');
                LogHelper::addError('cron', __('common.error-b') . $cron->getName() . ' : ' . $e->getMessage());
            }
        }
    }
}

/**
 * Entry point
 */
$cronId = Utils::init('cron_id');
if ($cronId != '') {
    startSingleCron($cronId);
} else {
    startAllCrons();
}
