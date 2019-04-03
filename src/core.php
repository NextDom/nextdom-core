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

namespace {
    use NextDom\Helpers\FileSystemHelper;
    use NextDom\Managers\ConfigManager;

    define('NEXTDOM_ROOT', realpath(__DIR__ . '/..'));

    if (file_exists(NEXTDOM_ROOT . '/core/config/common.config.php')) {
        require_once NEXTDOM_ROOT . '/core/config/common.config.php';
    }

    global $CONFIG;
    define('NEXTDOM_DATA', $CONFIG["paths"]["lib"]);
    define('NEXTDOM_LOG',  $CONFIG["paths"]["log"]);
    define('NEXTDOM_RUN',  $CONFIG["paths"]["run"]);

    require_once NEXTDOM_ROOT . '/vendor/autoload.php';
    require_once NEXTDOM_ROOT . '/core/class/DB.class.php';
    require_once NEXTDOM_DATA . '/config/nextdom.config.php';
    require_once NEXTDOM_DATA . '/config/compatibility.config.php';

    // Developer mode : Register global error and exception handlers
    if (('cli' != php_sapi_name()) &&
        (  '1' == ConfigManager::getDefaultConfiguration()['core']['developer::mode']) &&
        (  '1' == ConfigManager::getDefaultConfiguration()['core']['developer::errorhandler']) &&
        (  '1' == ConfigManager::getDefaultConfiguration()['core']['developer::exceptionhandler']))
    {
        Symfony\Component\Debug\ErrorHandler::register();
        Symfony\Component\Debug\ExceptionHandler::register();
    }

    /**
     * Include files from Jeedom core
     *
     * @param string $className Name of the class
     */
    function jeedomCoreAutoload(string $className)
    {
        if (strpos($className, '\\') === false) {
            try {
                FileSystemHelper::includeFile('core', $className, 'class');
            } catch (\Throwable $e) {

            }
        }
    }

    /**
     * Include files from plugins
     *
     * @param string $className Name of the class
     *
     * @throws Exception
     */
    function nextdomPluginAutoload($className)
    {
        if (strpos($className, '\\') !== false || strpos($className, 'com_') !== false || strpos($className, 'repo_') !== false || strpos($className, '/') !== false) {
            return;
        }
        $purgedClassName = str_replace(array('Real', 'Cmd'), '', $className);
        $activePlugin = ConfigManager::byKey('active', $purgedClassName, null);
        if ($activePlugin === null || $activePlugin == '') {
            $purgedClassName = explode('_', $purgedClassName)[0];
            $activePlugin = ConfigManager::byKey('active', $purgedClassName, null);
        }
        try {
            if ($activePlugin == 1) {
                FileSystemHelper::includeFile('core', $purgedClassName, 'class', $purgedClassName);
            }
        } catch (\Throwable $e) {

        }
    }

    /**
     * Include repo_* files and com_* files
     *
     * @param string $className Name of the class
     */
    function nextdomOtherAutoload($className)
    {
        if (strpos($className, '\\') === false) {
            if (strpos($className, 'com_') !== false) {
                try {
                    FileSystemHelper::includeFile('core', substr($className, 4), 'com');
                    return;
                } catch (\Throwable $e) {

                }
            }
            if (strpos($className, 'repo_') !== false) {
                try {
                    FileSystemHelper::includeFile('core', substr($className, 5), 'repo');
                    return;
                } catch (\Throwable $e) {

                }
            }
        }
    }

    /**
     * Translate a string (global function)
     *
     * @param string $content
     * @param string $name
     * @param bool $backslah
     * @return string
     * @throws \Exception
     */
    function __(string $content, string $name = '', bool $backslah = false): string
    {
        return \NextDom\__($content, $name, $backslah);
    }

    /**
     * Autoloading functions
     */
    spl_autoload_register('nextdomOtherAutoload', true, true);
    spl_autoload_register('nextdomPluginAutoload', true, true);
    spl_autoload_register('jeedomCoreAutoload', true, true);
}

// Declare global functions
namespace NextDom {

    use NextDom\Helpers\TranslateHelper;

    /**
     * Translate a string
     *
     * @param string $content
     * @param string $name
     * @param bool $backslah
     * @return string
     * @throws \Exception
     */
    function __(string $content, string $name = '', bool $backslah = false): string
    {
        return TranslateHelper::sentence($content, $name, $backslah);
    }
}
