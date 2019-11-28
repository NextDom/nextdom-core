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

namespace NextDom\Managers;

use NextDom\Helpers\DBHelper;
use NextDom\Model\Entity\ScenarioSubElement;

/**
 * Class ScenarioSubElementManager
 * @package NextDom\Managers
 */
class ScenarioSubElementManager
{
    const DB_CLASS_NAME = 'scenarioSubElement';
    const CLASS_NAME = ScenarioSubElement::class;

    /**
     * Obtain a sub-element of a scenario from its identifier
     *
     * @param $id
     *
     * @return array|mixed|null
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        $values = ['id' => $id];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * Get the sub-elements of a scenario
     *
     * @param string $scenarioElementId Identifier of the scenario element
     * @param string $filterByType Filter a type of sub-elements
     *
     * @return array|mixed|null
     *
     * @throws \Exception
     */
    public static function byScenarioElementId($scenarioElementId, $filterByType = '')
    {
        $values = [
            'scenarioElement_id' => $scenarioElementId,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE scenarioElement_id=:scenarioElement_id ';
        if ($filterByType != '') {
            $values['type'] = $filterByType;
            $sql .= ' AND type=:type ';
            return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
        } else {
            $sql .= ' ORDER BY `order`';
            return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
        }
    }

}
