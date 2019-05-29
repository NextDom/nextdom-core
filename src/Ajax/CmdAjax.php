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
use NextDom\Managers\CmdManager;
use NextDom\Managers\HistoryManager;
use NextDom\Model\Entity\Cmd;
use NextDom\Model\Entity\EqLogic;
use NextDom\Model\Entity\Scenario;

/**
 * Class CmdAjax
 * @package NextDom\Ajax
 */
class CmdAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    /**
     * Get command HTML render
     *
     * @throws CoreException
     */
    public function toHtml()
    {
        if (Utils::init('ids') != '') {
            $return = array();
            foreach (json_decode(Utils::init('ids'), true) as $id => $value) {
                $cmd = CmdManager::byId($id);
                if (!is_object($cmd)) {
                    continue;
                }
                $return[$cmd->getId()] = array(
                    'html' => $cmd->toHtml($value['version']),
                    'id' => $cmd->getId(),
                );
            }
            AjaxHelper::success($return);
        } else {
            $cmd = CmdManager::byId(Utils::init('id'));
            if (!is_object($cmd)) {
                throw new CoreException(__('Commande inconnue - Vérifiez l\'id'));
            }
            $info_cmd = array();
            $info_cmd['id'] = $cmd->getId();
            $info_cmd['html'] = $cmd->toHtml(Utils::init('version'), Utils::init('option'), Utils::init('cmdColor', null));
            AjaxHelper::success($info_cmd);
        }
    }

    /**
     * Execute commande
     *
     * @throws CoreException
     */
    public function execCmd()
    {
        $cmdId = Utils::init('id');
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande ID inconnu : ') . $cmdId);
        }
        $eqLogic = $cmd->getEqLogicId();
        if ($cmd->getType() == 'action' && !$eqLogic->hasRight('x')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        if (!$cmd->checkAccessCode(Utils::init('codeAccess'))) {
            throw new CoreException(__('Cette action nécessite un code d\'accès'), -32005);
        }
        if ($cmd->getType() == 'action' && $cmd->getConfiguration('actionConfirm') == 1 && Utils::init('confirmAction') != 1) {
            throw new CoreException(__('Cette action nécessite une confirmation'), -32006);
        }
        $options = json_decode(Utils::init('value', '{}'), true);
        if (Utils::init('utid') != '') {
            $options['utid'] = Utils::init('utid');
        }
        AjaxHelper::success($cmd->execCmd($options));
    }

    public function getByObjectNameEqNameCmdName()
    {
        $cmd = CmdManager::byObjectNameEqLogicNameCmdName(Utils::init('object_name'), Utils::init('eqLogic_name'), Utils::init('cmd_name'));
        if (!is_object($cmd)) {
            throw new CoreException(__('Cmd inconnu : ') . Utils::init('object_name') . '/' . Utils::init('eqLogic_name') . '/' . Utils::init('cmd_name'));
        }
        AjaxHelper::success($cmd->getId());
    }

    public function getByObjectNameCmdName()
    {
        $cmd = CmdManager::byObjectNameCmdName(Utils::init('object_name'), Utils::init('cmd_name'));
        if (!is_object($cmd)) {
            throw new CoreException(__('Cmd inconnu : ') . Utils::init('object_name') . '/' . Utils::init('cmd_name'), 9999);
        }
        AjaxHelper::success(Utils::o2a($cmd));
    }

    /**
     * Get command object by his id
     *
     * @throws CoreException
     */
    public function byId()
    {
        $cmd = CmdManager::byId(Utils::init('id'));
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande inconnue : ') . Utils::init('id'), 9999);
        }
        AjaxHelper::success(NextDomHelper::toHumanReadable(Utils::o2a($cmd)));
    }

    public function copyHistoryToCmd()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        HistoryManager::copyHistoryToCmd(Utils::init('source_id'), Utils::init('target_id'));
        AjaxHelper::success();
    }

    public function replaceCmd()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        NextDomHelper::replaceTag(array('#' . str_replace('#', '', Utils::init('source_id')) . '#' => '#' . str_replace('#', '', Utils::init('target_id')) . '#'));
        AjaxHelper::success();
    }

    public function byHumanName()
    {
        $cmd_id = CmdManager::humanReadableToCmd(Utils::init('humanName'));
        $cmd = CmdManager::byId(str_replace('#', '', $cmd_id));
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande inconnue : ') . Utils::init('humanName'), 9999);
        }
        AjaxHelper::success(Utils::o2a($cmd));
    }

    public function usedBy()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $cmd = CmdManager::byId(Utils::init('id'));
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande inconnue : ') . Utils::init('id'), 9999);
        }
        $result = $cmd->getUsedBy();
        $return = array('cmd' => array(), 'eqLogic' => array(), 'scenario' => array());
        /**
         * @var Cmd $cmd
         */
        foreach ($result['cmd'] as $cmd) {
            $info = Utils::o2a($cmd);
            $info['humanName'] = $cmd->getHumanName();
            $info['link'] = $cmd->getEqLogic()->getLinkToConfiguration();
            $return['cmd'][] = $info;
        }
        /**
         * @var EqLogic $eqLogic
         */
        foreach ($result['eqLogic'] as $eqLogic) {
            $info = Utils::o2a($eqLogic);
            $info['humanName'] = $eqLogic->getHumanName();
            $info['link'] = $eqLogic->getLinkToConfiguration();
            $return['eqLogic'][] = $info;
        }
        /**
         * @var Scenario $scenario
         */
        foreach ($result['scenario'] as $scenario) {
            $info = Utils::o2a($cmd);
            $info['humanName'] = $scenario->getHumanName();
            $info['link'] = $scenario->getLinkToConfiguration();
            $return['scenario'][] = $info;
        }
        AjaxHelper::success($return);
    }

    public function getHumanCmdName()
    {
        AjaxHelper::success(CmdManager::cmdToHumanReadable('#' . Utils::init('id') . '#'));
    }

    public function byEqLogic()
    {
        AjaxHelper::success(Utils::o2a(CmdManager::byEqLogicId(Utils::init('eqLogic_id'))));
    }

    public function getCmd()
    {
        $cmd = CmdManager::byId(Utils::init('id'));
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande inconnue : ') . Utils::init('id'));
        }
        $return = NextDomHelper::toHumanReadable(Utils::o2a($cmd));
        $eqLogic = $cmd->getEqLogicId();
        $return['eqLogic_name'] = $eqLogic->getName();
        $return['plugin'] = $eqLogic->getEqType_Name();
        if ($eqLogic->getObject_id() > 0) {
            $return['object_name'] = $eqLogic->getObject()->getName();
        }
        AjaxHelper::success($return);
    }

    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $cmd_ajax = NextDomHelper::fromHumanReadable(json_decode(Utils::init('cmd'), true));
        $cmd = CmdManager::byId($cmd_ajax['id']);
        if (!is_object($cmd)) {
            $cmd = new Cmd();
        }
        Utils::a2o($cmd, $cmd_ajax);
        $cmd->save();
        AjaxHelper::success(Utils::o2a($cmd));
    }

    public function multiSave()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $cmds = json_decode(Utils::init('cmd'), true);
        foreach ($cmds as $cmd_ajax) {
            $cmd = CmdManager::byId($cmd_ajax['id']);
            if (!is_object($cmd)) {
                continue;
            }
            Utils::a2o($cmd, $cmd_ajax);
            $cmd->save();
        }
        AjaxHelper::success();
    }

    public function changeHistoryPoint()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        if (Utils::init('cmd_id') === '') {
            throw new CoreException(__('Historique impossible'));
        }
        $history = HistoryManager::byCmdIdDatetime(Utils::init('cmd_id'), Utils::init('datetime'));
        if (!is_object($history)) {
            $history = HistoryManager::byCmdIdDatetime(Utils::init('cmd_id'), Utils::init('datetime'), date('Y-m-d H:i:s', strtotime(Utils::init('datetime') . ' +1 hour')), Utils::init('oldValue'));
        }
        if (!is_object($history)) {
            $history = HistoryManager::byCmdIdDatetime(Utils::init('cmd_id'), Utils::init('datetime'), date('Y-m-d H:i:s', strtotime(Utils::init('datetime') . ' +1 day')), Utils::init('oldValue'));
        }
        if (!is_object($history)) {
            $history = HistoryManager::byCmdIdDatetime(Utils::init('cmd_id'), Utils::init('datetime'), date('Y-m-d H:i:s', strtotime(Utils::init('datetime') . ' +1 week')), Utils::init('oldValue'));
        }
        if (!is_object($history)) {
            $history = HistoryManager::byCmdIdDatetime(Utils::init('cmd_id'), Utils::init('datetime'), date('Y-m-d H:i:s', strtotime(Utils::init('datetime') . ' +1 month')), Utils::init('oldValue'));
        }
        if (!is_object($history)) {
            throw new CoreException(__('Aucun point ne correspond pour l\'historique : ') . Utils::init('cmd_id') . ' - ' . Utils::init('datetime') . ' - ' . Utils::init('oldValue'));
        }
        $value = Utils::init('value', null);
        if ($value === '') {
            $history->remove();
        } else {
            $history->setValue($value);
            $history->save(null, true);
        }
        AjaxHelper::success();
    }

    public function getHistory()
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        $return = array();
        $data = array();
        $dateStart = null;
        $dateEnd = null;
        if (Utils::init('dateRange') != '' && Utils::init('dateRange') != 'all') {
            if (is_json(Utils::init('dateRange'))) {
                $dateRange = json_decode(Utils::init('dateRange'), true);
                if (isset($dateRange['start'])) {
                    $dateStart = $dateRange['start'];
                }
                if (isset($dateRange['end'])) {
                    $dateEnd = $dateRange['end'];
                }
            } else {
                $dateEnd = date('Y-m-d H:i:s');
                $dateStart = date('Y-m-d 00:00:00', strtotime('- ' . Utils::init('dateRange') . ' ' . $dateEnd));
            }
        }
        if (Utils::init('dateStart') != '') {
            $dateStart = Utils::init('dateStart');
        }
        if (Utils::init('dateEnd') != '') {
            $dateEnd = Utils::init('dateEnd');
            if ($dateEnd == date('Y-m-d')) {
                $dateEnd = date('Y-m-d H:i:s');
            }
        }
        if (strtotime($dateEnd) > strtotime('now')) {
            $dateEnd = date('Y-m-d H:i:s');
        }
        $return['maxValue'] = '';
        $return['minValue'] = '';
        if ($dateStart === null) {
            $return['dateStart'] = '';
        } else {
            $return['dateStart'] = $dateStart;
        }
        if ($dateEnd === null) {
            $return['dateEnd'] = '';
        } else {
            $return['dateEnd'] = $dateEnd;
        }

        if (is_numeric(Utils::init('id'))) {
            $cmd = CmdManager::byId(Utils::init('id'));
            if (!is_object($cmd)) {
                throw new CoreException(__('Commande ID inconnu : ') . Utils::init('id'));
            }
            $eqLogic = $cmd->getEqLogicId();
            if (!$eqLogic->hasRight('r')) {
                throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
            }
            $histories = $cmd->getHistory($dateStart, $dateEnd);
            $return['cmd_name'] = $cmd->getName();
            $return['history_name'] = $cmd->getHumanName();
            $return['unite'] = $cmd->getUnite();
            $return['cmd'] = Utils::o2a($cmd);
            $return['eqLogic'] = Utils::o2a($cmd->getEqLogicId());
            $return['timelineOnly'] = $NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['timelineOnly'];
            $previsousValue = null;
            $derive = Utils::init('derive', $cmd->getDisplay('graphDerive'));
            if (trim($derive) == '') {
                $derive = $cmd->getDisplay('graphDerive');
            }
            foreach ($histories as $history) {
                $info_history = array();
                if ($cmd->getDisplay('groupingType') != '') {
                    $info_history[] = floatval(strtotime($history->getDatetime() . " UTC")) * 1000 - 1;
                } else {
                    $info_history[] = floatval(strtotime($history->getDatetime() . " UTC")) * 1000;
                }
                if ($NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['timelineOnly']) {
                    $value = $history->getValue();
                } else {
                    $value = ($history->getValue() === null) ? null : floatval($history->getValue());
                    if ($derive == 1 || $derive == '1') {
                        if ($value !== null && $previsousValue !== null) {
                            $value = $value - $previsousValue;
                        } else {
                            $value = null;
                        }
                        $previsousValue = ($history->getValue() === null) ? null : floatval($history->getValue());
                    }
                }
                $info_history[] = $value;
                if (!$NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['timelineOnly']) {
                    if (($value !== null && $value > $return['maxValue']) || $return['maxValue'] == '') {
                        $return['maxValue'] = round($value, 1);
                    }
                    if (($value !== null && $value < $return['minValue']) || $return['minValue'] == '') {
                        $return['minValue'] = round($value, 1);
                    }
                }
                $data[] = $info_history;
            }
        } else {
            $histories = HistoryManager::getHistoryFromCalcul(NextDomHelper::fromHumanReadable(Utils::init('id')), $dateStart, $dateEnd, Utils::init('allowZero', false));
            if (is_array($histories)) {
                foreach ($histories as $datetime => $value) {
                    $info_history = array();
                    $info_history[] = floatval($datetime) * 1000;
                    $info_history[] = ($value === null) ? null : floatval($value);
                    if ($value > $return['maxValue'] || $return['maxValue'] == '') {
                        $return['maxValue'] = round($value, 1);
                    }
                    if ($value < $return['minValue'] || $return['minValue'] == '') {
                        $return['minValue'] = round($value, 1);
                    }
                    $data[] = $info_history;
                }
            }
            $return['cmd_name'] = Utils::init('id');
            $return['history_name'] = Utils::init('id');
            $return['unite'] = Utils::init('unite');
        }
        $return['data'] = $data;
        AjaxHelper::success($return);
    }

    public function emptyHistory()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $cmd = CmdManager::byId(Utils::init('id'));
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande ID inconnu : ') . Utils::init('id'));
        }
        $cmd->emptyHistory(Utils::init('date'));
        AjaxHelper::success();
    }

    public function setOrder()
    {
        Utils::unautorizedInDemo();
        $cmds = json_decode(Utils::init('cmds'), true);
        $eqLogics = array();
        foreach ($cmds as $cmd_json) {
            if (!isset($cmd_json['id']) || trim($cmd_json['id']) == '') {
                continue;
            }
            $cmd = CmdManager::byId($cmd_json['id']);
            if (!is_object($cmd)) {
                continue;
            }
            if ($cmd->getOrder() != $cmd_json['order']) {
                $cmd->setOrder($cmd_json['order']);
                $cmd->save();
            }
            if (isset($cmd_json['line']) && isset($cmd_json['column'])) {
                $eqLogic = $cmd->getEqLogicId();
                if (!isset($eqLogics[$cmd->getEqLogic_id()])) {
                    $eqLogics[$cmd->getEqLogic_id()] = array('eqLogic' => $cmd->getEqLogic(), 'changed' => false);
                }
                if ($eqLogics[$cmd->getEqLogic_id()]['eqLogic']->getDisplay('layout::' . Utils::init('version', 'dashboard') . '::table::CmdManager::' . $cmd->getId() . '::line') != $cmd_json['line'] || $eqLogics[$cmd->getEqLogic_id()]['eqLogic']->getDisplay('layout::' . Utils::init('version', 'dashboard') . '::table::CmdManager::' . $cmd->getId() . '::column') != $cmd_json['column']) {
                    $eqLogics[$cmd->getEqLogic_id()]['eqLogic']->setDisplay('layout::' . Utils::init('version', 'dashboard') . '::table::CmdManager::' . $cmd->getId() . '::line', $cmd_json['line']);
                    $eqLogics[$cmd->getEqLogic_id()]['eqLogic']->setDisplay('layout::' . Utils::init('version', 'dashboard') . '::table::CmdManager::' . $cmd->getId() . '::column', $cmd_json['column']);
                    $eqLogics[$cmd->getEqLogic_id()]['changed'] = true;
                }
            }
        }
        /**
         * @var EqLogic[] $eqLogic
         */
        foreach ($eqLogics as $eqLogic) {
            if (!$eqLogic['changed']) {
                continue;
            }
            $eqLogic['eqLogic']->save(true);
        }
        AjaxHelper::success();
    }
}