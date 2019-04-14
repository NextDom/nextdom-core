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

use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\ReportHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\PlanManager;

/**
 * Planheader
 *
 * @ORM\Table(name="planHeader")
 * @ORM\Entity
 */
class PlanHeader
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
     * @ORM\Column(name="image", type="text", length=16777215, nullable=true)
     */
    protected $image;

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

    protected $_changed;

    public function report($_format = 'pdf', $_parameters = array())
    {
        $url = NetworkHelper::getNetworkAccess('internal') . '/index.php?v=d&p=plan';
        $url .= '&plan_id=' . $this->getId();
        $url .= '&report=1';
        if (isset($_parameters['arg']) && trim($_parameters['arg']) != '') {
            $url .= '&' . $_parameters['arg'];
        }
        return ReportHelper::generate($url, 'plan', $this->getId(), $_format, $_parameters);
    }

    public function copy($_name)
    {
        $planHeaderCopy = clone $this;
        $planHeaderCopy->setName($_name);
        $planHeaderCopy->setId('');
        $planHeaderCopy->save();
        foreach ($this->getPlan() as $plan) {
            $planCopy = clone $plan;
            $planCopy->setId('');
            $planCopy->setPlanHeader_id($planHeaderCopy->getId());
            $planCopy->save();
        }
        $filename1 = 'planHeader'.$this->getId().'-'.$this->getImage('sha512') . '.' . $this->getImage('type');
        if(file_exists(NEXTDOM_DATA . '/data/plan/'.$filename1)){
            $filename2 = 'planHeader'.$planHeaderCopy->getId().'-'.$planHeaderCopy->getImage('sha512') . '.' . $planHeaderCopy->getImage('type');
            copy(NEXTDOM_DATA.'/data/plan/'.$filename1,NEXTDOM_DATA.'/data/plan/'.$filename2);
        }
        return $planHeaderCopy;
    }

    public function preSave()
    {
        if (trim($this->getName()) == '') {
            throw new \Exception(__('Le nom du plan ne peut pas être vide'));
        }
        if ($this->getConfiguration('desktopSizeX') == '') {
            $this->setConfiguration('desktopSizeX', 500);
        }
        if ($this->getConfiguration('desktopSizeY') == '') {
            $this->setConfiguration('desktopSizeY', 500);
        }
        if ($this->getConfiguration('backgroundTransparent') == '') {
            $this->setConfiguration('backgroundTransparent', 1);
        }
        if ($this->getConfiguration('backgroundColor') == '') {
            $this->setConfiguration('backgroundColor', '#ffffff');
        }
    }

    public function save()
    {
        \DB::save($this);
    }

    public function remove()
    {
        NextDomHelper::addRemoveHistory(array('id' => $this->getId(), 'name' => $this->getName(), 'date' => date('Y-m-d H:i:s'), 'type' => 'plan'));
        \DB::remove($this);
    }

    public function displayImage()
    {
        if ($this->getImage('data') == '') {
            return '';
        }
        $dir = NEXTDOM_ROOT . '/public/img/plan';
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        if ($this->getImage('sha512') == '') {
            $this->setImage('sha512', Utils::sha512($this->getImage('data')));
            $this->save();
        }
        $filename = $this->getImage('sha512') . '.' . $this->getImage('type');
        $filepath = $dir . '/' . $filename;
        if (!file_exists($filepath)) {
            file_put_contents($filepath, base64_decode($this->getImage('data')));
        }
        $size = $this->getImage('size');
        return '<img style="z-index:997" src="/public/img/plan/' . $filename . '" data-sixe_y="' . $size[1] . '" data-sixe_x="' . $size[0] . '">';
    }

    /**
     * @return Plan[]
     */
    public function getPlan()
    {
        return PlanManager::byPlanHeaderId($this->getId());
    }

    public function getLinkData(&$_data = array('node' => array(), 'link' => array()), $_level = 0, $_drill = 3)
    {
        if (isset($_data['node']['plan' . $this->getId()])) {
            return null;
        }
        $_level++;
        if ($_level > $_drill) {
            return $_data;
        }
        $icon = Utils::findCodeIcon('fa-paint-brush');
        $_data['node']['plan' . $this->getId()] = array(
            'id' => 'interactDef' . $this->getId(),
            'name' => substr($this->getName(), 0, 20),
            'icon' => $icon['icon'],
            'fontfamily' => $icon['fontfamily'],
            'fontsize' => '1.5em',
            'fontweight' => ($_level == 1) ? 'bold' : 'normal',
            'texty' => -14,
            'textx' => 0,
            'title' => __('Design :') . ' ' . $this->getName(),
            'url' => 'index.php?v=d&p=plan&view_id=' . $this->getId(),
        );
        return null;
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

    public function getImage($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->image, $_key, $_default);
    }

    public function setImage($_key, $_value)
    {
        $image = Utils::setJsonAttr($this->image, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->image, $image);
        $this->image = $image;
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
        return 'planHeader';
    }
}