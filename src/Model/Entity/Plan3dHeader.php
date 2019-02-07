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

use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\Plan3dManager;

/**
 * Plan3dheader
 *
 * @ORM\Table(name="plan3dHeader")
 * @ORM\Entity
 */
class Plan3dHeader
{

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127, nullable=true)
     */
    protected $name;

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

    protected $_changed = false;

    public function preSave()
    {
        if (trim($this->getName()) == '') {
            throw new \Exception(__('Le nom du l\'objet ne peut pas être vide'));
        }
    }

    public function save()
    {
        \DB::save($this);
    }

    public function remove()
    {
        $cibDir = NEXTDOM_ROOT . '/' . $this->getConfiguration('path', '');
        if (file_exists($cibDir) && $this->getConfiguration('path', '') != '') {
            rrmdir($cibDir);
        }
        NextDomHelper::addRemoveHistory(array('id' => $this->getId(), 'name' => $this->getName(), 'date' => date('Y-m-d H:i:s'), 'type' => 'plan3d'));
        \DB::remove($this);
    }

    public function getPlan3d()
    {
        return Plan3dManager::byPlan3dHeaderId($this->getId());
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
    }

    public function setName($_name)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->name, $_name);
        $this->name = $_name;
        return $this;
    }

    public function getConfiguration($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    public function setConfiguration($_key, $_value)
    {
        if ($_key == 'accessCode' && $_value != '' && !Utils::isSha512($_value)) {
            $_value = Utils::sha512($_value);
        }
        $configuration = Utils::setJsonAttr($this->configuration, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->configuration, $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    public function getChanged()
    {
        return $this->_changed;
    }

    public function setChanged($_changed)
    {
        $this->_changed = $_changed;
        return $this;
    }

    public function getTableName()
    {
        return 'plan3dHeader';
    }
}
