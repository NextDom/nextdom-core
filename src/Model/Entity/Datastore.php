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
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\ScenarioManager;

/**
 * Datastore
 *
 * @ORM\Table(name="dataStore", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQUE", columns={"type", "link_id", "key"})})
 * @ORM\Entity
 */
class Datastore
{

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=false)
     */
    protected $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="link_id", type="integer", nullable=false)
     */
    protected $link_id;

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

    public function preSave()
    {
        $allowType = array('cmd', 'object', 'eqLogic', 'scenario', 'eqReal');
        if (!in_array($this->getType(), $allowType)) {
            throw new \Exception(\__('Le type doit être un des suivants : ') . print_r($allowType, true));
        }
        if (!is_numeric($this->getLink_id())) {
            throw new \Exception(\__('Link_id doit être un chiffre'));
        }
        if ($this->getKey() == '') {
            throw new \Exception(\__('La clef ne peut pas être vide'));
        }
        if ($this->getId() == '') {
            $dataStore = DataStoreManager::byTypeLinkIdKey($this->getType(), $this->getLink_id(), $this->getKey());
            if (is_object($dataStore)) {
                $this->setId($dataStore->getId());
            }
        }
        return true;
    }

    public function save()
    {
        \DB::save($this);
        return true;
    }

    public function postSave()
    {
        ScenarioManager::check('variable(' . $this->getKey() . ')');
        $value_cmd = CmdManager::byValue('variable(' . $this->getKey() . ')', null, true);
        if (is_array($value_cmd)) {
            foreach ($value_cmd as $cmd) {
                if ($cmd->getType() != 'action') {
                    $cmd->event($cmd->execute());

                }
            }
        }
    }

    public function remove()
    {
        \DB::remove($this);
    }

    public function getLinkData(&$_data = array('node' => array(), 'link' => array()), $_level = 0, $_drill = null)
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
        $_data['node']['dataStore' . $this->getId()] = array(
            'id' => 'dataStore' . $this->getId(),
            'name' => $this->getKey(),
            'icon' => $icon['icon'],
            'fontfamily' => $icon['fontfamily'],
            'fontsize' => '1.5em',
            'fontweight' => ($_level == 1) ? 'bold' : 'normal',
            'texty' => -14,
            'textx' => 0,
            'title' => \__('Variable :') . ' ' . $this->getKey(),
        );
        $usedBy = $this->getUsedBy();
        Utils::addGraphLink($this, 'dataStore', $usedBy['scenario'], 'scenario', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'dataStore', $usedBy['cmd'], 'cmd', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'dataStore', $usedBy['eqLogic'], 'eqLogic', $_data, $_level, $_drill);
        Utils::addGraphLink($this, 'dataStore', $usedBy['interactDef'], 'interactDef', $_data, $_level, $_drill);
        return $_data;
    }

    public function getUsedBy($_array = false)
    {
        $return = array('cmd' => array(), 'eqLogic' => array(), 'scenario' => array());
        $return['cmd'] = CmdManager::searchConfiguration(array('"cmd":"variable"%"name":"' . $this->getKey() . '"', 'variable(' . $this->getKey() . ')', '"name":"' . $this->getKey() . '"%"cmd":"variable"'));
        $return['eqLogic'] = EqLogicManager::searchConfiguration(array('"cmd":"variable"%"name":"' . $this->getKey() . '"', 'variable(' . $this->getKey() . ')', '"name":"' . $this->getKey() . '"%"cmd":"variable"'));
        $return['interactDef'] = InteractDefManager::searchByUse(array('"cmd":"variable"%"name":"' . $this->getKey() . '"', 'variable(' . $this->getKey() . ')', '"name":"' . $this->getKey() . '"%"cmd":"variable"'));
        $return['scenario'] = ScenarioManager::searchByUse(array(
            array('action' => 'variable(' . $this->getKey() . ')', 'option' => 'variable(' . $this->getKey() . ')'),
            array('action' => 'variable', 'option' => $this->getKey(), 'and' => true),
            array('action' => 'ask', 'option' => $this->getKey(), 'and' => true),
        ));
        if ($_array) {
            foreach ($return as &$value) {
                $value = Utils::o2a($value);
            }
        }
        return $return;
    }

    /*     * **********************Getteur Setteur*************************** */

    public function getId()
    {
        return $this->id;
    }

    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($_type)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->type, $_type);
        $this->type = $_type;
        return $this;
    }

    public function getLink_id()
    {
        return $this->link_id;
    }

    public function setLink_id($_link_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->link_id, $_link_id);
        $this->link_id = $_link_id;
        return $this;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($_key)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->key, $_key);
        $this->key = $_key;
        return $this;
    }

    public function getValue($_default = '')
    {
        if ($this->value === '') {
            return $_default;
        }
        return is_json($this->value, $this->value);
    }

    public function setValue($_value)
    {
        if (is_object($_value) || is_array($_value)) {
            $_value = json_encode($_value, JSON_UNESCAPED_UNICODE);
        }
        $this->_changed = Utils::attrChanged($this->_changed, $this->value, $_value);
        $this->value = $_value;
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
        return 'dataStore';
    }
}
