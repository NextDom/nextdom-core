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

namespace NextDom\src\DAO;

use NextDom\src\Domaine\Cmd;

class CmdDAO extends DAO
{

    /**
     * @param int $id
     * @return db
     */
    public function getCmdById(int $id)
    {
        return $this->getDb();
    }

    public function save()
    {
        
    }

    /**
     * @param array $row
     * Builds a domain object from a DB row.
     * Must be overridden by child classes.
     */
    protected function buildDomainObject(array $row)
    {
        $cmd = (new Cmd())
                ->setId($row['id'])
                ->setEqLogicId($row['eqLogic_Id'])
                ->setType($row['eqType'])
                ->setLogicalId($row['logicalId'])
                ->setGenericType($row['generic_type'])
                ->setOrder($row['order'])
                ->setName($row['name'])
                ->setConfiguration($row['configuration'])
                ->setTemplate($row['template'])
                ->setIsHistorized($row['isHistorized'])
                ->setType($row['type'])
                ->setSubType($row['subType'])
                ->setUnite($row['unite'])
                ->setDisplay($row['display'])
                ->setIsVisible($row['isVisible'])
                ->setValue($row['value'])
                ->setHtml($row['html'])
                ->setAlert($row['alert']);
        return $cmd;
    }

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
