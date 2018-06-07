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
 
$app = [];

$app['db'] = function() {
    return \NextDom\Singletons\ConnectDb::getInstance();
};
 
////////////////////////////////
////        NextDom DAO    /////
////////////////////////////////
 
/**
 * @param $app
 * @return \NextDom\src\DAO\CmdDAO
 */
$app['DAO.Cmd'] = function ($app) {
    return new NextDom\src\DAO\CmdDAO($app['db']);
};

