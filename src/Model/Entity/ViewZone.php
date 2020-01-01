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
use NextDom\Managers\ViewDataManager;
use NextDom\Managers\ViewManager;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\ConfigurationEntity;
use NextDom\Model\Entity\Parents\NameEntity;
use NextDom\Model\Entity\Parents\PositionEntity;
use NextDom\Model\Entity\Parents\TypeEntity;

/**
 * Viewzone
 *
 * @ORM\Table(name="viewZone", indexes={@ORM\Index(name="fk_zone_view1", columns={"view_id"})})
 * @ORM\Entity
 */
class ViewZone extends BaseEntity
{
    const TABLE_NAME = NextDomObj::VIEW_ZONE;

    use ConfigurationEntity, NameEntity, PositionEntity, TypeEntity;

    /**
     * @var \NextDom\Model\Entity\View
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\View")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="view_id", referencedColumnName="id")
     * })
     */
    protected $view_id;


    /*     * *********************Methode d'instance************************* */

    /**
     * @return array|mixed|null [ViewZone]|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function getViewData()
    {
        return ViewDataManager::byViewZoneId($this->getId());
    }

    /**
     * @return View|null
     * @throws \Exception
     */
    public function getView()
    {
        return ViewManager::byId($this->getView_id());
    }

    /**
     * @return View
     */
    public function getView_id()
    {
        return $this->view_id;
    }

    /**
     * @param $_view_id
     * @return $this
     */
    public function setView_id($_view_id)
    {
        $this->updateChangeState($this->view_id, $_view_id);
        $this->view_id = $_view_id;
        return $this;
    }
}
