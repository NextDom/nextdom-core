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

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

define ('NEXTDOM_ROOT', realpath(__DIR__.'/../..'));

date_default_timezone_set('Europe/Brussels');
if (file_exists(NEXTDOM_ROOT.'/core/config/common.config.php')) {
	require_once NEXTDOM_ROOT . '/core/config/common.config.php';
}
require_once NEXTDOM_ROOT.'/vendor/autoload.php';
require_once NEXTDOM_ROOT.'/core/class/DB.class.php';
require_once NEXTDOM_ROOT.'/core/class/config.class.php';
////////////////////////////////////
/////    developper mode   /////////
////////////////////////////////////
// Register global error and exception handlers
if ((config::getDefaultConfiguration()['core']['developer::mode'] == '1') && (config::getDefaultConfiguration()['core']['developer::errorhandler'] == '1') && (config::getDefaultConfiguration()['core']['developer::exceptionhandler'] == '1')) {
    Symfony\Component\Debug\ErrorHandler::register();
    Symfony\Component\Debug\ExceptionHandler::register();
}

//require_once NEXTDOM_ROOT.'/core/src/app/app.php';
require_once NEXTDOM_ROOT.'/core/class/nextdom.class.php';
require_once NEXTDOM_ROOT.'/core/class/jeedom.class.php';
require_once NEXTDOM_ROOT.'/core/class/plugin.class.php';
require_once NEXTDOM_ROOT.'/core/class/translate.class.php';
require_once NEXTDOM_ROOT.'/core/php/utils.inc.php';

include_file('core', 'nextdom', 'config');
include_file('core', 'compatibility', 'config');
include_file('core', 'utils', 'class');
include_file('core', 'log', 'class');

try {
    $configs = config::byKeys(array('timezone', 'log::level'));
    if (isset($configs['timezone'])) {
        date_default_timezone_set($configs['timezone']);
    }
} catch (\Throwable $e ) {

}

try {
    if (isset($configs['log::level'])) {
        log::define_error_reporting($configs['log::level']);
    }
} catch (\Throwable $e ) {

}

function nextdomCoreAutoload($classname) {
    try {
        include_file('core', $classname, 'class');
    } catch (\Throwable $e ) {

    }
}

function nextdomPluginAutoload($_classname) {
    if (strpos($_classname, '\\') !== false || strpos($_classname, 'com_') !== false || strpos($_classname, 'repo_') !== false || strpos($_classname, '/') !== false) {
        return;
    }
    $classname = str_replace(array('Real', 'Cmd'), '', $_classname);
    $plugin_active = config::byKey('active', $classname, null);
    if ($plugin_active === null || $plugin_active == '') {
        $classname = explode('_', $classname)[0];
        $plugin_active = config::byKey('active', $classname, null);
    }
    try {
        if ($plugin_active == 1) {
            include_file('core', $classname, 'class', $classname);
        }
    } catch (\Throwable $e ) {

    }
}

function nextdomOtherAutoload($classname) {
    try {
        include_file('core', substr($classname, 4), 'com');
        return;
    } catch (\Throwable $e ) {

    }
    try {
        include_file('core', substr($classname, 5), 'repo');
        return;
    } catch (\Throwable $e ) {

    }
}
spl_autoload_register('nextdomOtherAutoload', true, true);
spl_autoload_register('nextdomPluginAutoload', true, true);
spl_autoload_register('nextdomCoreAutoload', true, true);
