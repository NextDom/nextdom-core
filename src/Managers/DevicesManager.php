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

namespace NextDom\Managers;

use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\Utils;

require_once NEXTDOM_ROOT . '/core/class/cache.class.php';

/**
 * Class DevicesManager
 * @package NextDom\Managers
 */
class DevicesManager
{
    /**
     * Obtenir la liste des périphériques USB
     *
     * @param string $name
     * @param bool $getGPIO
     *
     * @return array|mixed|string
     * @throws \Exception
     */
    public static function getUsbMapping($name = '', $getGPIO = false)
    {
        $cache = CacheManager::byKey('nextdom::usbMapping');
        if (!Utils::isJson($cache->getValue()) || $name == '') {
            $usbMapping = [];
            foreach (FileSystemHelper::ls('/dev/', 'ttyUSB*') as $usb) {
                $vendor = '';
                $model = '';
                $devsList = shell_exec('/sbin/udevadm info --name=/dev/' . $usb . ' --query=all');
                foreach (explode("\n", $devsList) as $line) {
                    if (strpos($line, 'E: ID_MODEL=') !== false) {
                        $model = trim(str_replace(['E: ID_MODEL=', '"'], '', $line));
                    }
                    if (strpos($line, 'E: ID_VENDOR=') !== false) {
                        $vendor = trim(str_replace(['E: ID_VENDOR=', '"'], '', $line));
                    }
                }
                if ($vendor == '' && $model == '') {
                    $usbMapping['/dev/' . $usb] = '/dev/' . $usb;
                } else {
                    $deviceName = trim($vendor . ' ' . $model);
                    $number = 2;
                    while (isset($usbMapping[$deviceName])) {
                        $deviceName = trim($vendor . ' ' . $model . ' ' . $number);
                        $number++;
                    }
                    $usbMapping[$deviceName] = '/dev/' . $usb;
                }
            }
            if ($getGPIO) {
                if (file_exists('/dev/ttyAMA0')) {
                    $usbMapping['Raspberry pi'] = '/dev/ttyAMA0';
                }
                if (file_exists('/dev/ttymxc0')) {
                    $usbMapping['NextDom board'] = '/dev/ttymxc0';
                }
                if (file_exists('/dev/S2')) {
                    $usbMapping['Banana PI'] = '/dev/S2';
                }
                if (file_exists('/dev/ttyS2')) {
                    $usbMapping['Banana PI (2)'] = '/dev/ttyS2';
                }
                if (file_exists('/dev/ttyS0')) {
                    $usbMapping['Cubiboard'] = '/dev/ttyS0';
                }
                if (file_exists('/dev/ttyS3')) {
                    $usbMapping['Orange PI'] = '/dev/ttyS3';
                }
                if (file_exists('/dev/ttyS1')) {
                    $usbMapping['Odroid C2'] = '/dev/ttyS1';
                }
                foreach (FileSystemHelper::ls('/dev/', 'ttyACM*') as $value) {
                    $usbMapping['/dev/' . $value] = '/dev/' . $value;
                }
            }
            CacheManager::set('nextdom::usbMapping', json_encode($usbMapping));
        } else {
            $usbMapping = json_decode($cache->getValue(), true);
        }
        if ($name != '') {
            if (isset($usbMapping[$name])) {
                return $usbMapping[$name];
            }
            $usbMapping = self::getUsbMapping('', true);
            if (isset($usbMapping[$name])) {
                return $usbMapping[$name];
            }
            if (file_exists($name)) {
                return $name;
            }
            return '';
        }
        return $usbMapping;
    }

    /**
     * Obtenir la liste des périphériques Bluetooth
     *
     * @param string $name
     * @return array|mixed|string
     * @throws \Exception
     */
    public static function getBluetoothMapping($name = '')
    {
        $cache = CacheManager::byKey('nextdom::bluetoothMapping');
        if (!Utils::isJson($cache->getValue()) || $name == '') {
            $bluetoothMapping = [];
            foreach (explode("\n", shell_exec('hcitool dev')) as $line) {
                if (strpos($line, 'hci') === false || trim($line) == '') {
                    continue;
                }
                $infos = explode("\t", $line);
                $bluetoothMapping[$infos[2]] = $infos[1];
            }
            CacheManager::set('nextdom::bluetoothMapping', json_encode($bluetoothMapping));
        } else {
            $bluetoothMapping = json_decode($cache->getValue(), true);
        }
        if ($name != '') {
            if (isset($bluetoothMapping[$name])) {
                return $bluetoothMapping[$name];
            }
            $bluetoothMapping = self::getBluetoothMapping('');
            if (isset($bluetoothMapping[$name])) {
                return $bluetoothMapping[$name];
            }
            if (file_exists($name)) {
                return $name;
            }
            return '';
        }
        return $bluetoothMapping;
    }
}
