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

namespace NextDom\Models\DAO;

use NextDom\Models\Domain\Fragment;

class FragmentDAO extends DAO
{

    private $tableName = '`update`';

    /**
     *
     * @param Fragment $fragment
     * @return Fragment
     */
    public function save(Fragment $fragment): Fragment
    {
        $fragmentData = [
            ':type' => $fragment->getType(),
            ':logicalId' => $fragment->getLogicalId(),
            ':name' => $fragment->getName(),
            ':localVersion' => $fragment->getLocalVersion(),
            ':remoteVersion' => $fragment->getRemoteVersion(),
            ':status' => $fragment->getStatus(),
            ':configuration' => $fragment->getConfiguration(),
            ':source' => $fragment->getSource(),
        ];

        if ($fragment->getId() !== null) {
            $fragmentData['id'] = $fragmentData->getId();
            $sql = 'UPDATE SET '
                . $this->tableName
                . ' (type, logicalId, name, localVersion, remoteVersion, status, configuration, source)'
                . ' VALUES'
                . ' (:type, :logicalId, :name, :localVersion, :remoteVersion, :status, :configuration, :source)'
                . ' WHERE '
                . 'id = :id;';
            $fragment = $this->db->prepare($sql);
            $fragment->execute($fragmentData);
        } else {
            $sql = 'INSERT INTO '
                . $this->tableName
                . ' (type, logicalId, name, localVersion, remoteVersion, status, configuration, source)'
                . ' VALUES '
                . '(:type, :logicalId, :name, :localVersion, :remoteVersion, :status, :configuration, :source)';
            $insert = $this->db->prepare($sql);
            $insert->execute($fragmentData);
            $fragment->setId($insert->lastInsertId());
        }

        return $fragment;
    }

    /**
     * @param array $row
     *
     * @return Fragment
     */
    protected function buildDomainObject(array $row)
    {
        $fragment = (new Fragment())
            ->setId($row['id'])
            ->setType($row['eqType'])
            ->setLogicalId($row['logicalId'])
            ->setName($row['name'])
            ->setLocalVersion($row['localVersion'])
            ->setRemoteVersion($row['remoteVersion'])
            ->setStatus($row['status'])
            ->setConfiguration($row['configuration'])
            ->setSource($row['source']);
        return $fragment;
    }

}
