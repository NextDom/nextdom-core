<?php

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

require_once('NextDomMarket.php');
require_once('DownloadManager.php');

/**
 * Classe des objets de Jeedom
 * // @TODO: Virer eqLogic
 */
class AlternativeMarketForJeedom extends eqLogic
{
    /**
     * Compare deux objets en fonction de la valeur 'order'
     *
     * @param AlternativeMarketForJeedom $obj1 Premier objet à comparer
     * @param AlternativeMarketForJeedom $obj2 Deuxième objet à comparer
     *
     * @return int|null 0 si =, -1 si $obj1 < $obj2, 1 si $obj1 > $obj2
     */
    public static function cmpByOrder($obj1, $obj2)
    {
        $result = null;
        $obj1Order = $obj1->getConfiguration()['order'];
        $obj2Order = $obj2->getConfiguration()['order'];
        if ($obj1Order == $obj2Order) {
            $result = 0;
        } else {
            if ($obj1Order < $obj2Order) {
                $result = -1;
            } else {
                $result = 1;
            }
        }
        return $result;
    }
}
