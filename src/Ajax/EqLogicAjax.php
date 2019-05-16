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
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\ObjectManager;

class EqLogicAjax extends BaseAjax
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
     * @throws \ReflectionException
     */
    public function getEqLogicObject()
    {
        $object = ObjectManager::byId(Utils::init('object_id'));

        if (!is_object($object)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $return = Utils::o2a($object);
        $return['eqLogic'] = array();
        foreach ($object->getEqLogic() as $eqLogic) {
            if ($eqLogic->getIsVisible() == '1') {
                $info_eqLogic = array();
                $info_eqLogic['id'] = $eqLogic->getId();
                $info_eqLogic['type'] = $eqLogic->getEqType_name();
                $info_eqLogic['object_id'] = $eqLogic->getObject_id();
                $info_eqLogic['html'] = $eqLogic->toHtml(Utils::init('version'));
                $return['eqLogic'][] = $info_eqLogic;
            }
        }
        AjaxHelper::success($return);
    }

    /**
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function byId()
    {
        $eqLogic = EqLogicManager::byId(Utils::init('id'));
        if (!is_object($eqLogic)) {
            throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID'));
        }
        AjaxHelper::success(Utils::o2a($eqLogic));
    }

    /**
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function toHtml()
    {
        if (Utils::init('ids') != '') {
            $return = array();
            foreach (json_decode(Utils::init('ids'), true) as $id => $value) {
                $eqLogic = EqLogicManager::byId($id);
                if (!is_object($eqLogic)) {
                    continue;
                }
                $return[$eqLogic->getId()] = array(
                    'html' => $eqLogic->toHtml($value['version']),
                    'id' => $eqLogic->getId(),
                    'type' => $eqLogic->getEqType_name(),
                    'object_id' => $eqLogic->getObject_id(),
                );
            }
            AjaxHelper::success($return);
        } else {
            $eqLogic = EqLogicManager::byId(Utils::init('id'));
            if (!is_object($eqLogic)) {
                throw new CoreException(__('Eqlogic inconnu. Vérifiez l\'ID'));
            }
            $info_eqLogic = array();
            $info_eqLogic['id'] = $eqLogic->getId();
            $info_eqLogic['type'] = $eqLogic->getEqType_name();
            $info_eqLogic['object_id'] = $eqLogic->getObject_id();
            $info_eqLogic['html'] = $eqLogic->toHtml(Utils::init('version'));
            AjaxHelper::success($info_eqLogic);
        }
    }

    /**
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function htmlAlert()
    {
        $return = array();
        foreach (EqLogicManager::all() as $eqLogic) {
            if ($eqLogic->getAlert() == '') {
                continue;
            }
            $return[$eqLogic->getId()] = array(
                'html' => $eqLogic->toHtml(Utils::init('version')),
                'id' => $eqLogic->getId(),
                'type' => $eqLogic->getEqType_name(),
                'object_id' => $eqLogic->getObject_id(),
            );
        }
        AjaxHelper::success($return);
    }

    /**
     * @throws \Exception
     */
    public function htmlBattery()
    {
        $return = array();
        $list = array();
        foreach (EqLogicManager::all() as $eqLogic) {
            if ($eqLogic->getStatus('battery', -2) != -2) {
                $list[] = $eqLogic;
            }
        }
        usort($list, function ($a, $b) {
            return ($a->getStatus('battery') < $b->getStatus('battery')) ? -1 : (($a->getStatus('battery') > $b->getStatus('battery')) ? 1 : 0);
        });
        foreach ($list as $eqLogic) {
            $return[] = array(
                'html' => $eqLogic->batteryWidget(Utils::init('version')),
                'id' => $eqLogic->getId(),
                'type' => $eqLogic->getEqType_name(),
                'object_id' => $eqLogic->getObject_id(),
            );
        }
        AjaxHelper::success($return);
    }

    /**
     * @throws \ReflectionException
     */
    public function listByType()
    {
        AjaxHelper::success(Utils::a2o(EqLogicManager::byType(Utils::init('type'))));
    }

    /**
     * @throws \Exception
     */
    public function listByObjectAndCmdType()
    {
        $object_id = (Utils::init('object_id') != -1) ? Utils::init('object_id') : null;
        AjaxHelper::success(EqLogicManager::listByObjectAndCmdType($object_id, Utils::init('typeCmd'), Utils::init('subTypeCmd')));
    }

    /**
     * @throws \ReflectionException
     */
    public function listByObject()
    {
        $object_id = (Utils::init('object_id') != -1) ? Utils::init('object_id') : null;
        AjaxHelper::success(Utils::o2a(EqLogicManager::byObjectId($object_id, Utils::init('onlyEnable', true), Utils::init('onlyVisible', false), Utils::init('eqType_name', null), Utils::init('logicalId', null), Utils::init('orderByName', false))));
    }

    /**
     * @throws \ReflectionException
     */
    public function listByTypeAndCmdType()
    {
        $results = EqLogicManager::listByTypeAndCmdType(Utils::init('type'), Utils::init('typeCmd'), Utils::init('subTypeCmd'));
        $return = array();
        foreach ($results as $result) {
            $eqLogic = EqLogicManager::byId($result['id']);
            $info['eqLogic'] = Utils::o2a($eqLogic);
            $info['object'] = array('name' => 'Aucun');
            if (is_object($eqLogic)) {
                $object = $eqLogic->getObject();
                if (is_object($object)) {
                    $info['object'] = Utils::o2a($eqLogic->getObject());
                }
            }
            $return[] = $info;
        }
        AjaxHelper::success($return);
    }

    /**
     * @throws CoreException
     */
    public function setIsEnable()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $eqLogic = EqLogicManager::byId(Utils::init('id'));
        if (!is_object($eqLogic)) {
            throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID'));
        }
        if (!$eqLogic->hasRight('w')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        $eqLogic->setIsEnable(Utils::init('isEnable'));
        $eqLogic->save();
        AjaxHelper::success();
    }

    /**
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function setOrder()
    {
        Utils::unautorizedInDemo();
        $eqLogics = json_decode(Utils::init('eqLogics'), true);
        foreach ($eqLogics as $eqLogic_json) {
            if (!isset($eqLogic_json['id']) || trim($eqLogic_json['id']) == '') {
                continue;
            }
            $eqLogic = EqLogicManager::byId($eqLogic_json['id']);
            if (!is_object($eqLogic)) {
                continue;
            }
            Utils::a2o($eqLogic, $eqLogic_json);
            $eqLogic->save(true);
        }
        AjaxHelper::success();
    }

    /**
     * @throws CoreException
     */
    public function removes()
    {
        Utils::unautorizedInDemo();
        $eqLogics = json_decode(Utils::init('eqLogics'), true);
        foreach ($eqLogics as $id) {
            $eqLogic = EqLogicManager::byId($id);
            if (!is_object($eqLogic)) {
                throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID') . ' ' . $id);
            }
            if (!$eqLogic->hasRight('w')) {
                continue;
            }
            $eqLogic->remove();
        }
        AjaxHelper::success();
    }

    /**
     * @throws CoreException
     */
    public function setIsVisibles()
    {
        Utils::unautorizedInDemo();
        $eqLogics = json_decode(Utils::init('eqLogics'), true);
        foreach ($eqLogics as $id) {
            $eqLogic = EqLogicManager::byId($id);
            if (!is_object($eqLogic)) {
                throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID') . ' ' . $id);
            }
            if (!$eqLogic->hasRight('w')) {
                continue;
            }
            $eqLogic->setIsVisible(Utils::init('isVisible'));
            $eqLogic->save();
        }
        AjaxHelper::success();
    }

    /**
     * @throws CoreException
     */
    public function setIsEnables()
    {
        Utils::unautorizedInDemo();
        $eqLogics = json_decode(Utils::init('eqLogics'), true);
        foreach ($eqLogics as $id) {
            $eqLogic = EqLogicManager::byId($id);
            if (!is_object($eqLogic)) {
                throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID') . ' ' . $id);
            }
            if (!$eqLogic->hasRight('w')) {
                continue;
            }
            $eqLogic->setIsEnable(Utils::init('isEnable'));
            $eqLogic->save();
        }
        AjaxHelper::success();
    }

    /**
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function simpleSave()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $eqLogicSave = json_decode(Utils::init('eqLogic'), true);
        $eqLogic = EqLogicManager::byId($eqLogicSave['id']);
        if (!is_object($eqLogic)) {
            throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID ') . $eqLogicSave['id']);
        }

        if (!$eqLogic->hasRight('w')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        Utils::a2o($eqLogic, $eqLogicSave);
        $eqLogic->save();
        AjaxHelper::success();
    }

    /**
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function copy()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $eqLogic = EqLogicManager::byId(Utils::init('id'));
        if (!is_object($eqLogic)) {
            throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID'));
        }
        if (Utils::init('name') == '') {
            throw new CoreException(__('Le nom de la copie de l\'équipement ne peut être vide'));
        }
        AjaxHelper::success(Utils::o2a($eqLogic->copy(Utils::init('name'))));
    }

    /**
     * @throws CoreException
     */
    public function remove()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $eqLogic = EqLogicManager::byId(Utils::init('id'));
        if (!is_object($eqLogic)) {
            throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID ') . Utils::init('id'));
        }
        if (!$eqLogic->hasRight('w')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        $eqLogic->remove();
        AjaxHelper::success();
    }

    /**
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function get()
    {
        $typeEqLogic = Utils::init('type');
        if ($typeEqLogic == '' || !class_exists($typeEqLogic)) {
            throw new CoreException(__('Type incorrect (classe équipement inexistante) : ') . $typeEqLogic);
        }
        $eqLogic = $typeEqLogic::byId(Utils::init('id'));
        if (!is_object($eqLogic)) {
            throw new CoreException(__('EqLogic inconnu. Vérifiez l\'ID ') . Utils::init('id'));
        }
        $return = Utils::o2a($eqLogic);
        $return['cmd'] = Utils::o2a($eqLogic->getCmd());
        AjaxHelper::success(NextDomHelper::toHumanReadable($return));
    }

    /**
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();

        $eqLogicsSave = json_decode(Utils::init('eqLogic'), true);

        foreach ($eqLogicsSave as $eqLogicSave) {
            try {
                if (!is_array($eqLogicSave)) {
                    throw new CoreException(__('Informations reçues incorrectes'));
                }
                $typeEqLogic = Utils::init('type');
                $typeCmd = $typeEqLogic . 'Cmd';
                if ($typeEqLogic == '' || !class_exists($typeEqLogic) || !class_exists($typeCmd)) {
                    throw new CoreException(__('Type incorrect, (classe commande inexistante)') . $typeCmd);
                }
                $eqLogic = null;
                if (isset($eqLogicSave['id'])) {
                    $eqLogic = $typeEqLogic::byId($eqLogicSave['id']);
                }
                if (!is_object($eqLogic)) {
                    $eqLogic = new $typeEqLogic();
                    $eqLogic->setEqType_name(Utils::init('type'));
                } else {
                    if (!$eqLogic->hasRight('w')) {
                        throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
                    }
                }
                if (method_exists($eqLogic, 'preAjax')) {
                    $eqLogic->preAjax();
                }
                $eqLogicSave = NextDomHelper::fromHumanReadable($eqLogicSave);
                Utils::a2o($eqLogic, $eqLogicSave);
                $dbList = $typeCmd::byEqLogicId($eqLogic->getId());
                $eqLogic->save();
                $enableList = array();

                if (isset($eqLogicSave['cmd'])) {
                    $cmd_order = 0;
                    foreach ($eqLogicSave['cmd'] as $cmd_info) {
                        $cmd = null;
                        if (isset($cmd_info['id'])) {
                            $cmd = $typeCmd::byId($cmd_info['id']);
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
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), '[MySQL] Error code : 23000') !== false) {
                    if ($e->getTrace()[2]['class'] == 'eqLogic') {
                        throw new CoreException(__('Un équipement portant ce nom (') . $e->getTrace()[0]['args'][1]['name'] . __(') existe déjà pour cet objet'));
                    } elseif ($e->getTrace()[2]['class'] == 'cmd') {
                        throw new CoreException(__('Une commande portant ce nom (') . $e->getTrace()[0]['args'][1]['name'] . __(') existe déjà pour cet équipement'));
                    }
                } else {
                    throw new CoreException($e->getMessage());
                }
            }
            AjaxHelper::success(Utils::o2a($eqLogic));
        }
        AjaxHelper::success(null);
    }

    /**
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function getAlert()
    {
        $alerts = array();
        foreach (EqLogicManager::all() as $eqLogic) {
            if ($eqLogic->getAlert() == '') {
                continue;
            }
            $alerts[] = $eqLogic->toHtml(Utils::init('version'));
        }
        AjaxHelper::success($alerts);
    }

}
