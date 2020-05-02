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

use NextDom\Enums\NextDomObj;
use NextDom\Exceptions\CoreException;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\NameEntity;

/**
 * Dash
 *
 */
class Dash extends BaseEntity
{
    const TABLE_NAME = NextDomObj::DASH;

    use NameEntity;

    /**
     * Data of the dash (JSON encoded)
     *
     * @var string
     */
    protected $data;

    /**
     * Get the data of the dash
     *
     * @return string Text of the note
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set note data
     *
     * @param $newData
     * @return $this
     */
    public function setData($newData)
    {
        $this->updateChangeState($this->data, $newData);
        $this->data = $newData;
        return $this;
    }
}
