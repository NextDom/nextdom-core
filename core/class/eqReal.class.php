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

use NextDom\Managers\EqRealManager;

class eqReal {
    /*     * *************************Attributs****************************** */

    protected $id;
    protected $logicalId = '';
    protected $name;
    protected $type;
    protected $cat;
    protected $configuration;

    /*     * ***********************Méthodes statiques*************************** */

    public function getTableName() {
        return 'eqReal';
    }

    public static function byId($_id) {
        return EqRealManager::byId($_id);
    }

    public static function byLogicalId($_logicalId, $_cat) {
        return EqRealManager::byLogicalId($_logicalId, $_cat);
    }

    /*     * *********************Méthodes d'instance************************* */

    public function remove() {
        foreach ($this->getEqLogic() as $eqLogic) {
            $eqLogic->remove();
        }
        dataStore::removeByTypeLinkId('eqReal', $this->getId());
        return DB::remove($this);
    }

    public function save() {
        if ($this->getName() == '') {
            throw new Exception(__('Le nom de l\'équipement réel ne peut pas être vide', __FILE__));
        }
        return DB::save($this);
    }

    /*     * **********************Getteur Setteur*************************** */

    public function getEqLogic() {
        return eqLogic::byEqRealId($this->id);
    }

    public function getId() {
        return $this->id;
    }

    public function getLogicalId() {
        return $this->logicalId;
    }

    public function getName() {
        return $this->name;
    }

    public function getType() {
        return $this->type;
    }

    public function getCat() {
        return $this->cat;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setLogicalId($logicalId) {
        $this->logicalId = $logicalId;
        return $this;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function setCat($cat) {
        $this->cat = $cat;
        return $this;
    }

    public function getConfiguration($_key = '', $_default = '') {
        return utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    public function setConfiguration($_key, $_value) {
        $this->configuration = utils::setJsonAttr($this->configuration, $_key, $_value);
        return $this;
    }

}
