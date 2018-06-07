<?php
/**
 * Created by PhpStorm.
 * User: luc
 * Date: 06/06/2018
 * Time: 20:59
 */

namespace NextDom\src\DAO;

use NextDom\Interfaces\DAOInterface;

abstract class DAO implements DAOInterface
{
    /**
     * Database connection
     *
     */
    private $db;

    /**
     * Constructor
     *
     * @param db
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Grants access to the database connection object
     * @return db
     */
    protected function getDb()
    {
        return $this->db;
    }

    /**
     * @param array $row
     * Builds a domain object from a DB row.
     * Must be overridden by child classes.
     */
    protected abstract function buildDomainObject(array $row);

    /**
     * @param array $array
     * @return array
     */
    public function buildListDomainObject(array $array): array
    {
        $list = [];
        foreach ($array as $row) {
            $list[] = $this->buildDomainObject($row);
        }
        return $list;
    }


}