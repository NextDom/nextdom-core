<?php

use NextDom\Singletons\ConnectDb;
use NextDom\Exceptions\DbException;

$app = [];

$app['db'] = function () {
    try {
        return ConnectDb::getInstance();
    } catch (DbException $exc) {
        throw new \Exception(__('Erreur de chargement de la class --> ' .  ConnectDb::class . ' Message -->' $exc->getMessage() , __FILE__));
    }
};

////////////////////////////////
////        NextDom DAO    /////
////////////////////////////////
///
/**
 * @param $app
 * @return \NextDom\src\DAO\CmdDAO
 */
$app['DAO.Cmd'] = function($app){
    return (new NextDom\src\DAO\CmdDAO($app['db']));
};