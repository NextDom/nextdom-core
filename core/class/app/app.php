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
 

global $CONFIG;
 
$app = [];

$app['db'] = function($CONFIG) {
    // TU CONNECTES AVANT D'AVOIR LES INFOS
    \NextDom\Singletons\ConnectDb::getInstance()
            ->setHost($CONFIG['db']['host'])
            ->setPort($CONFIG['db']['port'])
            ->setDbName($CONFIG['db']['dbname'])
            ->setUserName($CONFIG['db']['username'])
            ->setPassword($CONFIG['db']['password'])
    ;
    echo "JAMAIS APPELE";
    var_dump(\NextDom\Singletons\ConnectDb::getInstance());
    return \NextDom\Singletons\ConnectDb::getInstance();
};

\NextDom\Singletons\ConnectDb::getInstance();
//var_dump($CONFIG);
////////////////////////////////
////        NextDom DAO    /////
////////////////////////////////
/**
 * @param $app
 * @return \NextDom\src\DAO\CmdDAO
 */
$app['DAO.Cmd'] = function ($app) {

    return new NextDom\src\DAO\CmdDAO($app['db']::getInstance());
};

