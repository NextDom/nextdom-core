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

use NextDom\Helpers\NetworkHelper;

require_once __DIR__ . '/../../core/php/core.inc.php';

class network {
    
    public static function getUserLocation() {
        return NetworkHelper::getUserLocation();
    }

    public static function getClientIp() {
        return NetworkHelper::getClientIp();
    }

    public static function getNetworkAccess($_mode = 'auto', $_protocol = '', $_default = '', $_test = false) {
        return NetworkHelper::getNetworkAccess($_mode, $_protocol, $_default, $_test);
    }

    public static function checkConf($_mode = 'external') {
        NetworkHelper::checkConf($_mode);
    }

    public static function test($_mode = 'external', $_timeout = 5) {
        return NetworkHelper::test($_mode, $_timeout);
    }

    public static function dns_create() {
        return NetworkHelper::dnsCreate();
    }

    public static function dns_start() {
        NetworkHelper::dnsStart();
    }

    public static function dns_run() {
        return NetworkHelper::dnsRun();
    }

    public static function dns_stop() {
        NetworkHelper::dnsStop();
    }

    public static function getInterfaceIp($_interface) {
        return NetworkHelper::getInterfaceIp($_interface);
    }

    public static function getInterfaceMac($_interface) {
        return NetworkHelper::getInterfaceMac($_interface);
    }

    public static function getInterfaces() {
        return NetworkHelper::getInterfacesList();
    }

    public static function cron5() {
        NetworkHelper::cron5();
    }
}
