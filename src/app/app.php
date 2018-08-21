<?php

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

$app = [];

$app['db'] = function() {
//    $instance = \NextDom\Singletons\ConnectDb::getInstance();
    return \NextDom\Singletons\ConnectDb::getConnection();
};

///////////////////////////////
////        NextDom DAO    ////
///////////////////////////////

/**
 * @param $app
 * @return \NextDom\Models\DAO\CmdDAO
 */
$app['DAO.Cmd'] = function () use ($app) {
    return new NextDom\Models\DAO\CmdDAO($app['db']());
};
$app['DAO.Fragment'] = function() use ($app) {
    return new NextDom\Models\DAO\FragmentDAO($app['db']());
};

