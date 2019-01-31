<?php

/* This file is part of NextDom Software.
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

namespace NextDom\Helpers;

use NextDom\Exceptions\CoreException;
use NextDom\Managers\ConfigManager;

/**
 * Temporary class to store different states.
 */
class Status
{

    /**
     * @var bool Status of the user connection
     */
    private static $connectState = false;

    /**
     * @var bool Status of the user login as administrator
     */
    private static $connectAdminState = false;

    /**
     * @var bool Recovery mode status
     */
    private static $rescueMode = false;

    /**
     * Initialize the status of the recovery mode
     */
    public static function initRescueModeState()
    {
        if (\init('rescue', 0) == 1) {
            self::$rescueMode = true;
        }
    }

    /**
     * Initialize the status of the user's connection
     */
    public static function initConnectState()
    {
        self::$connectState = AuthentificationHelper::isConnected();
        self::$connectAdminState = AuthentificationHelper::isConnected('admin');
    }

    /**
     * Get the status of the user login
     * @return bool Status of the user connection
     */
    public static function isConnect(): bool
    {
        return self::$connectState;
    }

    /**
     * Test if the user is logged in and throws an exception if this is not the case.
     * @return bool
     * @throws CoreException
     */
    public static function isConnectedOrFail()
    {
        if (!self::$connectState) {
            throw new CoreException(__('core.error-401'), 401);
        }
        return self::isConnect();
    }

    /**
     * @abstract Test if user is connected with admins right or throw CoreException if not.
     * @return bool
     * @throws CoreException
     */
    public static function isConnectedAdminOrFail()
    {
        if (!self::$connectAdminState) {
            throw new CoreException(__('core.error-401'), 401);
        }
        return self::isConnectAdmin();
    }

    /**
     * Get the login status of the user as an administrator
     * @return bool Status of the user login as administrator
     */
    public static function isConnectAdmin(): bool
    {
        return self::$connectAdminState;
    }

    /**
     * Get the status of the recovery mode
     * @return bool Recovery mode status
     */
    public static function isRescueMode(): bool
    {
        return self::$rescueMode;
    }

    /**
     * @return bool
     */
    public static function isInDeveloperMode(): bool
    {
        return ConfigManager::getDefaultConfiguration()['core']['developer::mode'] == '1';
    }
}
