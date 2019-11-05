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

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../core/php/core.inc.php';

use NextDom\Helpers\AjaxHelper;

class ajax {
    private static $ajax = null;

    private static function getAjaxHelper() {
        if (self::$ajax === null) {
            self::$ajax = new AjaxHelper();
        }
        return self::$ajax;
    }

    public static function init($checkToken = true) {
        $ajax = self::getAjaxHelper();
        if ($checkToken) {
            $ajax->checkToken();
        }
    }

    public static function getToken() {
        return AjaxHelper::getToken();
    }

    public static function success($_data = '') {
        self::getAjaxHelper()->success($_data);
    }

    public static function error($_data = '', $_errorCode = 0) {
        self::getAjaxHelper()->error($_data, $_errorCode);
    }

    public static function getResponse($_data = '', $_errorCode = null) {
        self::getAjaxHelper()->getResponse($_data, $_errorCode);
    }
}
