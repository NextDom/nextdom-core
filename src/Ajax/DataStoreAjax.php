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

use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AjaxHelper;
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
    /**
     * @var string
     */
    protected $NEEDED_RIGHTS = UserRight::USER;

    /**
     * @var bool
     */
    protected $MUST_BE_CONNECTED = true;

    /**
     * @var bool
     */
    protected $CHECK_AJAX_TOKEN = true;

    /**
     * @throws CoreException
     */
    public function remove()
    {
        $dataStore = DataStoreManager::byId(Utils::init('id'));
        if (!is_object($dataStore)) {
            throw new CoreException(__('Dépôt de données inconnu. Vérifiez l\'ID : ') . Utils::init('id'));
        }
        $dataStore->remove();
        AjaxHelper::success();
    }

    /**
     * @throws CoreException
     */
    public function save()
    {
        if (Utils::init('id') == '') {
            $dataStore = new DataStore();
            $dataStore->setKey(Utils::init('key'));
            $dataStore->setLink_id(Utils::init('link_id'));
            $dataStore->setType(Utils::init('type'));
        } else {
            $dataStore = DataStoreManager::byId(Utils::init('id'));
        }
        if (!is_object($dataStore)) {
            throw new CoreException(__('Dépôt de données inconnu. Vérifiez l\'ID : ') . Utils::init('id'));
        }
        $dataStore->setValue(Utils::init('value'));
        $dataStore->save();
        AjaxHelper::success();
    }

    /**
     * @throws \ReflectionException
     */
    public function all()
    {
        $dataStores = DataStoreManager::byTypeLinkId(Utils::init('type'));
        $return = array();
        if (Utils::init('usedBy') == 1) {
            foreach ($dataStores as $datastore) {
                $info_datastore = Utils::o2a($datastore);
                $info_datastore['usedBy'] = array(
                    'scenario' => array(),
                    'eqLogic' => array(),
                    'cmd' => array(),
                    'interactDef' => array(),
                );
                $usedBy = $datastore->getUsedBy();
                /**
                 * @var Scenario $scenario
                 */
                foreach ($usedBy['scenario'] as $scenario) {
                    $info_datastore['usedBy']['scenario'][] = $scenario->getHumanName();
                }
                /**
                 * @var EqLogic $eqLogic
                 */
                foreach ($usedBy['eqLogic'] as $eqLogic) {
                    $info_datastore['usedBy']['eqLogic'][] = $eqLogic->getHumanName();
                }
                /**
                 * @var Cmd $cmd
                 */
                foreach ($usedBy['cmd'] as $cmd) {
                    $info_datastore['usedBy']['cmd'][] = $cmd->getHumanName();
                }
                /**
                 * @var InteractDef $interactDef
                 */
                foreach ($usedBy['interactDef'] as $interactDef) {
                    $info_datastore['usedBy']['interactDef'][] = $interactDef->getHumanName();
                }
                $return[] = $info_datastore;
            }
        } else {
            $return = Utils::o2a($dataStores);
        }
        AjaxHelper::success($return);
    }

}
