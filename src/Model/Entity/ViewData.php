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
use NextDom\Helpers\Utils;
use NextDom\Managers\ViewZoneManager;

/**
 * Viewdata
 *
 * @ORM\Table(name="viewData", uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"viewZone_id", "link_id", "type"})}, indexes={@ORM\Index(name="fk_data_zone1_idx", columns={"viewZone_id"}), @ORM\Index(name="order", columns={"order", "viewZone_id"})})
 * @ORM\Entity
 */
class ViewData
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
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \NextDom\Model\Entity\ViewZone
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Viewzone")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="viewZone_id", referencedColumnName="id")
     * })
     */
    protected $viewZone_id;

    protected $_changed = false;

    public function save() {
        return \DB::save($this);
    }

    public function remove() {
        return \DB::remove($this);
    }

    public function getviewZone() {
        return ViewZoneManager::byId($this->getviewZone_id());
    }

    /*     * **********************Getteur Setteur*************************** */

    public function getId() {
        return $this->id;
    }

    public function setId($_id) {
        $this->_changed = Utils::attrChanged($this->_changed,$this->id,$_id);
        $this->id = $_id;
        return $this;
    }

    public function getOrder() {
        return $this->order;
    }

    public function setOrder($_order) {
        $this->_changed = Utils::attrChanged($this->_changed,$this->order,$_order);
        $this->order = $_order;
        return $this;
    }

    public function getviewZone_id() {
        return $this->viewZone_id;
    }

    public function setviewZone_id($_viewZone_id) {
        $this->_changed = Utils::attrChanged($this->_changed,$this->viewZone_id,$_viewZone_id);
        $this->viewZone_id = $_viewZone_id;
        return $this;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($_type) {
        $this->_changed = Utils::attrChanged($this->_changed,$this->type,$_type);
        $this->type = $_type;
        return $this;
    }

    public function getLink_id() {
        return $this->link_id;
    }

    public function setLink_id($_link_id) {
        $this->_changed = Utils::attrChanged($this->_changed,$this->link_id,$_link_id);
        $this->link_id = $_link_id;
        return $this;
    }

    public function getLinkObject() {
        $type = $this->getType();
        if (class_exists($type)) {
            return $type::byId($this->getLink_id());
        }
        return false;
    }

    public function getConfiguration($_key = '', $_default = '') {
        return Utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    public function setConfiguration($_key, $_value) {
        $configuration = Utils::setJsonAttr($this->configuration, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed,$this->configuration,$configuration);
        $this->configuration = $configuration;
        return $this;
    }

    public function getChanged() {
        return $this->_changed;
    }

    public function setChanged($_changed) {
        $this->_changed = $_changed;
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
