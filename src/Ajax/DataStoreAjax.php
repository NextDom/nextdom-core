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

namespace NextDom\Ajax;

use NextDom\Enums\AjaxParams;
use NextDom\Enums\Common;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Utils;
use NextDom\Managers\DataStoreManager;
use NextDom\Model\Entity\Cmd;
use NextDom\Model\Entity\DataStore;
use NextDom\Model\Entity\EqLogic;
use NextDom\Model\Entity\InteractDef;
use NextDom\Model\Entity\Scenario;

/**
 * Class DataStoreAjax
 * @package NextDom\Ajax
 */
class DataStoreAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    /**
     * Remove variable from the dataStore
     * @throws CoreException
     */
    public function remove()
    {
        $dataStoreId = Utils::initInt('id');
        $dataStore = DataStoreManager::byId($dataStoreId);
        if (!is_object($dataStore)) {
            throw new CoreException(__('Dépôt de données inconnu. Vérifiez l\'ID : ') . $dataStoreId);
        }
        $dataStore->remove();
        $this->ajax->success();
    }

    /**
     * Save variable in the dataStore
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        $dataStoreId = Utils::initInt('id', -1);
        if ($dataStoreId <= 0) {
            $dataStore = new DataStore();
            $dataStore->setKey(Utils::init('key'));
            $dataStore->setLink_id(Utils::init('link_id'));
            $dataStore->setType(Utils::init('type'));
        } else {
            $dataStore = DataStoreManager::byId($dataStoreId);
        }
        if (!is_object($dataStore)) {
            throw new CoreException(__('Dépôt de données inconnu. Vérifiez l\'ID : ') . $dataStoreId);
        }
        $dataStore->setValue(Utils::init('value'));
        $dataStore->save();
        $this->ajax->success();
    }

    /**
     * Get all variables from the dataStore
     * @throws \ReflectionException
     */
    public function all()
    {
        $dataStores = DataStoreManager::byTypeLinkId(Utils::init('type'));
        $result = [];
        if (Utils::init(AjaxParams::USED_BY) == 1) {
            $linkedObjectTypes = [
                NextDomObj::SCENARIO => [],
                NextDomObj::EQLOGIC => [],
                NextDomObj::CMD => [],
                NextDomObj::INTERACT_DEF => [],
            ];
            /**
             * Loop on all variables
             */
            foreach ($dataStores as $datastore) {
                $dataStoreInformations = Utils::o2a($datastore);
                $dataStoreInformations[Common::USED_BY] = $linkedObjectTypes;
                $usedBy = $datastore->getUsedBy();
                /**
                 * Loop on all linked objects to the variable
                 */
                foreach (array_keys($linkedObjectTypes) as $objectType) {
                    /**
                     * @var Scenario|EqLogic|Cmd|InteractDef $objectToConvert
                     */
                    foreach ($usedBy[$objectType] as $objectToConvert) {
                        $dataStoreInformations[Common::USED_BY][$objectType][] = $objectToConvert->getHumanName();
                    }

                }
                $result[] = $dataStoreInformations;
            }
        } else {
            $result = Utils::o2a($dataStores);
        }
        $this->ajax->success($result);
    }
}