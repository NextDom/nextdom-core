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
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        global $CONFIG;

        try {

            $pdo = new \PDO('mysql:host=' . $CONFIG['db']['host'] . ';port=' . $CONFIG['db']['port'] . ';dbname=' . $CONFIG['db']['dbname'], $CONFIG['db']['username'], $CONFIG['db']['password']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            return $pdo;

        } catch (\PDOException $exc) {
            throw new DbException('PDO Error : ' . $exc->getMessage(), 500);
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Self();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

}
