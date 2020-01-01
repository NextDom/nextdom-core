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

use NextDom\Enums\CmdType;
use NextDom\Enums\NextDomObj;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\LinkIdEntity;
use NextDom\Model\Entity\Parents\TypeEntity;

/**
 * Datastore
 *
 * @ORM\Table(name="dataStore", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQUE", columns={"type", "link_id", "key"})})
 * @ORM\Entity
 */
class DataStore implements EntityInterface
{
    const TABLE_NAME = NextDomObj::DATASTORE;

    use LinkIdEntity, TypeEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=127, nullable=false)
     */
    protected $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", length=65535, nullable=true)
     */
    protected $value;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    protected $_changed;

    /**
     * @return bool
     * @throws \Exception
     */
    public function preSave()
    {
        $allowedType = [NextDomObj::CMD, NextDomObj::OBJECT, NextDomObj::EQLOGIC, NextDomObj::SCENARIO, NextDomObj::EQREAL];
        if (!in_array($this->getType(), $allowedType)) {
            throw new CoreException(__('Le type doit être un des suivants : ') . print_r($allowedType, true));
        }
        if (!is_numeric($this->getLink_id())) {
            throw new CoreException(__('Link_id doit être un chiffre'));
        }
        if (empty($this->getKey())) {
            throw new CoreException(__('La clé ne peut pas être vide'));
        }
        if (empty($this->getId())) {
            $dataStore = DataStoreManager::byTypeLinkIdKey($this->getType(), $this->getLink_id(), $this->getKey());
            if (is_object($dataStore)) {
                $this->setId($dataStore->getId());
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /*     * **********************Getteur Setteur*************************** */

    /**
     * @param $_key
     * @return $this
     */
    public function setKey($_key)
    {
        $this->updateChangeState($this->key, $_key);
        $this->key = $_key;
        return $this;
    }

    public function postSave()
    {
        ScenarioManager::check('variable(' . $this->getKey() . ')');
        $value_cmd = CmdManager::byValue('variable(' . $this->getKey() . ')', null, true);
        if (is_array($value_cmd)) {
            foreach ($value_cmd as $cmd) {
                if ($cmd->isType(CmdType::ACTION)) {
                    $cmd->event($cmd->execute());
                }
            }
        }
    }

    /**
     * @param array $_data
     * @param int $_level
     * @param null $_drill
     * @return array|null
     * @throws \ReflectionException
     */
    public function getLinkData(&$_data = ['node' => [], 'link' => []], $_level = 0, $_drill = null)
    {
        if ($_drill == null) {
            $_drill = ConfigManager::byKey('graphlink::dataStore::drill');
        }
        if (isset($_data['node']['dataStore' . $this->getId()])) {
            return null;
        }
        $_level++;
        if ($_level > $_drill) {
            return $_data;
        }
        $icon = Utils::findCodeIcon('fa-code');
        $_data['node']['dataStore' . $this->getId()] = [
            'id' => 'dataStore' . $this->getId(),
            'name' => $this->getKey(),
            'icon' => $icon['icon'],
            'fontfamily' => $icon['fontfamily'],
            'fontsize' => '1.5em',
            'fontweight' => ($_level == 1) ? 'bold' : 'normal',
            'texty' => -14,
            'textx' => 0,
            'title' => __('Variable :') . ' ' . $this->getKey(),
        ];
        $usedBy = $this->getUsedBy();
        Utils::addGraphLink($this, 'dataStore', $usedBy[NextDomObj::SCENARIO], NextDomObj::SCENARIO, $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'dataStore', $usedBy[NextDomObj::CMD], NextDomObj::CMD, $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'dataStore', $usedBy[NextDomObj::EQLOGIC], NextDomObj::EQLOGIC, $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'dataStore', $usedBy[NextDomObj::INTERACT_DEF], NextDomObj::INTERACT_DEF, $_data, $_level, $_drill);
        return $_data;
    }

    /**
     * @param bool $_array
     * @return array
     * @throws \ReflectionException
     */
    public function getUsedBy($_array = false)
    {
        $searchConfigurationKey = '"cmd":"variable"%"name":"';
        $result = [NextDomObj::CMD => [], NextDomObj::EQLOGIC => [], NextDomObj::SCENARIO => []];
        $result[NextDomObj::CMD] = CmdManager::searchConfiguration([$searchConfigurationKey . $this->getKey() . '"', 'variable(' . $this->getKey() . ')', 'variable(' . $this->getKey() . ',', '"name":"' . $this->getKey() . '"%"cmd":"variable"']);
        $result[NextDomObj::EQLOGIC] = EqLogicManager::searchConfiguration([$searchConfigurationKey . $this->getKey() . '"', 'variable(' . $this->getKey() . ')', 'variable(' . $this->getKey() . ',', '"name":"' . $this->getKey() . '"%"cmd":"variable"']);
        $result[NextDomObj::INTERACT_DEF] = InteractDefManager::searchByUse([$searchConfigurationKey . $this->getKey() . '"', 'variable(' . $this->getKey() . ')', 'variable(' . $this->getKey() . ',', '"name":"' . $this->getKey() . '"%"cmd":"variable"']);
        $result[NextDomObj::SCENARIO] = ScenarioManager::searchByUse([
            ['action' => 'variable(' . $this->getKey() . ')', 'option' => 'variable(' . $this->getKey() . ')'],
            ['action' => 'variable(' . $this->getKey() . ',', 'option' => 'variable(' . $this->getKey() . ','],
            ['action' => 'variable', 'option' => $this->getKey(), 'and' => true],
            ['action' => 'ask', 'option' => $this->getKey(), 'and' => true],
        ]);
        if ($_array) {
            foreach ($result as &$value) {
                $value = Utils::o2a($value);
            }
        }
        return $result;
    }

    /**
     * @param string $_default
     * @return bool|mixed|null|string
     */
    public function getValue($_default = '')
    {
        if ($this->value === '') {
            return $_default;
        }
        return Utils::isJson($this->value, $this->value);
    }

    /**
     * @param $_value
     * @return $this
     */
    public function setValue($_value)
    {
        if (is_object($_value) || is_array($_value)) {
            $_value = json_encode($_value, JSON_UNESCAPED_UNICODE);
        }
        $this->updateChangeState($this->value, $_value);
        $this->value = $_value;
        return $this;
    }
}
