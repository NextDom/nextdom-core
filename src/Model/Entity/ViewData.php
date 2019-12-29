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
use NextDom\Managers\ViewZoneManager;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\ConfigurationEntity;
use NextDom\Model\Entity\Parents\LinkIdEntity;
use NextDom\Model\Entity\Parents\OrderEntity;
use NextDom\Model\Entity\Parents\TypeEntity;

/**
 * Viewdata
 *
 * @ORM\Table(name="viewData", uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"viewZone_id", "link_id", "type"})}, indexes={@ORM\Index(name="fk_data_zone1_idx", columns={"viewZone_id"}), @ORM\Index(name="order", columns={"order", "viewZone_id"})})
 * @ORM\Entity
 */
class ViewData extends BaseEntity
{
    const TABLE_NAME = NextDomObj::VIEW_DATA;

    use ConfigurationEntity, LinkIdEntity, TypeEntity, OrderEntity;

    /**
     * @var \NextDom\Model\Entity\ViewZone
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Viewzone")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="viewZone_id", referencedColumnName="id")
     * })
     */
    protected $viewZone_id;

    public function __construct()
    {
        if ($this->order === null) {
            $this->order = 0;
        }
    }

    /**
     * @return ViewZone|null
     * @throws \Exception
     */
    public function getviewZone()
    {
        return ViewZoneManager::byId($this->getviewZone_id());
    }

    /*     * **********************Getteur Setteur*************************** */

    /**
     * @return ViewZone
     */
    public function getviewZone_id()
    {
        return $this->viewZone_id;
    }

    /**
     * @param $_viewZone_id
     * @return $this
     */
    public function setviewZone_id($_viewZone_id)
    {
        $this->updateChangeState($this->viewZone_id, $_viewZone_id);
        $this->viewZone_id = $_viewZone_id;
        return $this;
    }
}
