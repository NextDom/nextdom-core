<?php


namespace NextDom\Singletons;

use NextDom\Exceptions\DbException;

class ConnectDb
{

    /**
     * Instance de la classe PDO
     *
     * @var PDO
     * @access private
     */
    private $PDOInstance = null;
    /**
     * @var
     */
    private static $instance;

    /**
     * @name __construct()
     * @access private
     * @return object or DbException
     */
    private function __construct()
    {
        global $CONFIG;

        try {
            $this->PDOInstance = new \PDO('mysql:host=' . $CONFIG['db']['host'] . ';port=' . $CONFIG['db']['port'] . ';dbname=' . $CONFIG['db']['dbname'], $CONFIG['db']['username'], $CONFIG['db']['password'],
                [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                ]);
            $this->PDOInstance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        } catch (\PDOException $e) {
            throw new DbException('PDO Error : ' . $e->getMessage() . ' for bdd --> ' . self::PATH . self::IMPEDANCE, 500);
        }
    }

    /**
     * @name getInstance()
     * @return instance of DB
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}
