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

use NextDom\Managers\Parents\BaseManager;
use NextDom\Managers\Parents\CommonManager;
use NextDom\Model\Entity\ScenarioSubElement;

/**
 * Class ScenarioSubElementManager
 * @package NextDom\Managers
 */
class ScenarioSubElementManager extends BaseManager
{
    use CommonManager;

    const DB_CLASS_NAME = '`scenarioSubElement`';
    const CLASS_NAME = ScenarioSubElement::class;

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
        $clauses = [
            'scenarioElement_id' => $scenarioElementId,
        ];
        if ($filterByType !== '') {
            $clauses['type'] = $filterByType;
            return static::getOneByClauses($clauses);
        }
        else {
            return static::getMultipleByClauses($clauses);
        }
    }

}
