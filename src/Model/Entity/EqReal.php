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

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\EqLogicManager;

/**
 * Eqreal
 *
 * @ORM\Table(name="eqReal", uniqueConstraints={@ORM\UniqueConstraint(name="name_UNIQUE", columns={"name"})}, indexes={@ORM\Index(name="logicalId", columns={"logicalId"}), @ORM\Index(name="type", columns={"type"}), @ORM\Index(name="logicalId_Type", columns={"logicalId", "type"}), @ORM\Index(name="name", columns={"name"})})
 * @ORM\Entity
 */
class EqReal implements EntityInterface
{

    /**
     * @var string
     *
     * @ORM\Column(name="logicalId", type="string", length=45, nullable=true)
     */
    protected $logicalId = '';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=45, nullable=false)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    protected $configuration;

    /**
     * @var string
     *
     * @ORM\Column(name="cat", type="string", length=45, nullable=true)
     */
    protected $cat;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function remove()
    {
        foreach ($this->getEqLogic() as $eqLogic) {
            $eqLogic->remove();
        }
        DataStoreManager::removeByTypeLinkId('eqReal', $this->getId());
        return DBHelper::remove($this);
    }

    /**
     * @return EqLogic[]|null
     * @throws \Exception
     */
    public function getEqLogic()
    {
        return EqLogicManager::byEqRealId($this->id);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        if ($this->getName() == '') {
            throw new CoreException(__('Le nom de l\'équipement réel ne peut pas être vide'));
        }
        return DBHelper::save($this);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalId()
    {
        return $this->logicalId;
    }

    /**
     * @param $logicalId
     * @return $this
     */
    public function setLogicalId($logicalId)
    {
        $this->logicalId = $logicalId;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getCat()
    {
        return $this->cat;
    }

    /**
     * @param $cat
     * @return $this
     */
    public function setCat($cat)
    {
        $this->cat = $cat;
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
        $this->configuration = Utils::setJsonAttr($this->configuration, $_key, $_value);
        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'eqReal';
    }
}
