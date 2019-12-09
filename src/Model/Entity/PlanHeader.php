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

use NextDom\Enums\DateFormat;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
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
class PlanHeader implements EntityInterface
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

    /**
     * @param string $_format
     * @param array $_parameters
     * @return string
     * @throws \Exception
     */
    public function report($_format = 'pdf', $_parameters = [])
    {
        $url = NetworkHelper::getNetworkAccess('internal') . '/index.php?v=d&p=plan';
        $url .= '&plan_id=' . $this->getId();
        $url .= '&report=1';
        if (isset($_parameters['arg']) && trim($_parameters['arg']) != '') {
            $url .= '&' . $_parameters['arg'];
        }
        return ReportHelper::generate($url, 'plan', $this->getId(), $_format, $_parameters);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $_id
     * @return $this
     */
    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
    }

    /**
     * @param $_name
     * @return PlanHeader
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
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
        $filename1 = 'planHeader' . $this->getId() . '-' . $this->getImage('sha512') . '.' . $this->getImage('type');
        if (file_exists(NEXTDOM_DATA . '/data/custom/plans/' . $filename1)) {
            $filename2 = 'planHeader' . $planHeaderCopy->getId() . '-' . $planHeaderCopy->getImage('sha512') . '.' . $planHeaderCopy->getImage('type');
            copy(NEXTDOM_DATA . '/data/custom/plans/' . $filename1, NEXTDOM_DATA . '/data/custom/plans/' . $filename2);
        }
        return $planHeaderCopy;
    }

    public function save()
    {
        DBHelper::save($this);
    }

    /**
     * @return Plan[]
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function getPlan()
    {
        return PlanManager::byPlanHeaderId($this->getId());
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getImage($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->image, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setImage($_key, $_value)
    {
        $image = Utils::setJsonAttr($this->image, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->image, $image);
        $this->image = $image;
        return $this;
    }

    public function preSave()
    {
        if (trim($this->getName()) == '') {
            throw new CoreException(__('Le nom du plan ne peut pas être vide'));
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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $_name
     * @return $this
     */
    public function setName($_name)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->name, $_name);
        $this->name = $_name;
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
        if ($_key == 'accessCode' && $_value != '' && !Utils::isSha512($_value)) {
            $_value = Utils::sha512($_value);
        }
        $configuration = Utils::setJsonAttr($this->configuration, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->configuration, $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    public function remove()
    {
        NextDomHelper::addRemoveHistory(['id' => $this->getId(), 'name' => $this->getName(), 'date' => date(DateFormat::FULL), 'type' => 'plan']);
        DBHelper::remove($this);
    }

    /**
     * @return string
     * @throws CoreException
     */
    public function displayImage()
    {
        if ($this->getImage('data') == '') {
            return '';
        }
        $dir = NEXTDOM_DATA . '/data/custom/plans/';
        if (!file_exists($dir)) {
            FileSystemHelper::mkdirIfNotExists($dir, 0755, true);
        }
        if ($this->getImage('sha512') == '') {
            $this->setImage('sha512', Utils::sha512($this->getImage('data')));
            $this->save();
        }
        $filename = $this->getImage('sha512') . '.' . $this->getImage('type');
        $filepath = $dir . $filename;
        if (!file_exists($filepath)) {
            file_put_contents($filepath, base64_decode($this->getImage('data')));
        }
        $size = $this->getImage('size');
        return '<img style="z-index:997" src="' . '/data/custom/plans/' . $filename . '" data-size_y="' . $size[1] . '" data-size_x="' . $size[0] . '">';
    }

    /**
     * @param array $_data
     * @param int $_level
     * @param int $_drill
     * @return array|null
     * @throws \Exception
     */
    public function getLinkData(&$_data = ['node' => [], 'link' => []], $_level = 0, $_drill = 3)
    {
        if (isset($_data['node']['plan' . $this->getId()])) {
            return null;
        }
        $_level++;
        if ($_level > $_drill) {
            return $_data;
        }
        $icon = Utils::findCodeIcon('fa-paint-brush');
        $_data['node']['plan' . $this->getId()] = [
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
        ];
        return null;
    }

    /**
     * @return mixed
     */
    public function getChanged()
    {
        return $this->_changed;
    }

    /**
     * @param $_changed
     * @return $this
     */
    public function setChanged($_changed)
    {
        $this->_changed = $_changed;
        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'planHeader';
    }
}