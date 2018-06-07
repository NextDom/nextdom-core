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

    private $host;
    
    private $port;
    
    private $dbName;
    
    private $userName;
    
    private $password;
    /**
     * @var
     */
    private static $instance;

    /**
     * ConnectDb constructor.
     */
    private function __construct(){

    }

    /**
     * @name connectPDO()
     * @access private
     * @return object or DbException
     */
    private static function connectPDO()
    {

        try {

            $pdo = new \PDO('mysql:host=' . $this->getHost() . ';port=' . $this->getPort() . ';dbname=' . $this->getDbName(), $this->getUserName(), $this->getPassword());
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $pdo;

        } catch (\PDOException $exc) {
            throw new DbException('PDO Error : ' . $exc->getMessage(), 500);
        }
    }

    /**
     * @name getInstance()
     * @return instance of DB
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = self::connectPDO();
        }
        return self::$instance;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): string
    {
        return $this->port;
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setHost(string $host)
    {
        $this->host = $host;
        return $this;
    }

    public function setPort(string $port)
    {
        $this->port = $port;
        return $this;
    }

    public function setDbName(string $dbName)
    {
        $this->dbName = $dbName;
        return $this;
    }

    public function setUserName(string $userName)
    {
        $this->userName = $userName;
        return $this;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
        return $this;
    }

}
