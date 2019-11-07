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

use NextDom\Enums\CmdType;
use NextDom\Enums\CmdViewType;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\FileSystemHelper;
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
     * Get one or more command(s) HTML render
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function toHtml()
    {
        // Render list of id
        if (Utils::init('ids') != '') {
            $result = [];
            foreach (json_decode(Utils::init('ids'), true) as $id => $value) {
                $cmd = CmdManager::byId($id);
                if (!is_object($cmd)) {
                    continue;
                }
                $result[$cmd->getId()] = array(
                    'html' => $cmd->toHtml($value['version']),
                    'id' => $cmd->getId(),
                );
            }
            $this->ajax->success($result);
        } else {
            // Render one command
            $cmd = CmdManager::byId(Utils::init('id'));
            if (!is_object($cmd)) {
                throw new CoreException(__('Commande inconnue - Vérifiez l\'id'));
            }
            $result = [];
            $result['id'] = $cmd->getId();
            $result['html'] = $cmd->toHtml(Utils::init('version'), Utils::init('option'), Utils::init('cmdColor', null));
            $this->ajax->success($result);
        }
    }

    /**
     * Execute a command
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function execCmd()
    {
        $cmdId = Utils::init('id');
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande ID inconnu : ') . $cmdId);
        }
        $eqLogic = $cmd->getEqLogicId();
        if ($cmd->getType() == CmdType::ACTION && !$eqLogic->hasRight('x')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        if (!$cmd->checkAccessCode(Utils::init('codeAccess'))) {
            throw new CoreException(__('Cette action nécessite un code d\'accès'), -32005);
        }
        if ($cmd->getType() == CmdType::ACTION && $cmd->getConfiguration('actionConfirm') == 1 && Utils::init('confirmAction') != 1) {
            throw new CoreException(__('Cette action nécessite une confirmation'), -32006);
        }
        $options = json_decode(Utils::init('value', '{}'), true);
        if (Utils::init('utid') != '') {
            $options['utid'] = Utils::init('utid');
        }
        $this->ajax->success($cmd->execCmd($options));
    }

    /**
     * Get an object from his name, eqLogic name and command name
     *
     * @throws CoreException
     */
    public function getByObjectNameEqNameCmdName()
    {
        $objectName = Utils::init('object_name');
        $eqLogicName = Utils::init('eqLogic_name');
        $cmdName = Utils::init('cmd_name');
        $cmd = CmdManager::byObjectNameEqLogicNameCmdName($objectName, $eqLogicName, $cmdName);
        if (!is_object($cmd)) {
            throw new CoreException(__('Cmd inconnu : ') . $objectName . '/' . $eqLogicName . '/' . $cmdName);
        }
        $this->ajax->success($cmd->getId());
    }

    /**
     * Get an object from his name and command name
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function getByObjectNameCmdName()
    {
        $objectName = Utils::init('object_name');
        $cmdName = Utils::init('cmd_name');
        $cmd = CmdManager::byObjectNameCmdName($objectName, $cmdName);
        if (!is_object($cmd)) {
            throw new CoreException(__('Cmd inconnu : ') . $objectName . '/' . $cmdName, 9999);
        }
        $this->ajax->success(Utils::o2a($cmd));
    }

    /**
     * Get command object by his id
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function byId()
    {
        $cmdId = Utils::init('id');
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande inconnue : ') . $cmdId, 9999);
        }
        $this->ajax->success(NextDomHelper::toHumanReadable(Utils::o2a($cmd)));
    }

    /**
     * Copy history from cmd to another one
     *
     * @throws CoreException
     */
    public function copyHistoryToCmd()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        HistoryManager::copyHistoryToCmd(Utils::init('source_id'), Utils::init('target_id'));
        $this->ajax->success();
    }

    /**
     * Replace command with another one
     *
     * @throws CoreException
     */
    public function replaceCmd()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        NextDomHelper::replaceTag(array('#' . str_replace('#', '', Utils::init('source_id')) . '#' => '#' . str_replace('#', '', Utils::init('target_id')) . '#'));
        $this->ajax->success();
    }

    /**
     * Get command by human name
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function byHumanName()
    {
        $humanName = Utils::init('humanName');
        $cmd_id = CmdManager::humanReadableToCmd($humanName);
        $cmd = CmdManager::byId(str_replace('#', '', $cmd_id));
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande inconnue : ') . $humanName, 9999);
        }
        $this->ajax->success(Utils::o2a($cmd));
    }

    /**
     * Get list of command, eqLogic and scenario that use a command
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function usedBy()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $cmdId = Utils::init('id');
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande inconnue : ') . $cmdId, 9999);
        }
        $result = $cmd->getUsedBy();
        $return = ['cmd' => [], 'eqLogic' => [], 'scenario' => []];
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
        $this->ajax->success($return);
    }

    /**
     * Get command human name
     *
     * @throws \ReflectionException
     */
    public function getHumanCmdName()
    {
        $this->ajax->success(CmdManager::cmdToHumanReadable('#' . Utils::init('id') . '#'));
    }

    /**
     * Get list of command by eqLogic id
     * @throws \ReflectionException
     */
    public function byEqLogic()
    {
        $this->ajax->success(Utils::o2a(CmdManager::byEqLogicId(Utils::init('eqLogic_id'))));
    }

    /**
     * Get a command by his id with eqLogic name and Object name
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function getCmd()
    {
        $cmdId = Utils::init('id');
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande inconnue : ') . $cmdId);
        }
        $result = NextDomHelper::toHumanReadable(Utils::o2a($cmd));
        $eqLogic = $cmd->getEqLogicId();
        $result['eqLogic_name'] = $eqLogic->getName();
        $result['plugin'] = $eqLogic->getEqType_Name();
        if ($eqLogic->getObject_id() > 0) {
            $result['object_name'] = $eqLogic->getObject()->getName();
        }
        $this->ajax->success($result);
    }

    /**
     * Save change on command
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $cmdAjaxData = NextDomHelper::fromHumanReadable(json_decode(Utils::init('cmd'), true));
        $cmd = CmdManager::byId($cmdAjaxData['id']);
        if (!is_object($cmd)) {
            $cmd = new Cmd();
        }
        Utils::a2o($cmd, $cmdAjaxData);
        $cmd->save();
        $this->ajax->success(Utils::o2a($cmd));
    }

    /**
     * Save a list of command
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function multiSave()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $cmds = json_decode(Utils::init('cmd'), true);
        foreach ($cmds as $cmdAjaxData) {
            $cmd = CmdManager::byId($cmdAjaxData['id']);
            if (!is_object($cmd)) {
                continue;
            }
            Utils::a2o($cmd, $cmdAjaxData);
            $cmd->save();
        }
        $this->ajax->success();
    }

    /**
     * Change history item from datetime to another
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function changeHistoryPoint()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $cmdId = Utils::init('cmd_id');
        $targetDatetime = Utils::init('datetime');
        $srcDatetime = Utils::init('oldValue');
        if ($cmdId === '') {
            throw new CoreException(__('Historique impossible'));
        }
        $history = HistoryManager::byCmdIdDatetime($cmdId, $targetDatetime);
        if (!is_object($history)) {
            $history = HistoryManager::byCmdIdDatetime($cmdId, $targetDatetime, date('Y-m-d H:i:s', strtotime($targetDatetime . ' +1 hour')), $srcDatetime);
        }
        if (!is_object($history)) {
            $history = HistoryManager::byCmdIdDatetime($cmdId, $targetDatetime, date('Y-m-d H:i:s', strtotime($targetDatetime . ' +1 day')), $srcDatetime);
        }
        if (!is_object($history)) {
            $history = HistoryManager::byCmdIdDatetime($cmdId, $targetDatetime, date('Y-m-d H:i:s', strtotime($targetDatetime . ' +1 week')), $srcDatetime);
        }
        if (!is_object($history)) {
            $history = HistoryManager::byCmdIdDatetime($cmdId, $targetDatetime, date('Y-m-d H:i:s', strtotime($targetDatetime . ' +1 month')), $srcDatetime);
        }
        if (!is_object($history)) {
            throw new CoreException(__('Aucun point ne correspond pour l\'historique : ') . $cmdId . ' - ' . $targetDatetime . ' - ' . $srcDatetime);
        }
        $value = Utils::init('value', null);
        if ($value === '') {
            $history->remove();
        } else {
            $history->setValue($value);
            $history->save(null, true);
        }
        $this->ajax->success();
    }

    /**
     * Get command history
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function getHistory()
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        $result = [];
        $data = [];
        $userDateStart = Utils::init('dateStart');
        $userDateEnd = Utils::init('dateEnd');
        $dateStart = null;
        $dateEnd = null;
        $dateRange = Utils::init('dateRange');
        if ($dateRange != '' && $dateRange != 'all') {
            if (is_json($dateRange)) {
                $dateRange = json_decode(Utils::init('dateRange'), true);
                if (isset($dateRange['start'])) {
                    $dateStart = $dateRange['start'];
                }
                if (isset($dateRange['end'])) {
                    $dateEnd = $dateRange['end'];
                }
            } else {
                $dateEnd = date('Y-m-d H:i:s');
                $dateStart = date('Y-m-d 00:00:00', strtotime('- ' . $dateRange . ' ' . $dateEnd));
            }
        }
        if ($userDateStart != '') {
            $dateStart = $userDateStart;
        }
        if ($userDateEnd != '') {
            $dateEnd = $userDateEnd;
            if ($dateEnd == date('Y-m-d')) {
                $dateEnd = date('Y-m-d H:i:s');
            }
        }
        if (strtotime($dateEnd) > strtotime('now')) {
            $dateEnd = date('Y-m-d H:i:s');
        }
        $result['maxValue'] = '';
        $result['minValue'] = '';
        if ($dateStart === null) {
            $result['dateStart'] = '';
        } else {
            $result['dateStart'] = $dateStart;
        }
        if ($dateEnd === null) {
            $result['dateEnd'] = '';
        } else {
            $result['dateEnd'] = $dateEnd;
        }

        $cmdId = Utils::init('id');
        if (is_numeric($cmdId)) {
            $cmd = CmdManager::byId($cmdId);
            if (!is_object($cmd)) {
                throw new CoreException(__('Commande ID inconnu : ') . $cmdId);
            }
            $eqLogic = $cmd->getEqLogicId();
            if (!$eqLogic->hasRight('r')) {
                throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
            }
            $histories = $cmd->getHistory($dateStart, $dateEnd);
            $result['cmd_name'] = $cmd->getName();
            $result['history_name'] = $cmd->getHumanName();
            $result['unite'] = $cmd->getUnite();
            $result['cmd'] = Utils::o2a($cmd);
            $result['eqLogic'] = Utils::o2a($cmd->getEqLogicId());
            $result['timelineOnly'] = $NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['timelineOnly'];
            $previsousValue = null;
            $derive = Utils::init('derive', $cmd->getDisplay('graphDerive'));
            if (trim($derive) == '') {
                $derive = $cmd->getDisplay('graphDerive');
            }
            foreach ($histories as $history) {
                $infoHistory = [];
                if ($cmd->getDisplay('groupingType') != '') {
                    $infoHistory[] = floatval(strtotime($history->getDatetime() . " UTC")) * 1000 - 1;
                } else {
                    $infoHistory[] = floatval(strtotime($history->getDatetime() . " UTC")) * 1000;
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
                $infoHistory[] = $value;
                if (!$NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['timelineOnly']) {
                    if (($value !== null && $value > $result['maxValue']) || $result['maxValue'] == '') {
                        $result['maxValue'] = round($value, 1);
                    }
                    if (($value !== null && $value < $result['minValue']) || $result['minValue'] == '') {
                        $result['minValue'] = round($value, 1);
                    }
                }
                $data[] = $infoHistory;
            }
        } else {
            $histories = HistoryManager::getHistoryFromCalcul(NextDomHelper::fromHumanReadable($cmdId), $dateStart, $dateEnd, Utils::init('allowZero', false));
            if (is_array($histories)) {
                foreach ($histories as $datetime => $value) {
                    $infoHistory = [];
                    $infoHistory[] = floatval($datetime) * 1000;
                    $infoHistory[] = ($value === null) ? null : floatval($value);
                    if ($value > $result['maxValue'] || $result['maxValue'] == '') {
                        $result['maxValue'] = round($value, 1);
                    }
                    if ($value < $result['minValue'] || $result['minValue'] == '') {
                        $result['minValue'] = round($value, 1);
                    }
                    $data[] = $infoHistory;
                }
            }
            $result['cmd_name'] = $cmdId;
            $result['history_name'] = $cmdId;
            $result['unite'] = Utils::init('unite');
        }
        $result['data'] = $data;
        $this->ajax->success($result);
    }

    /**
     * Clear history
     *
     * @throws CoreException
     */
    public function emptyHistory()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $cmdId = Utils::init('id');
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande ID inconnu : ') . $cmdId);
        }
        $cmd->emptyHistory(Utils::init('date'));
        $this->ajax->success();
    }

    /**
     * Set command order
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function setOrder()
    {
        $cmds = json_decode(Utils::init('cmds'), true);
        $eqLogics = [];
        foreach ($cmds as $cmdJsonData) {
            if (!isset($cmdJsonData['id']) || trim($cmdJsonData['id']) == '') {
                continue;
            }
            $cmd = CmdManager::byId($cmdJsonData['id']);
            if (!is_object($cmd)) {
                continue;
            }
            if ($cmd->getOrder() != $cmdJsonData['order']) {
                $cmd->setOrder($cmdJsonData['order']);
                $cmd->save();
            }
            if (isset($cmdJsonData['line']) && isset($cmdJsonData['column'])) {
                $renderVersion = Utils::init('version', CmdViewType::DASHBOARD);
                if (!isset($eqLogics[$cmd->getEqLogic_id()])) {
                    $eqLogics[$cmd->getEqLogic_id()] = ['eqLogic' => $cmd->getEqLogic(), 'changed' => false];
                }
                $layoutDisplay = 'layout::' . $renderVersion . '::table::cmd::' . $cmd->getId();
                if ($eqLogics[$cmd->getEqLogic_id()]['eqLogic']->getDisplay($layoutDisplay . '::line') != $cmdJsonData['line']
                    || $eqLogics[$cmd->getEqLogic_id()]['eqLogic']->getDisplay($layoutDisplay . '::column') != $cmdJsonData['column']) {
                    $eqLogics[$cmd->getEqLogic_id()]['eqLogic']->setDisplay($layoutDisplay . '::line', $cmdJsonData['line']);
                    $eqLogics[$cmd->getEqLogic_id()]['eqLogic']->setDisplay($layoutDisplay . '::column', $cmdJsonData['column']);
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
        $this->ajax->success();
    }

    /**
     * Upload file from dashboard
     *
     * When file is uploaded, the method execute of the cmd is called with the filename in option.
     *
     * @throws CoreException
     */
    public function fileUpload()
    {
        AuthentificationHelper::isConnectedOrFail();

        $destDirectory = NEXTDOM_TMP . '/uploads';
        FileSystemHelper::mkdirIfNotExists($destDirectory);

        $filename = Utils::readUploadedFile($_FILES, "upload", $destDirectory, 8, []);
        if (!$filename) {
            $this->ajax->error(__('File error'));
        }

        $cmdId = Utils::init('cmdId');
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            $this->ajax->error(__('Command not found : ') . $cmdId);
        }

        $cmd->execute(['filename' => $destDirectory . '/' . $filename]);
        $this->ajax->success();
    }
}