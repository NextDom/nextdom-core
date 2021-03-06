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
use NextDom\Enums\NextDomObj;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\ReportHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\PlanManager;
use NextDom\Model\Entity\Parents\AccessCodeConfigurationEntity;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\ConfigurationEntity;
use NextDom\Model\Entity\Parents\NameEntity;
use NextDom\Model\Entity\Parents\ImageEntity;
use NextDom\Model\Entity\Parents\OrderEntity;

/**
 * Planheader
 *
 * @ORM\Table(name="planHeader")
 * @ORM\Entity
 */
class PlanHeader extends BaseEntity
{
    const TABLE_NAME = NextDomObj::PLAN_HEADER;
    const IMG_DIR_NAME = NextDomObj::PLAN;

    use NameEntity, ImageEntity, AccessCodeConfigurationEntity, OrderEntity;

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
        $filename1 = self::TABLE_NAME . $this->getId() . '-' . $this->getImage('sha512') . '.' . $this->getImage('type');
        if (file_exists(NEXTDOM_DATA . '/data/plan/' . $filename1)) {
            $filename2 = self::TABLE_NAME . $planHeaderCopy->getId() . '-' . $planHeaderCopy->getImage('sha512') . '.' . $planHeaderCopy->getImage('type');
            copy(NEXTDOM_DATA . '/data/' . self::IMG_DIR_NAME . '/' . $filename1, NEXTDOM_DATA . '/data/' . self::IMG_DIR_NAME . '/' . $filename2);
        }
        return $planHeaderCopy;
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

    public function remove()
    {
        NextDomHelper::addRemoveHistory(['id' => $this->getId(), 'name' => $this->getName(), 'date' => date(DateFormat::FULL), 'type' => 'plan']);
        return parent::remove();
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
}