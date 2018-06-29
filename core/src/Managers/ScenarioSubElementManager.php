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

namespace NextDom\Managers;

class ScenarioSubElementManager
{
    const DB_CLASS_NAME = 'scenarioSubElement';
    const CLASS_NAME = 'scenarioSubElement';

    /**
     * Obtenir un sous élément d'un scénario à partir de son identifiant
     *
     * @param $id
     *
     * @return array|mixed|null
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        $values = array('id' => $id);
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Obtenir les sous-éléments d'un scénarios
     *
     * @param $scenarioElementId Identifiant de l'élément du scénario
     * @param string $filterByType Filtrer un type de sous-éléments
     *
     * @return array|mixed|null
     *
     * @throws \Exception
     */
    public static function byScenarioElementId($scenarioElementId, $filterByType = '')
    {
        $values = array('scenarioElement_id' => $scenarioElementId);
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE scenarioElement_id = :scenarioElement_id ';
        if ($filterByType != '') {
            $values['type'] = $filterByType;
            $sql .= 'AND `type` = :type ';
        }
        else {
            $sql .= 'ORDER BY `order`';
        }

        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

}