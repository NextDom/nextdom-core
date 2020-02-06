<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../core/php/core.inc.php';

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\EqLogicManager;

class eqReal {
    const CLASS_NAME = EqReal::class;
    const DB_CLASS_NAME = '`eqReal`';


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

    /**
     * @param $_logicalId
     * @param $_cat
     * @return array
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byLogicalId($_logicalId, $_cat)
    {
        $values = [
            'logicalId' => $_logicalId,
            'cat' => $_cat,
        ];
        $sql = 'SELECT id
                FROM ' . self::DB_CLASS_NAME . '
                WHERE `logicalId` = :logicalId
                AND `cat` = :cat';
        $results = DBHelper::getAll($sql, $values);
        $return = [];
        foreach ($results as $result) {
            $return[] = self::byId($result['id']);
        }
        return $return;
    }

    /**
     * @param $_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byId($_id)
    {
        $values = [
            'id' => $_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE `id` = :id';
        $className = self::getClass($_id);
        return DBHelper::getOneObject($sql, $values, $className);
    }

    /**
     * @param $_id
     * @return string
     * @throws \NextDom\Exceptions\CoreException
     */
    protected static function getClass($_id)
    {
        if (get_called_class() != self::CLASS_NAME) {
            return get_called_class();
        }
        $values = [
            'id' => $_id,
        ];
        $sql = 'SELECT plugin, isEnable
                FROM `eqLogic`
                WHERE `eqReal_id` = :id';
        $result = DBHelper::getOne($sql, $values);
        $eqTyme_name = $result['plugin'];
        if ($result['isEnable'] == 0) {
            try {
                $plugin = null;
                if ($eqTyme_name != '') {
                    $plugin = PluginManager::byId($eqTyme_name);
                }
                if (!is_object($plugin) || $plugin->isActive() == 0) {
                    return self::CLASS_NAME;
                }
            } catch (\Exception $e) {
                return self::CLASS_NAME;
            }
        }
        if (class_exists($eqTyme_name)) {
            if (method_exists($eqTyme_name, 'getClassCmd')) {
                return $eqTyme_name::getClassCmd();
            }
        }
        if (class_exists($eqTyme_name . 'Real')) {
            return $eqTyme_name . 'Real';
        }
        return self::CLASS_NAME;
    }
}
