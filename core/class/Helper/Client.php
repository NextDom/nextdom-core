<?php
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
namespace NextDom\Helper;

class Client
{
    /**
     * Test si l'utilisateur utilise un navigateur sur mobile
     *
     * @return bool True si un navigateur mobile est détecté
     */
    public static function isMobile()
    {
        $userAgent = 'none';
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            $userAgent = $_SERVER["HTTP_USER_AGENT"];
        }
        $result = false;
        if (stristr($userAgent, "Android") ||
            strpos($userAgent, "iPod") ||
            strpos($userAgent, "iPhone") ||
            strpos($userAgent, "Mobile") ||
            strpos($userAgent, "WebOS") ||
            strpos($userAgent, "mobile") ||
            strpos($userAgent, "hp-tablet")) {
            $result = true;
        }
        return $result;
    }
}
