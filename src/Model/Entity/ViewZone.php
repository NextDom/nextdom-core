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
use NextDom\Managers\ViewDataManager;
use NextDom\Managers\ViewManager;

/**
 * Viewzone
 *
 * @ORM\Table(name="viewZone", indexes={@ORM\Index(name="fk_zone_view1", columns={"view_id"})})
 * @ORM\Entity
 */
class ViewZone
{

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=true)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127, nullable=true)
     */
    protected $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    protected $position;

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
     * @var \NextDom\Model\Entity\View
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\View")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="view_id", referencedColumnName="id")
     * })
     */
    protected $view_id;

    protected $_changed = false;


    /*     * *********************Methode d'instance************************* */

    public function save() {
        return \DB::save($this);
    }

    public function remove() {
        return \DB::remove($this);
    }

    public function getviewData() {
        return ViewDataManager::byviewZoneId($this->getId());
    }

    public function getView() {
        return ViewManager::byId($this->getView_id());
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

    public function getView_id() {
        return $this->view_id;
    }

    public function setView_id($_view_id) {
        $this->_changed = Utils::attrChanged($this->_changed,$this->view_id,$_view_id);
        $this->view_id = $_view_id;
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

    public function getName() {
        return $this->name;
    }

    public function setName($_name) {
        $this->_changed = Utils::attrChanged($this->_changed,$this->name,$_name);
        $this->name = $_name;
        return $this;
    }

    public function getPosition() {
        return $this->position;
    }

    public function setPosition($_position) {
        $this->_changed = Utils::attrChanged($this->_changed,$this->position,$_position);
        $this->position = $_position;
        return $this;
    }

    public function getConfiguration($_key = '', $_default = '') {
        return Utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    public function setConfiguration($_key, $_value) {
        $configuration =  Utils::setJsonAttr($this->configuration, $_key, $_value);
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
        return 'viewZone';
    }
}
