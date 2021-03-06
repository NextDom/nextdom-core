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

use NextDom\Enums\ActionRight;
use NextDom\Enums\AjaxParams;
use NextDom\Enums\Common;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Model\Entity\Cmd;
use NextDom\Model\Entity\EqLogic;

/**
 * Class EqLogicAjax
 * @package NextDom\Ajax
 */
class EqLogicAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function getEqLogicObject()
    {
        $linkedObject = JeeObjectManager::byId(Utils::init(AjaxParams::OBJECT_ID));

        if (!is_object($linkedObject)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $result = Utils::o2a($linkedObject);
        $result[NextDomObj::EQLOGIC] = [];
        foreach ($linkedObject->getEqLogic() as $eqLogic) {
            if ($eqLogic->isVisible()) {
                $info_eqLogic = [];
                $info_eqLogic[AjaxParams::ID] = $eqLogic->getId();
                $info_eqLogic[Common::TYPE] = $eqLogic->getEqType_name();
                $info_eqLogic[Common::OBJECT_ID] = $eqLogic->getObject_id();
                $info_eqLogic[Common::HTML] = $eqLogic->toHtml(Utils::init(AjaxParams::VERSION));
                $result[NextDomObj::EQLOGIC][] = $info_eqLogic;
            }
        }
        $this->ajax->success($result);
    }

    public function byId()
    {
        $eqLogic = EqLogicManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($eqLogic)) {
            throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID'));
        }
        $this->ajax->success(Utils::o2a($eqLogic));
    }

    public function toHtml()
    {
        if (Utils::init(AjaxParams::IDS) != '') {
            $result = [];
            foreach (json_decode(Utils::init(AjaxParams::IDS), true) as $eqLogicId => $eqLogicData) {
                $eqLogic = EqLogicManager::byId($eqLogicId);
                if (!is_object($eqLogic)) {
                    continue;
                }
                if (!isset($eqLogicData[AjaxParams::VERSION])) {
                    throw new CoreException(__('Pas de version indiqué pour le rendu HTML'));
                }
                $result[$eqLogic->getId()] = [
                    AjaxParams::ID => $eqLogic->getId(),
                    Common::TYPE => $eqLogic->getEqType_name(),
                    Common::OBJECT_ID => $eqLogic->getObject_id(),
                    Common::HTML => $eqLogic->toHtml($eqLogicData[AjaxParams::VERSION]),
                ];
            }
            $this->ajax->success($result);
        } else {
            $eqLogic = EqLogicManager::byId(Utils::init(AjaxParams::ID));
            if (!is_object($eqLogic)) {
                throw new CoreException(__('Eqlogic inconnu. Vérifiez l\'ID'));
            }
            $eqLogicInfo = [];
            $eqLogicInfo[AjaxParams::ID] = $eqLogic->getId();
            $eqLogicInfo[Common::TYPE] = $eqLogic->getEqType_name();
            $eqLogicInfo[Common::OBJECT_ID] = $eqLogic->getObject_id();
            $eqLogicInfo[Common::HTML] = $eqLogic->toHtml(Utils::init(AjaxParams::VERSION));
            $this->ajax->success($eqLogicInfo);
        }
    }

    public function htmlAlert()
    {
        $result = [];
        foreach (EqLogicManager::all() as $eqLogic) {
            if ($eqLogic->getAlert() == '') {
                continue;
            }
            $result[$eqLogic->getId()] = [
                Common::HTML => $eqLogic->toHtml(Utils::init(AjaxParams::VERSION)),
                AjaxParams::ID => $eqLogic->getId(),
                Common::TYPE => $eqLogic->getEqType_name(),
                Common::OBJECT_ID => $eqLogic->getObject_id(),
            ];
        }
        $this->ajax->success($result);
    }

    public function htmlBattery()
    {
        $result = [];
        $list = [];
        foreach (EqLogicManager::all() as $eqLogic) {
            if ($eqLogic->getStatus(Common::BATTERY, -2) != -2) {
                $list[] = $eqLogic;
            }
        }
        usort($list, function ($a, $b) {
            $aStatus = $a->getStatus(Common::BATTERY);
            $bStatus = $b->getStatus(Common::BATTERY);
            if ($aStatus < $bStatus) {
                return -1;
            } elseif ($aStatus > $bStatus) {
                return 1;
            } else {
                return 0;
            }
        });
        foreach ($list as $eqLogic) {
            $result[] = [
                Common::HTML => $eqLogic->batteryWidget(Utils::init(AjaxParams::VERSION)),
                AjaxParams::ID => $eqLogic->getId(),
                Common::TYPE => $eqLogic->getEqType_name(),
                Common::OBJECT_ID => $eqLogic->getObject_id(),
            ];
        }
        $this->ajax->success($result);
    }

    public function listByType()
    {
        $result = [];
        $eqLogics = EqLogicManager::byType(Utils::init(AjaxParams::TYPE));
        foreach ($eqLogics as $eqLogic) {
            $result[$eqLogic->getId()] = Utils::o2a($eqLogic);
            $result[$eqLogic->getId()][Common::HUMAN_NAME] = $eqLogic->getHumanName();
        }
        $this->ajax->success(array_values($result));
    }

    public function listByObjectAndCmdType()
    {
        $objectId = (Utils::init(AjaxParams::OBJECT_ID) != -1) ? Utils::init(AjaxParams::OBJECT_ID) : null;
        $this->ajax->success(
            EqLogicManager::listByObjectAndCmdType(
                $objectId,
                Utils::init(AjaxParams::TYPE_CMD),
                Utils::init(AjaxParams::SUB_TYPE_CMD)
            )
        );
    }

    public function listByObject()
    {
        $objectId = (Utils::init(AjaxParams::OBJECT_ID) != -1) ? Utils::init(AjaxParams::OBJECT_ID) : null;
        $this->ajax->success(
            Utils::o2a(
                EqLogicManager::byObjectId(
                    $objectId,
                    Utils::init(AjaxParams::ONLY_ENABLE, true),
                    Utils::init(AjaxParams::ONLY_VISIBLE, false),
                    Utils::init(AjaxParams::EQTYPE_NAME, null),
                    Utils::init(AjaxParams::LOGICAL_ID, null),
                    Utils::init(AjaxParams::ORDER_BY_NAME, false))
            )
        );
    }

    public function listByTypeAndCmdType()
    {
        $eqLogicList = EqLogicManager::listByTypeAndCmdType(Utils::init(AjaxParams::TYPE), Utils::init(AjaxParams::TYPE_CMD), Utils::init(AjaxParams::SUB_TYPE_CMD));
        $result = [];
        foreach ($eqLogicList as $eqLogic) {
            $eqLogic = EqLogicManager::byId($eqLogic[AjaxParams::ID]);
            $info[NextDomObj::EQLOGIC] = Utils::o2a($eqLogic);
            $info[NextDomObj::OBJECT] = ['name' => 'Aucun'];
            if (is_object($eqLogic)) {
                $linkedObject = $eqLogic->getObject();
                if (is_object($linkedObject)) {
                    $info['object'] = Utils::o2a($eqLogic->getObject());
                }
            }
            $result[] = $info;
        }
        $this->ajax->success($result);
    }

    public function setIsEnable()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $eqLogic = EqLogicManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($eqLogic)) {
            throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID'));
        }
        if (!$eqLogic->hasRight(ActionRight::WRITE)) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        $eqLogic->setIsEnable(Utils::init('isEnable'));
        $eqLogic->save();
        $this->ajax->success();
    }

    public function setOrder()
    {
        $eqLogics = json_decode(Utils::init('eqLogics'), true);
        foreach ($eqLogics as $eqLogic_json) {
            if (!isset($eqLogic_json[AjaxParams::ID]) || trim($eqLogic_json[AjaxParams::ID]) == '') {
                continue;
            }
            $eqLogic = EqLogicManager::byId($eqLogic_json[AjaxParams::ID]);
            if (!is_object($eqLogic)) {
                continue;
            }
            Utils::a2o($eqLogic, $eqLogic_json);
            $eqLogic->save(true);
        }
        $this->ajax->success();
    }

    public function removes()
    {
        $eqLogics = json_decode(Utils::init('eqLogics'), true);
        foreach ($eqLogics as $id) {
            $eqLogic = EqLogicManager::byId($id);
            if (!is_object($eqLogic)) {
                throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID') . ' ' . $id);
            }
            if (!$eqLogic->hasRight(ActionRight::WRITE)) {
                continue;
            }
            $eqLogic->remove();
        }
        $this->ajax->success();
    }

    public function setIsVisibles()
    {
        $eqLogics = json_decode(Utils::init('eqLogics'), true);
        foreach ($eqLogics as $id) {
            $eqLogic = EqLogicManager::byId($id);
            if (!is_object($eqLogic)) {
                throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID') . ' ' . $id);
            }
            if (!$eqLogic->hasRight(ActionRight::WRITE)) {
                continue;
            }
            $eqLogic->setIsVisible(Utils::init('isVisible'));
            $eqLogic->save();
        }
        $this->ajax->success();
    }

    public function setIsEnables()
    {
        $eqLogics = json_decode(Utils::init('eqLogics'), true);
        foreach ($eqLogics as $id) {
            $eqLogic = EqLogicManager::byId($id);
            if (!is_object($eqLogic)) {
                throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID') . ' ' . $id);
            }
            if (!$eqLogic->hasRight(ActionRight::WRITE)) {
                continue;
            }
            $eqLogic->setIsEnable(Utils::init('isEnable'));
            $eqLogic->save();
        }
        $this->ajax->success();
    }

    public function simpleSave()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $eqLogicSave = json_decode(Utils::init(NextDomObj::EQLOGIC), true);
        $eqLogic = EqLogicManager::byId($eqLogicSave[AjaxParams::ID]);
        if (!is_object($eqLogic)) {
            throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID ') . $eqLogicSave[AjaxParams::ID]);
        }

        if (!$eqLogic->hasRight(ActionRight::WRITE)) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        Utils::a2o($eqLogic, $eqLogicSave);
        $eqLogic->save();
        $this->ajax->success();
    }

    public function copy()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $eqLogic = EqLogicManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($eqLogic)) {
            throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID'));
        }
        if (Utils::init('name') == '') {
            throw new CoreException(__('Le nom de la copie de l\'équipement ne peut être vide'));
        }
        $this->ajax->success(Utils::o2a($eqLogic->copy(Utils::init('name'))));
    }

    public function remove()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $eqLogic = EqLogicManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($eqLogic)) {
            throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID ') . Utils::init(AjaxParams::ID));
        }
        if (!$eqLogic->hasRight(ActionRight::WRITE)) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        $eqLogic->remove();
        $this->ajax->success();
    }

    public function get()
    {
        $typeEqLogic = Utils::init(AjaxParams::TYPE);
        if ($typeEqLogic == '' || !class_exists($typeEqLogic)) {
            throw new CoreException(__('Type incorrect (classe équipement inexistante) : ') . $typeEqLogic);
        }
        $eqLogic = $typeEqLogic::byId(Utils::init(AjaxParams::ID));
        if (!is_object($eqLogic)) {
            throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID ') . Utils::init(AjaxParams::ID));
        }
        $result = Utils::o2a($eqLogic);
        $result[NextDomObj::CMD] = Utils::o2a($eqLogic->getCmd());
        $this->ajax->success(NextDomHelper::toHumanReadable($result));
    }

    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $eqLogicsSave = json_decode(Utils::init(NextDomObj::EQLOGIC), true);

        foreach ($eqLogicsSave as $eqLogicSave) {
            try {
                if (!is_array($eqLogicSave)) {
                    throw new CoreException(__('Informations reçues incorrectes'));
                }
                $typeEqLogic = Utils::initStr(AjaxParams::TYPE);
                $typeCmd = $typeEqLogic . NextDomObj::CMD;
                if ($typeEqLogic == '' || !class_exists($typeEqLogic) || !class_exists($typeCmd)) {
                    throw new CoreException(__('Type incorrect, (classe commande inexistante)') . $typeCmd);
                }
                /** @var EqLogic $eqLogic */
                $eqLogic = null;
                if (isset($eqLogicSave[AjaxParams::ID])) {
                    $eqLogic = $typeEqLogic::byId($eqLogicSave[AjaxParams::ID]);
                }
                if (!is_object($eqLogic)) {
                    $eqLogic = new $typeEqLogic();
                    $eqLogic->setEqType_name(Utils::init(AjaxParams::TYPE));
                } else {
                    if (!$eqLogic->hasRight(ActionRight::WRITE)) {
                        throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
                    }
                }
                if (method_exists($eqLogic, 'preAjax')) {
                    $eqLogic->preAjax();
                }
                $eqLogicSave = NextDomHelper::fromHumanReadable($eqLogicSave);
                Utils::a2o($eqLogic, $eqLogicSave);
                $dbList = CmdManager::byEqLogicId($eqLogic->getId());
                $eqLogic->save();
                $enableList = [];

                if (isset($eqLogicSave[NextDomObj::CMD])) {
                    $cmd_order = 0;
                    foreach ($eqLogicSave[NextDomObj::CMD] as $cmd_info) {
                        $cmd = null;
                        if (isset($cmd_info[AjaxParams::ID])) {
                            $cmd = CmdManager::byId($cmd_info[AjaxParams::ID]);
                        }
                        if (!is_object($cmd)) {
                            $cmd = new $typeCmd();
                        }
                        $cmd->setEqLogic_id($eqLogic->getId());
                        $cmd->setOrder($cmd_order);
                        Utils::a2o($cmd, $cmd_info);
                        $cmd->save();
                        $cmd_order++;
                        $enableList[$cmd->getId()] = true;
                    }
                    foreach ($dbList as $dbObject) {
                        if (!isset($enableList[$dbObject->getId()]) && !$dbObject->dontRemoveCmd()) {
                            $dbObject->remove();
                        }
                    }
                }
                if (method_exists($eqLogic, 'postAjax')) {
                    $eqLogic->postAjax();
                }
                $this->ajax->success(Utils::o2a($eqLogic));
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), '[MySQL] Error code : 23000') !== false) {
                    if ($e->getTrace()[2]['class'] == NextDomObj::EQLOGIC) {
                        throw new CoreException(__('Un équipement portant ce nom (') . $e->getTrace()[0]['args'][1]['name'] . __(') existe déjà pour cet objet'));
                    } elseif ($e->getTrace()[2]['class'] == NextDomObj::CMD) {
                        throw new CoreException(__('Une commande portant ce nom (') . $e->getTrace()[0]['args'][1]['name'] . __(') existe déjà pour cet équipement'));
                    }
                } else {
                    throw new CoreException($e->getMessage());
                }
            }
        }
        $this->ajax->success(null);
    }

    public function getAlert()
    {
        $alerts = [];
        foreach (EqLogicManager::all() as $eqLogic) {
            if ($eqLogic->getAlert() == '') {
                continue;
            }
            $alerts[] = $eqLogic->toHtml(Utils::init(AjaxParams::VERSION));
        }
        $this->ajax->success($alerts);
    }

    public function getUseBeforeRemove()
    {
        $eqLogic = EqLogicManager::byId(Utils::init('id'));
        if (!is_object($eqLogic)) {
            $this->ajax->error(__('ID de l\'objet manquant.'));
        } else {
            $data = ['node' => [], 'link' => []];
            $data = $eqLogic->getLinkData($data, 0, 2);
            $used = $data['node'];
            $eqLogicCode = 'eqLogic' . $eqLogic->getId();
            if (isset($used[$eqLogicCode])) {
                unset($used[$eqLogicCode]);
            }
            /** @var Cmd $cmd */
            foreach ($eqLogic->getCmd() as $cmd) {
                $cmdCode = 'cmd' . $cmd->getId();
                if (isset($used[$cmdCode])) {
                    unset($used[$cmdCode]);
                }
                $cmdData = ['node' => [], 'link' => []];
                $cmdData = $cmd->getLinkData($cmdData, 0, 2);
                if (isset($cmdData['node'][$eqLogicCode])) {
                    unset($cmdData['node'][$eqLogicCode]);
                }
                if (isset($cmdData['node'][$cmdCode])) {
                    unset($cmdData['node'][$cmdCode]);
                }
                if (count($cmdData['node']) > 0) {
                    foreach ($cmdData['node'] as $name => $data) {
                        $data['sourceName'] = $cmd->getName();
                        $used[$name . $cmd->getName()] = $data;
                    }
                }
            }
            $this->ajax->success($used);
        }
    }
}
