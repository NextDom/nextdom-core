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
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ViewZoneManager;

/**
 * View
 *
 * @ORM\Table(name="view", uniqueConstraints={@ORM\UniqueConstraint(name="name_UNIQUE", columns={"name"})})
 * @ORM\Entity
 */
class View
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
     * @ORM\Column(name="display", type="text", length=65535, nullable=true)
     */
    protected $display;

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=true)
     */
    protected $order;

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

    protected $_changed = false;

    public function report($_format = 'pdf', $_parameters = array()) {
        $url = NetworkHelper::getNetworkAccess('internal') . '/index.php?v=d&p=view';
        $url .= '&view_id=' . $this->getId();
        $url .= '&report=1';
        if (isset($_parameters['arg']) && trim($_parameters['arg']) != '') {
            $url .= '&' . $_parameters['arg'];
        }
        return \report::generate($url, 'view', $this->getId(), $_format, $_parameters);
    }
    /**
     *
     * @throws \Exception
     */
    public function presave() {
        if (trim($this->getName()) == '') {
            throw new \Exception(__('Le nom de la vue ne peut pas être vide'));
        }
    }

    public function save() {
        return \DB::save($this);
    }

    public function remove() {
        NextDomHelper::addRemoveHistory(array('id' => $this->getId(), 'name' => $this->getName(), 'date' => date('Y-m-d H:i:s'), 'type' => 'view'));
        return \DB::remove($this);
    }

    public function getviewZone() {
        return ViewZoneManager::byView($this->getId());
    }

    public function removeviewZone() {
        return ViewZoneManager::removeByViewId($this->getId());
    }

    public function getImgLink() {
        if ($this->getImage('data') == '') {
            return '';
        }
        $dir = __DIR__ . '/../../core/img/view';
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        if ($this->getImage('sha512') == '') {
            $this->setImage('sha512', sha512($this->getImage('data')));
            $this->save();
        }
        $filename = $this->getImage('sha512') . '.' . $this->getImage('type');
        $filepath = $dir . '/' . $filename;
        if (!file_exists($filepath)) {
            file_put_contents($filepath, base64_decode($this->getImage('data')));
        }
        return 'core/img/view/' . $filename;
    }

    public function toArray() {
        $return = Utils::o2a($this, true);
        unset($return['image']);
        $return['img'] = $this->getImgLink();
        return $return;
    }

    public function toAjax($_version = 'dview', $_html = false) {
        $return = Utils::o2a($this);
        $return['viewZone'] = array();
        foreach ($this->getViewZone() as $viewZone) {
            $viewZone_info = Utils::o2a($viewZone);
            $viewZone_info['viewData'] = array();
            foreach ($viewZone->getViewData() as $viewData) {
                $viewData_info = Utils::o2a($viewData);
                $viewData_info['name'] = '';
                switch ($viewData->getType()) {
                    case 'cmd':
                        $cmd = $viewData->getLinkObject();
                        if (is_object($cmd)) {
                            $viewData_info['type'] = 'cmd';
                            if ($_html) {
                                $viewData_info['html'] = $cmd->toHtml($_version);
                            } else {
                                $viewData_info['name'] = $cmd->getHumanName();
                                $viewData_info['id'] = $cmd->getId();
                            }
                        }
                        break;
                    case 'eqLogic':
                        $eqLogic = $viewData->getLinkObject();
                        if (is_object($eqLogic)) {
                            $viewData_info['type'] = 'eqLogic';
                            if ($_html) {
                                $viewData_info['html'] = $eqLogic->toHtml($_version);
                            } else {
                                $viewData_info['name'] = $eqLogic->getHumanName();
                                $viewData_info['id'] = $eqLogic->getId();
                            }
                        }
                        break;
                    case 'scenario':
                        $scenario = $viewData->getLinkObject();
                        if (is_object($scenario)) {
                            $viewData_info['type'] = 'scenario';
                            if ($_html) {
                                $viewData_info['html'] = $scenario->toHtml($_version);
                            } else {
                                $viewData_info['name'] = $scenario->getHumanName();
                                $viewData_info['id'] = $scenario->getId();
                            }
                        }
                        break;
                }
                $viewZone_info['viewData'][] = $viewData_info;
                if ($_html && $viewZone->getType() == 'table') {
                    $viewZone_info['html'] = '<div class="table-responsive"><table class="table table-condensed ui-responsive table-stroke" data-role="table" data-mode="columntoggle">';
                    if (count($viewZone_info['viewData']) != 1) {
                        continue;
                    }
                    $viewData = $viewZone_info['viewData'][0];
                    $configurationViewZoneLine = $viewZone->getConfiguration('nbline', 2);
                    for ($i = 0; $i < $configurationViewZoneLine; $i++) {
                        $viewZone_info['html'] .= '<tr>';
                        $configurationViewZoneColumn = $viewZone->getConfiguration('nbcol', 2);
                        for ($j = 0; $j < $configurationViewZoneColumn; $j++) {
                            $viewZone_info['html'] .= '<td><center>';
                            if (isset($viewData['configuration'][$i][$j])) {
                                $replace = array();
                                preg_match_all("/#([0-9]*)#/", $viewData['configuration'][$i][$j], $matches);
                                foreach ($matches[1] as $cmd_id) {
                                    $cmd = CmdManager::byId($cmd_id);
                                    if (!is_object($cmd)) {
                                        continue;
                                    }
                                    $replace['#' . $cmd_id . '#'] = $cmd->toHtml($_version);
                                }
                                $viewZone_info['html'] .= str_replace(array_keys($replace), $replace, $viewData['configuration'][$i][$j]);
                            }
                            $viewZone_info['html'] .= '</center></td>';
                        }
                        $viewZone_info['html'] .= '</tr>';
                    }
                    $viewZone_info['html'] .= '</table></div>';
                }
            }
            $return['viewZone'][] = $viewZone_info;
        }
        if ($_html) {
            return $return;
        }
        return NextDomHelper::toHumanReadable($return);
    }

    public function getLinkData(&$_data = array('node' => array(), 'link' => array()), $_level = 0, $_drill = 3) {
        if (isset($_data['node']['view' . $this->getId()])) {
            return;
        }
        $_level++;
        if ($_level > $_drill) {
            return $_data;
        }
        $icon = findCodeIcon('fa-picture-o');
        $_data['node']['view' . $this->getId()] = array(
            'id' => 'interactDef' . $this->getId(),
            'name' => substr($this->getName(), 0, 20),
            'icon' => $icon['icon'],
            'fontfamily' => $icon['fontfamily'],
            'fontsize' => '1.5em',
            'fontweight' => ($_level == 1) ? 'bold' : 'normal',
            'texty' => -14,
            'textx' => 0,
            'title' => __('Vue :', __FILE__) . ' ' . $this->getName(),
            'url' => 'index.php?v=d&p=view&view_id=' . $this->getId(),
        );
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

    public function getName() {
        return $this->name;
    }

    public function setName($_name) {
        $this->_changed = Utils::attrChanged($this->_changed,$this->name,$_name);
        $this->name = $_name;
        return $this;
    }

    public function getOrder($_default = null) {
        if ($this->order == '' || !is_numeric($this->order)) {
            return $_default;
        }
        return $this->order;
    }

    public function setOrder($_order) {
        $this->_changed = Utils::attrChanged($this->_changed,$this->order,$_order);
        $this->order = $_order;
        return $this;
    }

    public function getDisplay($_key = '', $_default = '') {
        return Utils::getJsonAttr($this->display, $_key, $_default);
    }

    public function setDisplay($_key, $_value) {
        $display = Utils::setJsonAttr($this->display, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed,$this->display,$display);
        $this->display = $display;
        return $this;
    }

    public function getImage($_key = '', $_default = '') {
        return Utils::getJsonAttr($this->image, $_key, $_default);
    }

    public function setImage($_key, $_value) {
        $image = Utils::setJsonAttr($this->image, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed,$this->image,$image);
        $this->image = $image;
        return $this;
    }

    public function getConfiguration($_key = '', $_default = '') {
        return Utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    public function setConfiguration($_key, $_value) {
        if ($_key == 'accessCode' && $_value != '' && !Utils::isSha512($_value)) {
            $_value = Utils::sha512($_value);
        }
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
        return 'view';
    }

}
