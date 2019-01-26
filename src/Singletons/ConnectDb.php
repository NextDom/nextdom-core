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

namespace NextDom\Singletons;

use NextDom\Exceptions\DbException;

class ConnectDb
{

    // Hold the class instance.
    private static $connection;
    private static $init = false;

    /**
     *
     * @return \PDO
     * @throws DbException
     * @global mixed $CONFIG
     */
    public static function getConnection()
    {
        if (!self::$init) {
            global $CONFIG;

            try {

                self::$connection = new \PDO('mysql:host=' . $CONFIG['db']['host'] . ';port=' . $CONFIG['db']['port'] . ';dbname=' . $CONFIG['db']['dbname'], $CONFIG['db']['username'], $CONFIG['db']['password']);
                self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $exc) {
                throw new DbException('PDO Error : ' . $exc->getMessage(), 500);
            }
            self::$init = true;
        }
        return self::$connection;
    }

}
