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

use NextDom\Helpers\DBHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ViewZoneManager;
use NextDom\Model\BaseEntity;

/**
 * Viewdata
 *
 * @ORM\Table(name="viewData", uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"viewZone_id", "link_id", "type"})}, indexes={@ORM\Index(name="fk_data_zone1_idx", columns={"viewZone_id"}), @ORM\Index(name="order", columns={"order", "viewZone_id"})})
 * @ORM\Entity
 */
class ViewData extends BaseEntity
{

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=true)
     */
    protected $order = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=true)
     */
    protected $type;

    /**
     * @var integer
     *
     * ORM\Column(name="link_id", type="integer", nullable=true)
     */
    protected $link_id;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    protected $configuration;

    /**
     * @var \NextDom\Model\Entity\ViewZone
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Viewzone")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="viewZone_id", referencedColumnName="id")
     * })
     */
    protected $viewZone_id;

    /**
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        return DBHelper::save($this);
    }

    /**
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function remove()
    {
        return DBHelper::remove($this);
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
        $this->_changed = Utils::attrChanged($this->_changed, $this->viewZone_id, $_viewZone_id);
        $this->viewZone_id = $_viewZone_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param $_order
     * @return $this
     */
    public function setOrder($_order)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->order, $_order);
        $this->order = $_order;
        return $this;
    }

    /**
     * @return bool
     */
    public function getLinkObject()
    {
        $type = $this->getType();
        if (class_exists($type)) {
            return $type::byId($this->getLink_id());
        }
        return false;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $_type
     * @return $this
     */
    public function setType($_type)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->type, $_type);
        $this->type = $_type;
        return $this;
    }

    /**
     * @return int
     */
    public function getLink_id()
    {
        return $this->link_id;
    }

    /**
     * @param $_link_id
     * @return $this
     */
    public function setLink_id($_link_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->link_id, $_link_id);
        $this->link_id = $_link_id;
        return $this;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getConfiguration($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setConfiguration($_key, $_value)
    {
        $configuration = Utils::setJsonAttr($this->configuration, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->configuration, $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * Get the name of the SQL table where data is stored.
     *
     * @return string
     */
    public function getTableName()
    {
        return 'viewData';
    }

}
