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

namespace NextDom\Enums;

use NextDom\Exceptions\OperatingSystemException;

abstract class Enum
{
    /**
     * Get list of all constants
     *
     * @return array List of all constants
     *
     * @throws \ReflectionException
     * @throws OperatingSystemException
     */
    public static function getConstants()
    {
        $calledClass = get_called_class();
        if( $calledClass !== false){
             return (new \ReflectionClass(get_called_class()))->getConstants();
        } else {
            throw new OperatingSystemException('Error during calling class', 500);
        }
       
    }

    public static function exists($needle): bool 
    {
        return in_array($needle, array_values(self::getConstants()));
    }
}
