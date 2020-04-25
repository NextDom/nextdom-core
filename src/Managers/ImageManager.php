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


use NextDom\Exceptions\CoreException;
use NextDom\Model\DataClass\WidgetTheme;

/**
 * Class CacheManager
 * @package NextDom\ImageManager
 */
class ImageManager
{
    /**
     * Get all widget themes
     *
     * @return string
     * @throws CoreException
     */
    public static function getDirectory() {
        $dir = "data/img/";
        if ('/' != substr($dir, 0, 1)) {
            $dir = sprintf("%s/%s", NEXTDOM_DATA, $dir);
        }
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            throw new CoreException("unable to create backup directory " . $dir);
        }
        return $dir;
    }
}
