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

namespace NextDom\Managers\Parents;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Model\Entity\PlanHeader;

/**
 * Base manager with commonts functions
 *
 * @package NextDom\Managers
 */
trait CommonManager
{
    /**
     * Get one object by his id
     *
     * @param $requestedId
     *
     * @return mixed
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function byId($requestedId)
    {
        if (empty($requestedId)) {
            return null;
        }
        $params = ['id' => $requestedId];
        $sql = static::getBaseSQL() . "WHERE `id` = :id";
        return DBHelper::getOneObject($sql, $params, static::CLASS_NAME);
    }

    /**
     * Get all objects
     *
     * @return mixed|null
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected static function getAll()
    {
        return DBHelper::getAllObjects(static::getBaseSQL(), [], static::CLASS_NAME);
    }

    /**
     * Get all object sorted on column
     *
     * @param string $orderColumn Sort column
     * @param bool $reverseOrder True for DESC order
     * @param int $limit Max number of results
     *
     * @return mixed|null
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected static function getAllOrdered(string $orderColumn, bool $reverseOrder = false, int $limit = 0)
    {
        $sql = static::getBaseSQL() . "ORDER BY `$orderColumn`";
        if ($reverseOrder) {
            $sql .= ' DESC';
        }
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        return DBHelper::getAllObjects($sql, [], static::CLASS_NAME);
    }

    /**
     * Get one object filtered with clauses
     *
     * @param array $clauses
     * @param string|array $orderColumn
     * @param bool $reverseOrder
     *
     * @return mixed|null
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected static function getOneByClauses(array $clauses, $orderColumn = '', bool $reverseOrder = false)
    {
        return static::query($clauses, '=', true, $orderColumn, $reverseOrder);
    }

    /**
     * Get one multiple filtered with clauses
     *
     * @param array $clauses
     * @param string|array $orderColumn
     * @param bool $reverseOrder
     *
     * @return mixed|null
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected static function getMultipleByClauses(array $clauses, $orderColumn = '', bool $reverseOrder = false)
    {
        return static::query($clauses, '=', false, $orderColumn, $reverseOrder);
    }

    /**
     * Search one object filtered with clauses
     *
     * @param array $clauses
     * @param string|array $orderColumn
     * @param bool $reverseOrder
     *
     * @return mixed|null
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected static function searchOneByClauses(array $clauses, $orderColumn = '', bool $reverseOrder = false)
    {
        return static::query($clauses, 'LIKE', true, $orderColumn, $reverseOrder);
    }

    /**
     * Search one multiple filtered with clauses
     *
     * @param array $clauses
     * @param string|array $orderColumn
     * @param bool $reverseOrder
     *
     * @return mixed|null
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected static function searchMultipleByClauses(array $clauses, $orderColumn = '', bool $reverseOrder = false)
    {
        return static::query($clauses, 'LIKE', false, $orderColumn, $reverseOrder);
    }

    /**
     * Execute a query
     *
     * @param array $clauses
     * @param string $compOperator
     * @param bool $onlyOneResult
     * @param string|array $orderColumn
     * @param bool $reverseOrder
     *
     * @return mixed|null
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    private static function query(array $clauses, $compOperator = '', bool $onlyOneResult = false, $orderColumn = '', bool $reverseOrder = false) {
        $sql = static::createSQL($clauses, $compOperator, $orderColumn, $reverseOrder);
        if ($onlyOneResult) {
            return DBHelper::getOneObject($sql, $clauses, static::CLASS_NAME);
        }
        else {
            return DBHelper::getAllObjects($sql, $clauses, static::CLASS_NAME);
        }
    }

    /**
     * Create SQL query
     *
     * @param array $clauses
     * @param string $compOperator
     * @param string|array $orderColumn
     * @param bool $reverseOrder
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    private static function createSQL(array $clauses, string $compOperator, $orderColumn = '', bool $reverseOrder = false)
    {
        $sql = static::getBaseSQL();
        $sqlClauses = '';
        $sqlOrder = '';
        foreach ($clauses as $clauseName => $clauseValue) {
            if ($sqlClauses === '') {
                $sqlClauses = "WHERE ";
            }
            else {
                $sqlClauses .= "AND ";
            }
            $sqlClauses .= "`$clauseName` $compOperator :$clauseName ";
        }
        if ($orderColumn !== '') {
            if (is_array($orderColumn)) {
                $sqlOrder = "ORDER BY `" . implode("`, `", $orderColumn) . "`";
            }
            else {
                $sqlOrder = "ORDER BY `$orderColumn`";
            }
            if ($reverseOrder) {
                $sqlOrder .= ' DESC';
            }
        }
        return $sql . $sqlClauses . $sqlOrder;
    }
}