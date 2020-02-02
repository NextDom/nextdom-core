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
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\Plan3dManager;
use NextDom\Model\Entity\Parents\AccessCodeConfigurationEntity;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\ConfigurationEntity;
use NextDom\Model\Entity\Parents\NameEntity;

/**
 * Plan3dheader
 *
 * @ORM\Table(name="plan3dHeader")
 * @ORM\Entity
 */
class Plan3dHeader extends BaseEntity
{
    const TABLE_NAME = NextDomObj::PLAN3D_HEADER;

    use NameEntity, AccessCodeConfigurationEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    protected $configuration;

    public function preSave()
    {
        if (trim($this->getName()) == '') {
            throw new CoreException(__('Le nom du l\'objet ne peut pas être vide'));
        }
    }

    public function remove()
    {
        $cibDir = NEXTDOM_ROOT . '/' . $this->getConfiguration('path', '');
        if (file_exists($cibDir) && $this->getConfiguration('path', '') != '') {
            rrmdir($cibDir);
        }
        NextDomHelper::addRemoveHistory(['id' => $this->getId(), 'name' => $this->getName(), 'date' => date(DateFormat::FULL), 'type' => 'plan3d']);
        return parent::remove();
    }

    /**
     * @return Plan3d|null
     * @throws \Exception
     */
    public function getPlan3d()
    {
        return Plan3dManager::byPlan3dHeaderId($this->getId());
    }
}
