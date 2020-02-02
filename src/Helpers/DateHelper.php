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

namespace NextDom\Helpers;

use NextDom\Managers\ConfigManager;

/**
 * Class DateHelper
 * @package NextDom\Helpers
 */
class DateHelper
{
    /**
     * @param $dateEn
     * @return mixed
     * @throws \Exception
     */
    public static function dateToFr($dateEn)
    {
        if (ConfigManager::byKey('language', 'core', 'fr_FR') == 'en_US') {
            return $dateEn;
        }
        $longTextEn = [
            '/(^| )Monday($| )/', '/(^| )Tuesday($| )/', '/(^| )Wednesday($| )/', '/(^| )Thursday($| )/',
            '/(^| )Friday($| )/', '/(^| )Saturday($| )/', '/(^| )Sunday($| )/', '/(^| )January($| )/',
            '/(^| )February($| )/', '/(^| )March($| )/', '/(^| )April($| )/', '/(^| )May($| )/',
            '/(^| )June($| )/', '/(^| )July($| )/', '/(^| )August($| )/', '/(^| )September($| )/',
            '/(^| )October($| )/', '/(^| )November($| )/', '/(^| )December($| )/',
        ];
        $shortTextEn = [
            '/(^| )Mon($| )/', '/(^| )Tue($| )/', '/(^| )Wed($| )/', '/(^| )Thu($| )/', '/(^| )Fri($| )/', '/(^| )Sat($| )/', '/(^| )Sun($| )/',
            '/(^| )Jan($| )/', '/(^| )Feb($| )/', '/(^| )Mar($| )/', '/(^| )Apr($| )/', '/(^| )May($| )/', '/(^| )Jun($| )/', '/(^| )Jul($| )/',
            '/(^| )Aug($| )/', '/(^| )Sep($| )/', '/(^| )Oct($| )/', '/(^| )Nov($| )/', '/(^| )Dec($| )/',
        ];

        switch (ConfigManager::byKey('language', 'core', 'fr_FR')) {
            case 'fr_FR':
                $longText = [
                    'Lundi', 'Mardi', 'Mercredi', 'Jeudi',
                    'Vendredi', 'Samedi', 'Dimanche', 'Janvier',
                    'Février', 'Mars', 'Avril', 'Mai',
                    'Juin', 'Juillet', 'Août', 'Septembre',
                    'Octobre', 'Novembre', 'Décembre',
                ];
                $shortText = [
                    'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim',
                    'Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin',
                    'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.',
                ];
                break;
            case 'de_DE':
                $longText = [
                    'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag',
                    'Freitag', 'Samstag', 'Sonntag', 'Januar',
                    'Februar', 'März', 'April', 'May',
                    'Juni', 'July', 'August', 'September',
                    'October', 'November', 'December',
                ];

                $shortText = [
                    'Mon', 'Die', 'Mit', 'Thu', 'Don', 'Sam', 'Son',
                    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul',
                    'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
                ];
                break;
            default:
                return $dateEn;
                break;
        }
        return preg_replace($shortTextEn, $shortText, preg_replace($longTextEn, $longText, $dateEn));
    }

    /**
     * @param $_day
     * @return mixed
     * @throws \Exception
     */
    public static function convertDayFromEn($_day)
    {
        $result = $_day;
        $daysMapping = [
            'fr_FR' => [
                'Monday' => 'Lundi', 'Mon' => 'Lundi',
                'monday' => 'lundi', 'mon' => 'lundi',
                'Tuesday' => 'Mardi', 'Tue' => 'Mardi',
                'tuesday' => 'mardi', 'tue' => 'mardi',
                'Wednesday' => 'Mercredi', 'Wed' => 'Mercredi',
                'wednesday' => 'mercredi', 'wed' => 'mercredi',
                'Thursday' => 'Jeudi', 'Thu' => 'Jeudi',
                'thursday' => 'jeudi', 'thu' => 'jeudi',
                'Friday' => 'Vendredi', 'Fri' => 'Vendredi',
                'friday' => 'vendredi', 'fri' => 'vendredi',
                'Saturday' => 'Samedi', 'Sat' => 'Samedi',
                'saturday' => 'samedi', 'sat' => 'samedi',
                'Sunday' => 'Dimanche', 'Sun' => 'Dimanche',
                'sunday' => 'dimanche', 'sun' => 'dimanche',
            ],
            'de_DE' => [
                'Monday' => 'Montag', 'Mon' => 'Montag',
                'monday' => 'montag', 'mon' => 'montag',
                'Tuesday' => 'Dienstag', 'Tue' => 'Dienstag',
                'tuesday' => 'dienstag', 'tue' => 'dienstag',
                'Wednesday' => 'Mittwoch', 'Wed' => 'Mittwoch',
                'wednesday' => 'mittwoch', 'wed' => 'mittwoch',
                'Thursday' => 'Donnerstag', 'Thu' => 'Donnerstag',
                'thursday' => 'donnerstag', 'thu' => 'donnerstag',
                'Friday' => 'Freitag', 'Fri' => 'Freitag',
                'friday' => 'freitag', 'fri' => 'freitag',
                'Saturday' => 'Samstag', 'Sat' => 'Samstag',
                'saturday' => 'samstag', 'sat' => 'samstag',
                'Sunday' => 'Sonntag', 'Sun' => 'Sonntag',
                'sunday' => 'sonntag', 'sun' => 'sonntag',
            ],
        ];
        $language = ConfigManager::byKey('language', 'core', 'fr_FR');
        if (array_key_exists($language, $daysMapping)) {
            $daysArray = $daysMapping[$language];
            if (array_key_exists($_day, $daysArray)) {
                $result = $daysArray[$_day];
            }
        }

        return $result;
    }

    /**
     * @return bool|false|string
     */
    public static function getNtpTime()
    {
        $time_servers = [
            'ntp2.emn.fr',
            'time-a.timefreq.bldrdoc.gov',
            'utcnist.colorado.edu',
            'time.nist.gov',
            'ntp.pads.ufrj.br',
        ];
        $time_adjustment = 0;
        foreach ($time_servers as $time_server) {
            $fp = fsockopen($time_server, 37, $errno, $errstr, 1);
            if ($fp) {
                $data = NULL;
                while (!feof($fp)) {
                    $data .= fgets($fp, 128);
                }
                fclose($fp);
                if (strlen($data) == 4) {
                    $NTPtime = ord($data{0}) * pow(256, 3) + ord($data{1}) * pow(256, 2) + ord($data{2}) * 256 + ord($data{3});
                    $TimeFrom1990 = $NTPtime - 2840140800;
                    $TimeNow = $TimeFrom1990 + 631152000;
                    return date("m/d/Y H:i:s", $TimeNow + $time_adjustment);
                }
            }
        }
        return false;
    }
}
