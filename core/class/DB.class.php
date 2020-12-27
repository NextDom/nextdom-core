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

/* ------------------------------------------------------------ Inclusions */

use NextDom\Helpers\DBHelper;

class DB
{
    const FETCH_TYPE_ROW = 0;
    const FETCH_TYPE_ALL = 1;

    /**
     * @return mixed
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function getLastInsertId()
    {
        return DBHelper::getLastInsertId();
    }

    public static function getConnection()
    {
        return DBHelper::getConnection();
    }

    /**
     * @param      $_procName
     * @param      $_params
     * @param      $_fetch_type
     * @param null $_className
     * @param null $_fetch_opt
     * @return mixed
     */
    public static function &CallStoredProc($_procName, $_params, $_fetch_type, $_className = NULL, $_fetch_opt = NULL)
    {
        return self::CallStoredProc($_procName, $_params, $_fetch_type, $_className, $_fetch_opt);
    }

    public static function &Prepare($_query, $_params, $_fetchType = self::FETCH_TYPE_ROW, $_fetch_param = PDO::FETCH_ASSOC, $_fetch_opt = NULL)
    {
        return DBHelper::Prepare($_query, $_params, $_fetchType, $_fetch_param, $_fetch_opt);
    }

    public function __clone()
    {
        trigger_error('DB : Cloner cet objet n\'est pas permis', E_USER_ERROR);
    }

    public static function optimize()
    {
        DBHelper::optimize();
    }

    public static function beginTransaction()
    {
        DBHelper::beginTransaction();
    }

    public static function commit()
    {
        DBHelper::commit();
    }

    public static function rollBack()
    {
        DBHelper::rollBack();
    }

    public static function save($object, $_direct = false, $_replace = false)
    {
        return DBHelper::save($object, $_direct, $_replace);
    }

    public static function refresh($target)
    {
        return DBHelper::refresh($target);
    }

    /**
     * Retourne une liste d'objets ou un objet en fonction de filtres
     * @param array $_filters Filtres à appliquer
     * @param $_object Objet sur lequel appliquer les filtres
     * @return array ou liste d'objets correspondant à la requête
     * @throws ReflectionException
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function getWithFilter(array $_filters, $_object)
    {
        return DBHelper::getWithFilter($_filters, $_object);
    }

    /**
     * Deletes an entity.
     *
     * @param object $target
     * @return boolean
     * @throws \NextDom\Exceptions\CoreException
     * @throws ReflectionException
     */
    public static function remove($target)
    {
        return DBHelper::remove($target);
    }

    /**
     * @param $_table
     * @return mixed
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function checksum($_table)
    {
        return DBHelper::checksum($_table);
    }

    /**
     * Lock an entity.
     *
     * @param object $target
     * @return boolean
     * @throws \NextDom\Exceptions\CoreException
     * @throws ReflectionException
     */
    public static function lock($target)
    {
        return DBHelper::lock($target);
    }

    /*************************DB ANALYZER***************************/

    /**
     * @param        $_class
     * @param string $_prefix
     * @return string
     * @throws ReflectionException
     */
    public static function buildField($_class, $_prefix = '')
    {
        return DBHelper::buildField($_class, $_prefix);
    }

    /**
     * @param        $_database
     * @param string $_table
     * @param false  $_verbose
     * @param int    $_loop
     * @return bool
     * @throws Exception
     */
    public static function compareAndFix($_database, $_table = 'all', $_verbose = false, $_loop = 0)
    {
        return DBHelper::compareAndFix($_database, $_table, $_verbose, $_loop);
    }
}
