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

namespace NextDom\Model\Entity;

/**
 * Historyarch
 *
 * @ORM\Table(name="historyArch", uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"cmd_id", "datetime"})}, indexes={@ORM\Index(name="cmd_id_index", columns={"cmd_id"})})
 * @ORM\Entity
 */
class HistoryArch extends History
{
    protected $_tableName = 'historyArch';

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'historyArch';
    }
}
