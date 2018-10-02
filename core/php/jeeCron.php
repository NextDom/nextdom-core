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

use NextDom\Helpers\Utils;

const MAX_DURATION_TIME = 59;
const GARBAGE_COLLECTOR_LIMIT = 30;

// Check if the script as started from command line
if (php_sapi_name() != 'cli' || isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['argc'])) {
    header("Statut: 404 Page non trouvée");
    header('HTTP/1.0 404 Not Found');
    $_SERVER['REDIRECT_STATUS'] = 404;
    echo "<h1>404 Non trouvé</h1>";
    echo "La page que vous demandez ne peut être trouvée.";
    exit();
}

// Command line args are stored in $_GET var
// php jeeCron.php test=1 > $_GET['test'] = 1
if (isset($argv)) {
    foreach ($argv as $arg) {
        $argList = explode('=', $arg);
        if (isset($argList[0]) && isset($argList[1])) {
            $_GET[$argList[0]] = $argList[1];
        }
    }
}

require_once __DIR__ . "/core.inc.php";

/**
 * Set cron in error and stop the execution of the script
 *
 * @param \cron $cron Cron object
 * @param string $msg Message to log
 * @param int $startTime Start time of the cron
 */
function setCronErrorAndDie($cron, $msg, $startTime)
{
    $cron->setState('Not found');
    $cron->setPID();
    $cron->setCache('runtime', strtotime('now') - $startTime);
    log::add('cron', 'error', __($msg) . $cron->getName());
    die();
}

/**
 * Set cron error on exception
 *
 * @param \cron $cron Cron object
 * @param Exception $e Exception informations
 * @param string $logChannel Target log channel
 * @param int $startTime Start time of the cron
 */
function setCronErrorOnException($cron, $e, $logChannel, $startTime) {
    $cron->setState('error');
    $cron->setPID('');
    $cron->setCache('runtime', strtotime('now') - $startTime);
    $logicalId = config::genKey();
    if ($e->getCode() != 0) {
        $logicalId = $cron->getName() . '::' . $e->getCode();
    }
    echo '[Erreur] ' . $cron->getName() . ' : ' . log::exception($e);
    log::add($logChannel, 'error', __('Erreur sur ') . $cron->getName() . ' : ' . log::exception($e), $logicalId);
}

/**
 * Start cron where the target is a class method
 *
 * @param \cron $cron Cron object
 * @param array $option Execution option
 * @param int $startTime Start time.
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
            setCronErrorAndDie($cron, '[Erreur] Classe ou fonction non trouvée ', $startTime);
        }
    } catch (Exception $e) {
        setCronErrorOnException($cron, $e, $classToCall, $startTime);
    }
}

/**
 * Start cron where the target is a global function
 *
 * @param \cron $cron Cron object
 * @param array $option Execution option
 * @param int $startTime Start time.
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
            setCronErrorAndDie($cron, '[Erreur] Non trouvée ', $startTime);
        }
    } catch (Exception $e) {
        setCronErrorOnException($cron, $e, $functionToCall, $startTime);
    }
}

/**
 * Start single cron job
 *
 * @param int $cronId Id of the cron
 */
function startSingleCron($cronId)
{
    if (nextdom::isStarted() && config::byKey('enableCron', 'core', 1, true) == 0) {
        die(__('Tous les crons sont actuellement désactivés'));
    }
    $cron = cron::byId($cronId);
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
            $cron->setState('stop');
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
    if (cron::jeeCronRun()) {
        die();
    }
    $started = nextdom::isStarted();

    set_time_limit(MAX_DURATION_TIME);
    cron::setPidFile();

    if ($started && config::byKey('enableCron', 'core', 1, true) == 0) {
        die(__('Tous les crons sont actuellement désactivés'));
    }
    foreach (cron::all() as $cron) {
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
            if ($cron->getEnable() == 1 && $cron->getState() != 'run' && $cron->getState() != 'starting' && $cron->getState() != 'stoping') {
                if ($cron->isDue()) {
                    $cron->start();
                }
            }
            // Stop cron task if timeout is reached
            if ($cron->getState() == 'run' && ($duration / 60) >= $cron->getTimeout()) {
                $cron->stop();
            }
            switch ($cron->getState()) {
                case 'starting':
                    $cron->run();
                    break;
                case 'stoping':
                    $cron->halt();
                    break;
            }
        } catch (Exception $e) {
            if ($cron->getOnce() != 1) {
                $cron->setState('error');
                $cron->setPID('');
                log::add('cron', 'error', __('[Erreur master] ', __FILE__) . $cron->getName() . ' : ' . $e->getMessage());
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
