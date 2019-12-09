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
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Helpers;

use NextDom\Exceptions\CoreException;

/**
 * Class DBHelper
 * @package NextDom\Helpers
 */
class DBHelper
{
    const FETCH_TYPE_ROW = 0;
    const FETCH_TYPE_ALL = 1;
    const CONNECTION_TIMEOUT = 120;
    private static $sharedInstance;
    private static $fieldsCache = [];
    private static $fieldsQuery = [];
    private $connection;
    private $lastConnection;

    /**
     * Private constructor for singleton
     */
    private function __construct()
    {
        global $CONFIG;
        if (isset($CONFIG['db']['unix_socket'])) {
            $this->connection = new \PDO('mysql:unix_socket=' . $CONFIG['db']['unix_socket'] . ';dbname=' . $CONFIG['db']['dbname'], $CONFIG['db']['username'], $CONFIG['db']['password'], [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', \PDO::ATTR_PERSISTENT => true]);
        } else {
            $this->connection = new \PDO('mysql:host=' . $CONFIG['db']['host'] . ';port=' . $CONFIG['db']['port'] . ';dbname=' . $CONFIG['db']['dbname'], $CONFIG['db']['username'], $CONFIG['db']['password'], [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', \PDO::ATTR_PERSISTENT => true]);
        }
    }

    /**
     * Call procedure
     * @TODO: Y en a-t-il d'utilisé ?
     *
     * @param string $procName Procedure name
     * @param array $params Parameters
     * @param int $fetchType Fetch type
     * @param string|null $className Name of the class
     * @param null $fetchOpt
     * @return array|mixed|null
     *
     * @throws CoreException
     */
    public static function &CallStoredProc($procName, $params, $fetchType, $className = NULL, $fetchOpt = NULL)
    {
        // Generate parameters to bind string
        $bindParams = str_repeat('?, ', count($params));
        // Remove last ,
        $bindParams = trim($bindParams, ', ');
        if ($className != NULL && class_exists($className)) {
            return self::Prepare("CALL $procName($bindParams)", $params, $fetchType, \PDO::FETCH_CLASS, $className);
        } else if ($fetchOpt != NULL) {
            return self::Prepare("CALL $procName($bindParams)", $params, $fetchType, $fetchOpt, $className);
        } else {
            return self::Prepare("CALL $procName($bindParams)", $params, $fetchType);
        }

    }

    /**
     * Prepare a query and execute
     *
     * @param $query
     * @param array $params
     * @param int $fetchType
     * @param int $fetchParam
     * @param mixed $fetchOpt
     *
     * @return array|mixed|null
     *
     * @throws CoreException
     */
    public static function &Prepare($query, $params = [], $fetchType = self::FETCH_TYPE_ROW, $fetchParam = \PDO::FETCH_ASSOC, $fetchOpt = NULL)
    {
        // Prepare statement
        $statement = self::getConnection()->prepare($query);
        $result = NULL;
        // If execution success
        if ($statement !== false && $statement->execute($params) !== false) {
            if ($fetchType == self::FETCH_TYPE_ROW) {
                if ($fetchOpt === null) {
                    $result = $statement->fetch($fetchParam);
                } else if ($fetchParam == \PDO::FETCH_CLASS) {
                    $result = $statement->fetchObject($fetchOpt);
                }
            } else {
                if ($fetchOpt === null) {
                    $result = $statement->fetchAll($fetchParam);
                } else {
                    $result = $statement->fetchAll($fetchParam, $fetchOpt);
                }
            }
        }
        // Get error
        $errorInfo = $statement->errorInfo();
        // @TODO: Revoir cette chaine
        if ($errorInfo[0] != 0000) {
            throw new CoreException('[MySQL] Error code : ' . $errorInfo[0] . ' (' . $errorInfo[1] . '). ' . $errorInfo[2] . '  : ' . $query);
        }
        return $result;
    }

    /**
     * Get the connection. Connect to the database if not.
     *
     * @return \PDO Connection
     */
    public static function getConnection()
    {
        if (!isset(self::$sharedInstance) || self::$sharedInstance->lastConnection + self::CONNECTION_TIMEOUT < time()) {
            self::$sharedInstance = new self();
        }
        // Store last connection
        self::$sharedInstance->lastConnection = time();
        return self::$sharedInstance->connection;
    }

    /**
     * Get objects from database
     *
     * @param string $query SQL query
     * @param array $params Query params
     * @param string $objectClassName Class of object
     *
     * @return mixed|null Array of instances of the class
     *
     * @throws CoreException
     */
    public static function &getAllObjects(string $query, array $params, string $objectClassName)
    {
        return self::Prepare($query, $params, self::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, $objectClassName);
    }

    /**
     * Optimize all tables
     *
     * @throws CoreException
     */
    public static function optimize()
    {
        $tables = self::getAll('SELECT TABLE_NAME FROM information_schema.TABLES WHERE Data_Free > 0', []);
        foreach ($tables as $table) {
            $table = array_values($table);
            $table = $table[0];
            self::exec('OPTIMIZE TABLE `' . $table . '`');
        }
    }

    /**
     * Get data from database
     *
     * @param string $query SQL query
     * @param array $params Query params
     *
     * @return mixed Associative array with data
     *
     * @throws CoreException
     */
    public static function &getAll(string $query, array $params = [])
    {
        return self::Prepare($query, $params, self::FETCH_TYPE_ALL, \PDO::FETCH_ASSOC);
    }

    /**
     * Execute a query without result (DELETE, UPDATE, INSERT, etc.)
     *
     * @param string $query SQL query
     * @param array $params Query params
     *
     * @return bool True on success
     */
    public static function exec(string $query, array $params = [])
    {
        $statement = self::getConnection()->prepare($query);
        if ($statement !== false && $statement->execute($params) !== false) {
            $errorInfo = $statement->errorInfo();
            // TODO: Revoir cette chaine
            if ($errorInfo[0] != 0000) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction()
    {
        self::getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit()
    {
        self::getConnection()->commit();
    }

    /**
     * Rollback a transaction
     */
    public static function rollBack()
    {
        self::getConnection()->rollBack();
    }

    /**
     * Saves an entity inside the repository. If the entity is new a new row
     * will be created. If the entity is not new the row will be updated.
     *
     * @param mixed $objToSave Object to save
     * @param bool $noProcess Don't call process before and after (preSave, preInsert, postSave, etc.)
     * @param bool $forceReplace @TODO: Force le remplacement si pas d'ID ????
     *
     * @return boolean True on save success
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function save($objToSave, $noProcess = false, $forceReplace = false)
    {
        if (!$noProcess && method_exists($objToSave, 'preSave')) {
            $objToSave->preSave();
        }
        // Check if id is defined
        if (!self::getField($objToSave, 'id')) {
            //New object to save.
            $fields = self::getFields($objToSave);
            if (in_array('id', $fields)) {
                self::setField($objToSave, 'id', null);
            }
            if (!$noProcess && method_exists($objToSave, 'preInsert')) {
                $objToSave->preInsert();
            }
            list($sql, $parameters) = self::buildQuery($objToSave);
            if ($forceReplace) {
                $sql = 'REPLACE INTO `' . self::getTableName($objToSave) . '` SET ' . implode(', ', $sql);
            } else {
                $sql = 'INSERT INTO `' . self::getTableName($objToSave) . '` SET ' . implode(', ', $sql);
            }
            $res = self::getOne($sql, $parameters);
            $reflection = self::getReflectionClass($objToSave);
            if ($reflection->hasProperty('id')) {
                try {
                    self::setField($objToSave, 'id', self::getLastInsertId());
                } catch (\Exception $exc) {
                    trigger_error($exc->getMessage(), E_USER_NOTICE);
                }
            }
            if (!$noProcess && method_exists($objToSave, 'postInsert')) {
                $objToSave->postInsert();
            }
        } else {
            //Object to update.
            if (!$noProcess && method_exists($objToSave, 'preUpdate')) {
                $objToSave->preUpdate();
            }
            $changed = true;
            if (method_exists($objToSave, 'getChanged')) {
                $changed = $objToSave->getChanged();
            }
            if ($changed) {
                list($sql, $parameters) = self::buildQuery($objToSave);
                if (!$noProcess && method_exists($objToSave, 'getId')) {
                    $parameters['id'] = $objToSave->getId(); //override if necessary
                }
                $sql = 'UPDATE `' . self::getTableName($objToSave) . '` SET ' . implode(', ', $sql) . ' WHERE id = :id';
                $res = self::getOne($sql, $parameters);
            } else {
                $res = true;
            }
            if (!$noProcess && method_exists($objToSave, 'postUpdate')) {
                $objToSave->postUpdate();
            }
        }
        if (!$noProcess && method_exists($objToSave, 'postSave')) {
            $objToSave->postSave();
        }
        if (method_exists($objToSave, 'setChanged')) {
            $objToSave->setChanged(false);
        }
        return (null !== $res && false !== $res);
    }

    /**
     * Returns the value of a field of a given object. It'll try to use a
     * getter first if defined. If not defined, we'll use the reflection API.
     *
     * @param mixed $targetObject
     * @param string $field
     * @return mixed
     * @throws \ReflectionException
     */
    private static function getField($targetObject, $field)
    {
        $result = null;
        $method = 'get' . ucfirst($field);
        if (method_exists($targetObject, $method)) {
            $result = $targetObject->$method();
        } else {
            $reflection = self::getReflectionClass($targetObject);
            if ($reflection->hasProperty($field)) {
                $property = $reflection->getProperty($field);
                $property->setAccessible(true);
                $result = $property->getValue($targetObject);
                $property->setAccessible(false);
            }
        }
        if (is_array($result) || is_object($result)) {
            $result = json_encode($result, JSON_UNESCAPED_UNICODE);
        }
        return $result;
    }

    /**
     * Returns the reflection class for the given object.
     *
     * @param  object $targetObject
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    private static function getReflectionClass($targetObject)
    {
        $reflections = [];
        $uuid = spl_object_hash($targetObject);
        if (!isset($reflections[$uuid])) {
            $reflections[$uuid] = new \ReflectionClass($targetObject);
        }
        return $reflections[$uuid];
    }

    /**
     *
     *
     * @param mixed $objectToAnalyze
     * @return array List of fields
     * @throws \RuntimeException
     * @throws \ReflectionException
     */
    private static function getFields($objectToAnalyze)
    {
        $table = is_string($objectToAnalyze) ? $objectToAnalyze : self::getTableName($objectToAnalyze);
        if (isset(self::$fieldsCache[$table])) {
            return self::$fieldsCache[$table];
        }
        $reflection = is_object($objectToAnalyze) ? self::getReflectionClass($objectToAnalyze) : new \ReflectionClass($objectToAnalyze);
        $properties = $reflection->getProperties();
        self::$fieldsCache[$table] = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            if ('_' !== $name[0]) {
                self::$fieldsCache[$table][] = $name;
            }
        }
        if (empty(self::$fieldsCache[$table])) {
            throw new \RuntimeException('No fields found for class ' . get_class($objectToAnalyze));
        }
        return self::$fieldsCache[$table];
    }

    /**
     * Returns the name of the table where to save entities.
     *
     * @param $targetObject
     * @return string
     */
    private static function getTableName($targetObject)
    {
        if (method_exists($targetObject, 'getTableName')) {
            return $targetObject->getTableName();
        }
        return get_class($targetObject);
    }

    /**
     * Forces the value of a field of a given object, even if this field is
     * not accessible.
     *
     * @param object $targetObject The entity to alter
     * @param string $field The name of the member to alter
     * @param mixed $value The value to give to the member
     * @throws \ReflectionException
     */
    private static function setField($targetObject, $field, $value)
    {
        $method = 'set' . ucfirst($field);
        if (method_exists($targetObject, $method)) {
            $targetObject->$method($value);
        } else {
            $reflection = self::getReflectionClass($targetObject);
            if ($reflection->hasProperty($field)) {
                throw new \InvalidArgumentException('Unknown field ' . get_class($targetObject) . '::' . $field);
            }
            $property = $reflection->getProperty($field);
            $property->setAccessible(true);
            $property->setValue($targetObject, $value);
            $property->setAccessible(false);
        }
    }

    /**
     * Builds the elements for an SQL query. It will return two lists, the
     * first being the list of parts "key= :key" to inject in the SQL, the
     * second being the mapping of these parameters to the values.
     *
     * @param mixed $targetObject
     * @return array
     * @throws \ReflectionException
     */
    private static function buildQuery($targetObject)
    {
        $parameters = [];
        $sql = [];
        foreach (self::getFields($targetObject) as $field) {
            $sql[] = '`' . $field . '` = :' . $field;
            $parameters[$field] = self::getField($targetObject, $field);
        }
        return [$sql, $parameters];
    }

    /**
     * Get one row data from database
     *
     * @param string $query SQL query
     * @param array $params Query params
     *
     * @return mixed Associative array with data
     *
     * @throws CoreException
     */
    public static function &getOne(string $query, array $params = [])
    {
        return self::Prepare($query, $params, self::FETCH_TYPE_ROW, \PDO::FETCH_ASSOC);
    }

    /**
     * Get the last insert id
     * @TODO: ???? Ca me parait dangereux si il y a une écriture en bdd entre temps
     * @return mixed
     * @throws CoreException
     */
    public static function getLastInsertId()
    {
        if (!isset(self::$sharedInstance)) {
            throw new CoreException('DB : Aucune connection active - impossible d\'avoir le dernier ID inséré');
        }
        return self::$sharedInstance->connection->lastInsertId();
    }

    /**
     * Refresh value of an object
     *
     * @param mixed $objectToRefresh
     *
     * @return bool True if refresh success
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function refresh($objectToRefresh)
    {
        if (is_subclass_of($objectToRefresh, 'EntityInterface') || !self::getField($objectToRefresh, 'id')) {
            throw new CoreException('DB ne peut rafraîchir l\'objet sans son ID');
        }
        $parameters = ['id' => self::getField($objectToRefresh, 'id')];
        $sql = 'SELECT ' . self::buildField(get_class($objectToRefresh)) .
            ' FROM `' . self::getTableName($objectToRefresh) . '` ' .
            ' WHERE ';
        foreach ($parameters as $field => $value) {
            if ($value != '') {
                $sql .= '`' . $field . '` = :' . $field . ' AND ';
            } else {
                unset($parameters[$field]);
            }
        }
        // For last AND added to the query WHERE cond AND cond AND ???
        $sql .= '1';
        $newObject = self::getOneObject($sql, $parameters, get_class($objectToRefresh));
        if (!is_object($newObject)) {
            return false;
        }
        foreach (self::getFields($objectToRefresh) as $field) {
            $reflection = self::getReflectionClass($objectToRefresh);
            $property = $reflection->getProperty($field);
            if (!$reflection->hasProperty($field)) {
                throw new \InvalidArgumentException('Unknown field ' . get_class($objectToRefresh) . '::' . $field);
            }
            $property->setAccessible(true);
            $property->setValue($objectToRefresh, self::getField($newObject, $field));
            $property->setAccessible(false);
        }
        return true;
    }

    /**
     * Build fields for query
     * @param $className
     * @param string $prefix
     * @return string
     * @throws \ReflectionException
     */
    public static function buildField($className, $prefix = '')
    {
        $className = is_string($className) ? $className : self::getTableName($className);
        $code = $prefix . $className;
        if (isset(self::$fieldsQuery[$code])) {
            return self::$fieldsQuery[$code];
        } else {
            $fields = [];
            foreach (self::getFields($className) as $field) {
                if ('_' !== $field[0]) {
                    if ($prefix != '') {
                        $fields[] = '`' . $prefix . '`.' . '`' . $field . '`';
                    } else {
                        $fields[] = '`' . $field . '`';
                    }
                }
            }
            self::$fieldsQuery[$code] = implode(', ', $fields);
            return self::$fieldsQuery[$code];
        }
    }

    /**
     * Get one object from database
     *
     * @param string $query SQL query
     * @param array $params Query params
     * @param string $objectClassName Class of object
     *
     * @return mixed|null Instance of the class
     *
     * @throws CoreException
     */
    public static function &getOneObject(string $query, array $params, string $objectClassName)
    {
        return self::Prepare($query, $params, self::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, $objectClassName);
    }

    /**
     * Get list of objects filtered
     *
     * @param array $filters Filtres à appliquer
     * @param mixed $objectType Objet sur lequel appliquer les filtres
     * @return array List of objects filtered
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function getWithFilter(array $filters, $objectType)
    {
        // operators have to remain in this order. If you put '<' before '<=', algorithm won't make the difference & will think a '<=' is a '<'
        $operators = ['!=', '<=', '>=', '<', '>', 'NOT LIKE', 'LIKE', '='];
        $fields = self::getFields($objectType);
        $reflectedClass = self::getReflectionClass($objectType)->getName();
        // create query
        $query = 'SELECT ' . self::buildField($reflectedClass) . ' FROM ' . $reflectedClass . '';
        $values = [];
        $where = ' WHERE ';
        foreach ($fields as $property) {
            foreach ($filters as $key => $value) {
                if ($property == $key && $value != '') {
                    // traitement à faire sur value pour obtenir l'opérateur
                    $thereIsOperator = false;
                    $operatorInformation = [
                        'index' => -1,
                        'value' => '=', // by default '='
                        'length' => 0,
                    ];
                    foreach ($operators as $operator) {
                        if (($index = strpos($value, $operator)) !== false) {
                            $thereIsOperator = true;
                            $operatorInformation['index'] = $index;
                            $operatorInformation['value'] = $operator;
                            $operatorInformation['length'] = strlen($operator);
                            break;
                        }
                    }
                    if ($thereIsOperator) {
                        // extract operator from value
                        $value = substr($value, $operatorInformation['length'] + 1); // +1 because of space
                        // add % % to LIKE operator
                        if (in_array($operatorInformation['value'], ['LIKE', 'NOT LIKE'])) {
                            $value = '%' . $value . '%';
                        }
                    }

                    $where .= $property . ' ' . $operatorInformation['value'] . ' :' . $property . ' AND ';
                    $values[$property] = $value;
                    break;
                }
            }
        }
        if ($where != ' WHERE ') {
            $where = substr($where, 0, strlen($where) - 5); // on enlève le dernier ' AND '
            $query .= $where;
        }
        // si values contient id, on sait qu'il n'y aura au plus qu'une valeur
        return self::Prepare($query . ';', $values, in_array('id', $values) ? self::FETCH_TYPE_ROW : self::FETCH_TYPE_ALL);
    }

    /**
     * Deletes an object.
     *
     * @param mixed $objectToRemove
     * @return boolean
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function remove($objectToRemove)
    {
        if (method_exists($objectToRemove, 'preRemove')) {
            if ($objectToRemove->preRemove() === false) {
                return false;
            }
        }
        list(, $parameters) = self::buildQuery($objectToRemove);
        $sql = 'DELETE FROM `' . self::getTableName($objectToRemove) . '` WHERE ';
        if (isset($parameters['id'])) {
            $sql .= '`id` = :id AND ';
            $parameters = ['id' => $parameters['id']];
        } else {
            foreach ($parameters as $field => $value) {
                if ($value != '') {
                    $sql .= '`' . $field . '` = :' . $field . ' AND ';
                } else {
                    unset($parameters[$field]);
                }
            }
        }
        $sql .= '1';
        $res = self::getOne($sql, $parameters);
        $reflection = self::getReflectionClass($objectToRemove);
        if ($reflection->hasProperty('id')) {
            self::setField($objectToRemove, 'id', null);
        }
        if (method_exists($objectToRemove, 'postRemove')) {
            $objectToRemove->postRemove();
        }
        return null !== $res && false !== $res;
    }

    /**
     * @param $_table
     * @return mixed
     * @throws CoreException
     */
    /**
     * @param $_table
     * @return mixed
     * @throws CoreException
     */
    /**
     * @param $_table
     * @return mixed
     * @throws CoreException
     */
    public static function checksum($_table)
    {
        $sql = 'CHECKSUM TABLE ' . $_table;
        $result = self::getOne($sql);
        return $result['Checksum'];
    }

    /**
     * Lock an entity.
     *
     * @param object $objectToLock
     * @return boolean
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function lock($objectToLock)
    {
        if (method_exists($objectToLock, 'preLock')) {
            if ($objectToLock->preLock() === false) {
                return false;
            }
        }
        list(, $parameters) = self::buildQuery($objectToLock);
        $sql = 'SELECT * FROM ' . self::getTableName($objectToLock) . ' WHERE ';
        foreach ($parameters as $field => $value) {
            if ($value != '') {
                $sql .= '`' . $field . '` = :' . $field . ' AND ';
            } else {
                unset($parameters[$field]);
            }
        }
        $sql .= '1 LOCK IN SHARE MODE';
        $res = self::getOne($sql, $parameters);
        if (method_exists($objectToLock, 'postLock')) {
            $objectToLock->postLock();
        }
        return null !== $res && false !== $res;
    }

    /**
     *
     * @param $_database
     * @param string $_table
     * @param bool $_verbose
     * @param int $_loop
     * @return bool
     * @throws \Exception
     */
    public static function compareAndFix($_database, $_table = 'all', $_verbose = false, $_loop = 0)
    {
        $result = self::compareDatabase($_database);
        $error = '';
        foreach ($result as $tname => $tinfo) {
            if ($_table != 'all' && $tname != $_table) {
                continue;
            }
            if ($tinfo['sql'] != '') {
                try {
                    if ($_verbose) {
                        echo "\nFix : " . $tinfo['sql'];
                    }
                    self::exec($tinfo['sql']);
                } catch (\Exception $e) {
                    $error .= $e->getMessage() . "\n";
                }
            }
            if (isset($tinfo['indexes']) && count($tinfo['indexes']) > 0) {
                foreach ($tinfo['indexes'] as $iname => $iinfo) {
                    if (!isset($iinfo['presql']) || trim($iinfo['presql']) == '') {
                        continue;
                    }
                    try {
                        if ($_verbose) {
                            echo "\nFix : " . $iinfo['presql'];
                        }
                        self::exec($iinfo['presql']);
                    } catch (\Exception $e) {
                        $error .= $e->getMessage() . "\n";
                    }
                }

            }
            if (isset($tinfo['fields']) && count($tinfo['fields']) > 0) {
                foreach ($tinfo['fields'] as $fname => $finfo) {
                    if (!isset($finfo['sql']) || trim($finfo['sql']) == '') {
                        continue;
                    }
                    try {
                        if ($_verbose) {
                            echo "\nFix : " . $finfo['sql'];
                        }
                        self::exec($finfo['sql']);
                    } catch (\Exception $e) {
                        $error .= $e->getMessage() . "\n";
                    }
                }
            }
            if (isset($tinfo['indexes']) && count($tinfo['indexes']) > 0) {
                foreach ($tinfo['indexes'] as $iname => $iinfo) {
                    if (!isset($iinfo['sql']) || trim($iinfo['sql']) == '') {
                        continue;
                    }
                    try {
                        if ($_verbose) {
                            echo "\nFix : " . $iinfo['sql'];
                        }
                        self::exec($iinfo['sql']);
                    } catch (\Exception $e) {
                        $error .= $e->getMessage() . "\n";
                    }
                }
            }
        }
        if (trim($error) != '') {
            if ($_loop < 1) {
                return self::compareAndFix($_database, $_table, $_verbose, ($_loop + 1));
            }
            throw new CoreException($error);
        }
        return true;
    }

    /**
     * @param $database
     * @return array
     * @throws CoreException
     */
    /**
     * @param $database
     * @return array
     * @throws CoreException
     */
    /**
     * @param $database
     * @return array
     * @throws CoreException
     */
    private static function compareDatabase($database)
    {
        $result = [];
        foreach ($database['tables'] as $table) {
            $result = array_merge($result, self::compareTable($table));
        }
        return $result;
    }

    /**
     * @param $_table
     * @return array
     * @throws CoreException
     */
    /**
     * @param $_table
     * @return array
     * @throws CoreException
     */
    /**
     * @param $_table
     * @return array
     * @throws CoreException
     */
    private static function compareTable($_table)
    {
        try {
            $describes = self::getAll('describe `' . $_table['name'] . '`', []);
        } catch (\Exception $e) {
            $describes = [];
        }

        $result = [$_table['name'] => ['status' => 'ok', 'fields' => [], 'indexes' => [], 'sql' => '']];
        if (count($describes) == 0) {
            $result = [$_table['name'] => [
                'status' => 'nok',
                'message' => 'Not found',
                'sql' => 'CREATE TABLE IF NOT EXISTS ' . '`' . $_table['name'] . '` (',
            ]];
            foreach ($_table['fields'] as $field) {
                $result[$_table['name']]['sql'] .= "\n" . '`' . $field['name'] . '`';
                $result[$_table['name']]['sql'] .= self::buildDefinitionField($field);
                $result[$_table['name']]['sql'] .= ',';
            }
            $result[$_table['name']]['sql'] .= "\n" . 'primary key(';
            foreach ($_table['fields'] as $field) {
                if (isset($field['key']) && $field['key'] == 'PRI') {
                    $result[$_table['name']]['sql'] .= '`' . $field['name'] . '`,';
                }
            }
            $result[$_table['name']]['sql'] = trim($result[$_table['name']]['sql'], ',');
            $result[$_table['name']]['sql'] .= ')';
            $result[$_table['name']]['sql'] .= ')' . "\n";
            if (!isset($_table['engine'])) {
                $_table['engine'] = 'InnoDB';
            }
            $result[$_table['name']]['sql'] .= ' ENGINE ' . $_table['engine'] . ";\n";
            foreach ($_table['indexes'] as $index) {
                $result[$_table['name']]['sql'] .= "\n" . self::buildDefinitionIndex($index, $_table['name']) . ';';
            }
            $result[$_table['name']]['sql'] = trim($result[$_table['name']]['sql'], ';');
            return $result;
        }
        $forceRebuildIndex = false;
        foreach ($_table['fields'] as $field) {
            $found = false;
            foreach ($describes as $describe) {
                if ($describe['Field'] != $field['name']) {
                    continue;
                }
                $result[$_table['name']]['fields'] = array_merge($result[$_table['name']]['fields'], self::compareField($field, $describe, $_table['name']));
                if (isset($result[$_table['name']]['fields'][$field['name']]) && $result[$_table['name']]['fields'][$field['name']]['status'] == 'nok') {
                    $forceRebuildIndex = true;
                }
                $found = true;
            }
            if (!$found) {
                $result[$_table['name']]['fields'][$field['name']] = [
                    'status' => 'nok',
                    'message' => 'Not found',
                    'sql' => 'ALTER TABLE `' . $_table['name'] . '` ADD `' . $field['name'] . '`'
                ];
                $result[$_table['name']]['fields'][$field['name']]['sql'] .= self::buildDefinitionField($field);
            }
        }
        foreach ($describes as $describe) {
            $found = false;
            foreach ($_table['fields'] as $field) {
                if ($describe['Field'] == $field['name']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result[$_table['name']]['fields'][$describe['Field']] = [
                    'status' => 'nok',
                    'message' => 'Should not exist',
                    'sql' => 'ALTER TABLE `' . $_table['name'] . '` DROP `' . $describe['Field'] . '`'
                ];
            }
        }
        $showIndexes = self::prepareIndexCompare(self::getAll('show index from `' . $_table['name'] . '`', []));
        foreach ($_table['indexes'] as $index) {
            $found = false;
            foreach ($showIndexes as $showIndex) {
                if ($showIndex['Key_name'] != $index['Key_name']) {
                    continue;
                }
                $result[$_table['name']]['indexes'] = array_merge($result[$_table['name']]['indexes'], self::compareIndex($index, $showIndex, $_table['name'], $forceRebuildIndex));
                $found = true;
            }
            if (!$found) {
                $result[$_table['name']]['indexes'][$index['Key_name']] = [
                    'status' => 'nok',
                    'message' => 'Not found',
                    'sql' => ''
                ];
                $result[$_table['name']]['indexes'][$index['Key_name']]['sql'] .= self::buildDefinitionIndex($index, $_table['name']);
            }
        }
        foreach ($showIndexes as $showIndex) {
            $found = false;
            foreach ($_table['indexes'] as $index) {
                if ($showIndex['Key_name'] == $index['Key_name']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result[$_table['name']]['indexes'][$showIndex['Key_name']] = [
                    'status' => 'nok',
                    'message' => 'Should not exist',
                    'sql' => 'ALTER TABLE `' . $_table['name'] . '` DROP INDEX `' . $showIndex['Key_name'] . '`;'
                ];
            }
        }
        return $result;
    }

    /**
     * @param $_field
     * @return string
     */
    /**
     * @param $_field
     * @return string
     */
    /**
     * @param $_field
     * @return string
     */
    private static function buildDefinitionField($_field)
    {
        $return = ' ' . $_field['type'];
        if ($_field['null'] == 'NO') {
            $return .= ' NOT NULL';
        } else {
            $return .= ' NULL';
        }
        if ($_field['default'] != '') {
            $return .= ' DEFAULT "' . $_field['default'] . '"';
        }
        if ($_field['extra'] == 'auto_increment') {
            $return .= ' AUTO_INCREMENT';
        }
        return $return;
    }

    /**
     * @param $_index
     * @param $_table_name
     * @return string
     */
    /**
     * @param $_index
     * @param $_table_name
     * @return string
     */
    /**
     * @param $_index
     * @param $_table_name
     * @return string
     */
    private static function buildDefinitionIndex($_index, $_table_name)
    {
        if ($_index['Non_unique'] == 0) {
            $return = 'CREATE UNIQUE INDEX `' . $_index['Key_name'] . '` ON `' . $_table_name . '`' . ' (';
        } else {
            $return = 'CREATE INDEX `' . $_index['Key_name'] . '` ON `' . $_table_name . '`' . ' (';
        }
        foreach ($_index['columns'] as $value) {
            $return .= '`' . $value['column'] . '`';
            if ($value['Sub_part'] != null) {
                $return .= '(' . $value['Sub_part'] . ')';
            }
            $return .= ' ASC,';
        }
        $return = trim($return, ',');
        $return .= ')';
        return $return;
    }

    /**
     * @param $_ref_field
     * @param $_real_field
     * @param $_table_name
     * @return array
     */
    /**
     * @param $_ref_field
     * @param $_real_field
     * @param $_table_name
     * @return array
     */
    /**
     * @param $_ref_field
     * @param $_real_field
     * @param $_table_name
     * @return array
     */
    private static function compareField($_ref_field, $_real_field, $_table_name)
    {
        $return = [$_ref_field['name'] => ['status' => 'ok', 'sql' => '']];
        if ($_ref_field['type'] != $_real_field['Type']) {
            $return[$_ref_field['name']]['status'] = 'nok';
            $return[$_ref_field['name']]['message'] = 'Type nok';
        }
        if ($_ref_field['null'] != $_real_field['Null']) {
            $return[$_ref_field['name']]['status'] = 'nok';
            $return[$_ref_field['name']]['message'] = 'Null nok';
        }
        if ($_ref_field['default'] != $_real_field['Default']) {
            $return[$_ref_field['name']]['status'] = 'nok';
            $return[$_ref_field['name']]['message'] = 'Default nok';
        }
        if ($_ref_field['extra'] != $_real_field['Extra']) {
            $return[$_ref_field['name']]['status'] = 'nok';
            $return[$_ref_field['name']]['message'] = 'Extra nok';
        }
        if ($return[$_ref_field['name']]['status'] == 'nok') {
            $return[$_ref_field['name']]['sql'] = 'ALTER TABLE `' . $_table_name . '` MODIFY COLUMN `' . $_ref_field['name'] . '` ';
            $return[$_ref_field['name']]['sql'] .= self::buildDefinitionField($_ref_field);
        }
        return $return;
    }

    /**
     * @param $indexes
     * @return array
     */
    /**
     * @param $indexes
     * @return array
     */
    /**
     * @param $indexes
     * @return array
     */
    private static function prepareIndexCompare($indexes)
    {
        $return = [];
        foreach ($indexes as $index) {
            if ($index['Key_name'] == 'PRIMARY') {
                continue;
            }
            if (!isset($return[$index['Key_name']])) {
                $return[$index['Key_name']] = [
                    'Key_name' => $index['Key_name'],
                    'Non_unique' => 0,
                    'columns' => [],
                ];
            }
            $return[$index['Key_name']]['Non_unique'] = $index['Non_unique'];
            $return[$index['Key_name']]['columns'][$index['Seq_in_index']] = ['column' => $index['Column_name'], 'Sub_part' => $index['Sub_part']];
        }
        return $return;
    }

    /**
     * @param $_ref_index
     * @param $_real_index
     * @param $_table_name
     * @param bool $_forceRebuild
     * @return array
     */
    private static function compareIndex($_ref_index, $_real_index, $_table_name, $_forceRebuild = false)
    {
        $return = [$_ref_index['Key_name'] => ['status' => 'ok', 'presql' => '', 'sql' => '']];
        if ($_ref_index['Non_unique'] != $_real_index['Non_unique']) {
            $return[$_ref_index['Key_name']]['status'] = 'nok';
            $return[$_ref_index['Key_name']]['message'] = 'Non_unique nok';
        }
        if ($_ref_index['columns'] != $_real_index['columns']) {
            $return[$_ref_index['Key_name']]['status'] = 'nok';
            $return[$_ref_index['Key_name']]['message'] = 'Columns nok';
        }
        if ($_forceRebuild) {
            $return[$_ref_index['Key_name']]['status'] = 'nok';
            $return[$_ref_index['Key_name']]['message'] = 'Force rebuild';
        }
        if ($return[$_ref_index['Key_name']]['status'] == 'nok') {
            $return[$_ref_index['Key_name']]['presql'] = 'ALTER TABLE `' . $_table_name . '` DROP INDEX `' . $_ref_index['Key_name'] . '`;';
            $return[$_ref_index['Key_name']]['sql'] = "\n" . self::buildDefinitionIndex($_ref_index, $_table_name);
        }
        return $return;
    }

    /**
     * Block object cloning
     */
    public function __clone()
    {
        trigger_error('DB : Cloner cet objet n\'est pas permis', E_USER_ERROR);
    }

}
