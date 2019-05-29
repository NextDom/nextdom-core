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

namespace NextDom\Exceptions;

/**
 * Class CoreException
 * @package NextDom\Exceptions
 */
class CoreException extends \Exception
{
    /**
     * @param string $format
     * @throws CoreException
     */
    public static function do_throw($format = '')
    {
        $format = preg_replace_callback("/\{([^}]+)\}/", function ($match) {
            return __($match[1]);
        }, $format);

        $args = func_get_args();
        array_shift($args);

        $msg = vsprintf($format, $args);
        throw new CoreException($msg);
    }
}
