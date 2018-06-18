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

namespace NextDom\Helpers;

/**
 * Assistant regroupant les méthodes nécessaires à la conversion
 *
 * @package NextDom\Helper
 */
class JeedomToNextDom
{
    /**
     * Convertit un plugin Jeedom pour NextDom
     *
     * @param string $pluginDirectory Répertoire du plugin
     */
    public static function convertPlugin(string $pluginDirectory)
    {
        $script = array(
            "grep -rl $pluginDirectory -e jeedom | xargs sed -i 's/jeedom/nextdom/g'",
            "grep -rl $pluginDirectory -e Jeedom | xargs sed -i 's/Jeedom/NextDom/g'",
            "grep -rl $pluginDirectory -e JEEDOM | xargs sed -i 's/JEEDOM/NEXTDOM/g'",
            "grep -rl $pluginDirectory -e \"nextdom.com\" | xargs sed -i 's/nextdom\.com/jeedom\.com/g'",
            "grep -rl $pluginDirectory -e \"This file is part of NextDom\" | xargs sed -i 's/This file is part of NextDom/This file is part of Jeedom/g'",
            "grep -rl $pluginDirectory -e \"NextDom is free software\" | xargs sed -i 's/NextDom is free software/Jeedom is free software/g'",
            "grep -rl $pluginDirectory -e \"NextDom is distributed\" | xargs sed -i 's/NextDom is distributed/Jeedom is distributed/g'",
            "grep -rl $pluginDirectory -e \"along with NextDom\" | xargs sed -i 's/along with NextDom/along with Jeedom/g'");
        foreach ($script as $scriptCommand) {
            exec(\system::getCmdSudo().' '.$scriptCommand);
        }

    }
}
