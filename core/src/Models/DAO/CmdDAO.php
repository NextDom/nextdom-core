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

namespace NextDom\src\Models\DAO;

use NextDom\src\Models\Domaine\Cmd;

class CmdDAO extends DAO
{

    private $tableName = 'cmd';

    /**
     * 
     * @param Cmd $cmd
     * @return Cmd
     */
    public function save(Cmd $cmd): Cmd
    {
        $cmdData = [
            ':eqType'        => $cmd->getEqType(),
            ':logicalId'     => $cmd->getLogicalId(),
            ':generic_type'  => $cmd->getGenericType(),
            ':order'         => $cmd->getOrder(),
            ':name'          => $cmd->getName(),
            ':configuration' => $cmd->getConfiguration(),
            ':template'      => $cmd->getTemplate(),
            ':isHistorized'  => $cmd->getisHistorized(),
            ':type'          => $cmd->getType(),
            ':subType'       => $cmd->getSubType(),
            ':unite'         => $cmd->getUnite(),
            ':display'       => $cmd->getDisplay(),
            ':isVisible'     => $cmd->getisVisible(),
            ':value'         => $cmd->getValue(),
            ':html'          => $cmd->getHtml(),
            ':alert'         => $cmd->getAlert(),
        ];

        implode(',', $cmdData);

        if ($cmd->getId() !== null) {
            $sql    = 'UPDATE SET '
                    . $this->tableName .
                    '(eqType, logicalId, generic_type, order, name, configuration, template, isHistorized, type, subType, unite, display, isVisible, value, html, alert)'
                    . ' VALUES '
                    . '(:eqType, :logicalId, :generic_type, :order, :name, :configuration, :template, :isHistorized, :type, :subType, :unite, :display, :isVisible, :value, :html, :alert)'
                    . 'where'
                    . 'id = ' . $cmd->getId() . ';';
            $update = $this->db->prepare($sql);
            $update->execute($cmdData);
        } else {
            $sql    = 'INSERT INTO '
                    . $this->tableName .
                    '(eqType, logicalId, generic_type, order, name, configuration, template, isHistorized, type, subType, unite, display, isVisible, value, html, alert)'
                    . ' VALUES '
                    . '(:eqType, :logicalId, :generic_type, :order, :name, :configuration, :template, :isHistorized, :type, :subType, :unite, :display, :isVisible, :value, :html, :alert)';
            $insert = $this->db->prepare($sql);
            $insert->execute($cmdData);
            $cmd->setId($insert->lastInsertId());
        }

        return $cmd;
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

}
