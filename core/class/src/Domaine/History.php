<?php
/* This file is part of NextDom.
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


namespace NextDom\src\Domaine;


class History
{
    /**
     * @var int
     */
    private $cmdId;

    /**
     * @var string
     */
    private $datetime;

    /**
     * @var string
     */
    private $value;

    /**
     * @return int
     */
    public function getCmdId(): int
    {
        return $this->cmdId;
    }

    /**
     * @param int $cmdId
     * @return History
     */
    public function setCmdId(int $cmdId): History
    {
        $this->cmdId = $cmdId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDatetime(): string
    {
        return $this->datetime;
    }

    /**
     * @param string $datetime
     * @return History
     */
    public function setDatetime(string $datetime): History
    {
        $this->datetime = $datetime;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return History
     */
    public function setValue(string $value): History
    {
        $this->value = $value;
        return $this;
    }


}