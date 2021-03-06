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

use NextDom\Com\ComShell;
use NextDom\Enums\CacheEngine;
use NextDom\Enums\CacheKey;
use NextDom\Enums\DateFormat;
use NextDom\Enums\NextDomFile;
use NextDom\Enums\NextDomObj;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Model\DataClass\WidgetTheme;
use NextDom\Model\Entity\Cache;

/**
 * Class CacheManager
 * @package NextDom\Managers
 */
class ThemeManager
{
    /**
     * Get all widget themes
     *
     * @return WidgetTheme[]
     */
    public static function getWidgetThemes() {
        $result = [];
        $widgetThemesPath = '/views/templates/dashboard/themes/';
        $lsDir = FileSystemHelper::ls(NEXTDOM_ROOT . $widgetThemesPath, '*', true);
        foreach ($lsDir as $themesDir) {
            $lsThemes = FileSystemHelper::ls(NEXTDOM_ROOT . $widgetThemesPath . $themesDir, '*.png');
            foreach ($lsThemes as $themeFile) {
                $result[] = new WidgetTheme($widgetThemesPath . $themesDir . $themeFile);
            }
        }
        return $result;
    }
}
