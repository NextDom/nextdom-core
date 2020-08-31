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
use NextDom\Enums\CmdConfigKey;
use NextDom\Enums\CmdType;
use NextDom\Enums\CmdViewType;
use NextDom\Enums\Common;
use NextDom\Enums\DateFormat;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\HistoryManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\UserManager;
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
     */
    public function toHtml()
    {
        // Render list of id
        if (Utils::init(AjaxParams::IDS) != '') {
            $result = [];
            foreach (json_decode(Utils::init(AjaxParams::IDS), true) as $id => $value) {
                $cmd = CmdManager::byId($id);
                if (!is_object($cmd)) {
                    continue;
                }
                $result[$cmd->getId()] = [
                    'html' => $cmd->toHtml($value[Common::VERSION]),
                    AjaxParams::ID => $cmd->getId(),
                ];
            }
            $this->ajax->success($result);
        } else {
            // Render one command
            $cmd = CmdManager::byId(Utils::init(AjaxParams::ID));
            if (!is_object($cmd)) {
                throw new CoreException(__('Commande inconnue - Vérifiez l\'ID'));
            }
            $result = [];
            $result[AjaxParams::ID] = $cmd->getId();
            $result['html'] = $cmd->toHtml(Utils::init(AjaxParams::VERSION), Utils::init(AjaxParams::OPTION), Utils::init('cmdColor', null));
            $this->ajax->success($result);
        }
    }

    /**
     * Execute a command
     *
     * @throws CoreException
     */
    public function execCmd()
    {
        $cmdId = Utils::init(AjaxParams::ID);
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande ID inconnu : ') . $cmdId);
        }
        $eqLogic = $cmd->getEqLogicId();
        if ($cmd->isType(CmdType::ACTION) && !$eqLogic->hasRight('x')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        if (!$cmd->checkAccessCode(Utils::init('codeAccess'))) {
            throw new CoreException(__('Cette action nécessite un code d\'accès'), -32005);
        }
        if ($cmd->isType(CmdType::ACTION) && $cmd->getConfiguration('actionConfirm') == 1 && Utils::init('confirmAction') != 1) {
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
        $objectName = Utils::init(AjaxParams::OBJECT_NAME);
        $eqLogicName = Utils::init(AjaxParams::EQLOGIC_NAME);
        $cmdName = Utils::init(AjaxParams::CMD_NAME);
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
        $objectName = Utils::init(AjaxParams::OBJECT_NAME);
        $cmdName = Utils::init(AjaxParams::CMD_NAME);
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
        $cmdId = Utils::init(AjaxParams::ID);
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
        NextDomHelper::replaceTag(['#' . str_replace('#', '', Utils::init('source_id')) . '#' => '#' . str_replace('#', '', Utils::init('target_id')) . '#']);
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
        $humanName = Utils::init(AjaxParams::HUMAN_NAME);
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
        $cmdId = Utils::init(AjaxParams::ID);
        $cmd = CmdManager::byId($cmdId);
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande inconnue : ') . $cmdId, 9999);
        }
        $cmdUserBy = $cmd->getUsedBy();
        $result = [NextDomObj::CMD => [], NextDomObj::EQLOGIC => [], NextDomObj::SCENARIO => []];
        /**
         * @var Cmd $cmd
         */
        foreach ($cmdUserBy[NextDomObj::CMD] as $cmd) {
            $info = Utils::o2a($cmd);
            $info[Common::HUMAN_NAME] = $cmd->getHumanName();
            $info['link'] = $cmd->getEqLogic()->getLinkToConfiguration();
            $result[NextDomObj::CMD][] = $info;
        }
        /**
         * @var EqLogic $eqLogic
         */
        foreach ($cmdUserBy[NextDomObj::EQLOGIC] as $eqLogic) {
            $info = Utils::o2a($eqLogic);
            $info[Common::HUMAN_NAME] = $eqLogic->getHumanName();
            $info['link'] = $eqLogic->getLinkToConfiguration();
            $result[NextDomObj::EQLOGIC][] = $info;
        }
        /**
         * @var Scenario $scenario
         */
        foreach ($cmdUserBy[NextDomObj::SCENARIO] as $scenario) {
            $info = Utils::o2a($cmd);
            $info[Common::HUMAN_NAME] = $scenario->getHumanName();
            $info['link'] = $scenario->getLinkToConfiguration();
            $result[NextDomObj::SCENARIO][] = $info;
        }
        $this->ajax->success($result);
    }

    /**
     * Get command human name
     *
     * @throws \ReflectionException
     */
    public function getHumanCmdName()
    {
        $this->ajax->success(CmdManager::cmdToHumanReadable('#' . Utils::init(AjaxParams::ID) . '#'));
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
        $cmdId = Utils::init(AjaxParams::ID);
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
        $cmdAjaxData = NextDomHelper::fromHumanReadable(json_decode(Utils::init(NextDomObj::CMD), true));
        $cmd = CmdManager::byId($cmdAjaxData[AjaxParams::ID]);
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
        $cmds = json_decode(Utils::init(NextDomObj::CMD), true);
        foreach ($cmds as $cmdAjaxData) {
            $cmd = CmdManager::byId($cmdAjaxData[AjaxParams::ID]);
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
        foreach (['+1 hour', '+1 day', '+1 week', '+1 month'] as $timeStep) {
            if (is_object($history)) {
                break;
            }
            $history = HistoryManager::byCmdIdDatetime($cmdId, $targetDatetime, date(DateFormat::FULL, strtotime($targetDatetime . $timeStep)), $srcDatetime);
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
     * @throws \Exception
     */
    public function getHistory()
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        $result = [];
        $data = [];
        $userDateStart = Utils::init(AjaxParams::DATE_START, '');
        $userDateEnd = Utils::init(AjaxParams::DATE_END, '');
        $dateStart = null;
        $dateEnd = null;
        $dateRange = Utils::init(AjaxParams::DATE_RANGE);
        if ($dateRange != '' && $dateRange != Common::ALL) {
            if (Utils::isJson($dateRange)) {
                $dateRange = json_decode($dateRange, true);
                if (isset($dateRange[Common::START])) {
                    $dateStart = $dateRange[Common::START];
                }
                if (isset($dateRange[Common::END])) {
                    $dateEnd = $dateRange[Common::END];
                }
            } else {
                if (Utils::init(AjaxParams::DATE_RANGE) == '1 day') {
                    $dateStart = date(DateFormat::FULL_MIDNIGHT, strtotime('- 2 days ' . $dateEnd));
                } else {
                    $dateStart = date(DateFormat::FULL_MIDNIGHT, strtotime('- ' . Utils::init(AjaxParams::DATE_RANGE) . ' ' . $dateEnd));
                }
                $dateEnd = date(DateFormat::FULL);
            }
        }
        if ($userDateStart != '') {
            $dateStart = $userDateStart;
        }
        if ($userDateEnd != '') {
            $dateEnd = $userDateEnd;
            if ($dateEnd == date(DateFormat::FULL_DAY)) {
                $dateEnd = date(DateFormat::FULL);
            }
        }
        if (strtotime($dateEnd) > strtotime('now')) {
            $dateEnd = date(DateFormat::FULL);
        }
        if ($dateStart !== '') {
            $dateStart = Utils::init(AjaxParams::DATE_START, date(DateFormat::FULL_DAY, strtotime(ConfigManager::byKey('history::defautShowPeriod') . ' ' . date(DateFormat::FULL_DAY))));
        }
        $result[CmdConfigKey::MAX_VALUE] = '';
        $result[CmdConfigKey::MIN_VALUE] = '';
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

        $cmdId = Utils::init(AjaxParams::ID);
        if (is_numeric($cmdId)) {
            $cmd = CmdManager::byId($cmdId);
            if (!is_object($cmd)) {
                throw new CoreException(__('Commande ID inconnu : ') . $cmdId);
            }
            $eqLogic = $cmd->getEqLogicId();
            if (!$eqLogic->hasRight(ActionRight::READ)) {
                throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
            }
            $groupingType = Utils::init(AjaxParams::GROUPING_TYPE);
            if ($groupingType == '') {
                $groupingType = $cmd->getDisplay(AjaxParams::GROUPING_TYPE);
            }
            $histories = $cmd->getHistory($dateStart, $dateEnd, $groupingType);
            $result['cmd_name'] = $cmd->getName();
            $result['history_name'] = $cmd->getHumanName();
            $result[Common::UNITE] = $cmd->getUnite();
            $result[NextDomObj::CMD] = Utils::o2a($cmd);
            $result[NextDomObj::EQLOGIC] = Utils::o2a($cmd->getEqLogicId());
            $result[Common::TIMELINE_ONLY] = $NEXTDOM_INTERNAL_CONFIG[NextDomObj::CMD][Common::TYPE][Common::INFO]['subtype'][$cmd->getSubType()][Common::IS_HISTORIZED][Common::TIMELINE_ONLY];
            $previousValue = null;
            // Todo Optimisation possible
            $derive = intval(Utils::init('derive', $cmd->getDisplay('graphDerive')));
            if (trim($derive) == '') {
                $derive = intval($cmd->getDisplay('graphDerive'));
            }
            $result['derive'] = $derive;
            foreach ($histories as $history) {
                $infoHistory = [];
                if ($cmd->getDisplay(AjaxParams::GROUPING_TYPE) != '') {
                    $infoHistory[] = floatval(strtotime($history->getDatetime() . " UTC")) * 1000 - 1;
                } else {
                    $infoHistory[] = floatval(strtotime($history->getDatetime() . " UTC")) * 1000;
                }
                if ($NEXTDOM_INTERNAL_CONFIG[NextDomObj::CMD][Common::TYPE][Common::INFO][Common::SUBTYPE][$cmd->getSubType()][Common::IS_HISTORIZED][Common::TIMELINE_ONLY]) {
                    $value = $history->getValue();
                } else {
                    $value = ($history->getValue() === null) ? null : floatval($history->getValue());
                    if ($derive === 1) {
                        if ($value !== null && $previousValue !== null) {
                            $value = $value - $previousValue;
                        } else {
                            $value = null;
                        }
                        $previousValue = ($history->getValue() === null) ? null : floatval($history->getValue());
                    }
                }
                $infoHistory[] = $value;
                if (!$NEXTDOM_INTERNAL_CONFIG[NextDomObj::CMD][Common::TYPE][Common::INFO][Common::SUBTYPE][$cmd->getSubType()][Common::IS_HISTORIZED][Common::TIMELINE_ONLY]) {
                    if (($value !== null && $value > $result[CmdConfigKey::MAX_VALUE]) || $result[CmdConfigKey::MAX_VALUE] == '') {
                        $result[CmdConfigKey::MAX_VALUE] = round($value, 1);
                    }
                    if (($value !== null && $value < $result[CmdConfigKey::MIN_VALUE]) || $result[CmdConfigKey::MIN_VALUE] == '') {
                        $result[CmdConfigKey::MIN_VALUE] = round($value, 1);
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
                    if ($value > $result[CmdConfigKey::MAX_VALUE] || $result[CmdConfigKey::MAX_VALUE] == '') {
                        $result[CmdConfigKey::MAX_VALUE] = round($value, 1);
                    }
                    if ($value < $result[CmdConfigKey::MIN_VALUE] || $result[CmdConfigKey::MIN_VALUE] == '') {
                        $result[CmdConfigKey::MIN_VALUE] = round($value, 1);
                    }
                    $data[] = $infoHistory;
                }
            }
            $result['cmd_name'] = $cmdId;
            $result['history_name'] = $cmdId;
            $result[Common::UNITE] = Utils::init(Common::UNITE);
        }
        $result['data'] = $data;
        $this->ajax->success($result);
    }

    /**
     * Clear history
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function emptyHistory()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $cmdId = Utils::init(AjaxParams::ID);
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
            if (!isset($cmdJsonData[AjaxParams::ID]) || trim($cmdJsonData[AjaxParams::ID]) == '') {
                continue;
            }
            $cmd = CmdManager::byId($cmdJsonData[AjaxParams::ID]);
            if (!is_object($cmd)) {
                continue;
            }
            if ($cmd->getOrder() != $cmdJsonData['order']) {
                $cmd->setOrder($cmdJsonData['order']);
                $cmd->save();
            }
            if (isset($cmdJsonData['line']) && isset($cmdJsonData[Common::COLUMN])) {
                $renderVersion = Utils::init(AjaxParams::VERSION, CmdViewType::DASHBOARD);
                if (!isset($eqLogics[$cmd->getEqLogic_id()])) {
                    $eqLogics[$cmd->getEqLogic_id()] = [NextDomObj::EQLOGIC => $cmd->getEqLogic(), Common::CHANGED => false];
                }
                $layoutDisplay = 'layout::' . $renderVersion . '::table::cmd::' . $cmd->getId();
                if ($eqLogics[$cmd->getEqLogic_id()][NextDomObj::EQLOGIC]->getDisplay($layoutDisplay . '::line') != $cmdJsonData['line']
                    || $eqLogics[$cmd->getEqLogic_id()][NextDomObj::EQLOGIC]->getDisplay($layoutDisplay . '::column') != $cmdJsonData[Common::COLUMN]) {
                    $eqLogics[$cmd->getEqLogic_id()][NextDomObj::EQLOGIC]->setDisplay($layoutDisplay . '::line', $cmdJsonData['line']);
                    $eqLogics[$cmd->getEqLogic_id()][NextDomObj::EQLOGIC]->setDisplay($layoutDisplay . '::column', $cmdJsonData[Common::COLUMN]);
                    $eqLogics[$cmd->getEqLogic_id()][Common::CHANGED] = true;
                }
            }
        }
        /**
         * @var EqLogic[] $eqLogic
         */
        foreach ($eqLogics as $eqLogic) {
            if (!$eqLogic[Common::CHANGED]) {
                continue;
            }
            $eqLogic[NextDomObj::EQLOGIC]->save(true);
        }
        $this->ajax->success();
    }

    /**
     * Upload file from dashboard
     *
     * When file is uploaded, the method execute of the cmd is called with the filename in option.
     *
     * @throws CoreException
     * @throws \ReflectionException
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

    /**
     * Change command visibility of multiple commands
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function setIsVisibles()
    {
        $cmdIds = json_decode(Utils::init('cmds'), true);
        foreach ($cmdIds as $cmdId) {
            $cmd = CmdManager::byId($cmdId);
            if (!is_object($cmd)) {
                throw new CoreException(__('Cmd inconnu. Vérifiez l\'ID') . ' ' . $cmdId);
            }
            $cmd->setIsVisible(Utils::init('isVisible'));
            $cmd->save(true);
        }
        $this->ajax->success();
    }

    public function getDeadCmd()
    {
        $result = [
            Common::CORE => [NextDomObj::CMD => NextDomHelper::getDeadCmd(), 'name' => __('Jeedom')],
            NextDomObj::CMD => [NextDomObj::CMD => CmdManager::deadCmd(), 'name' => __('Commande')],
            NextDomObj::JEE_OBJECT => [NextDomObj::CMD => JeeObjectManager::deadCmd(), 'name' => __('Objet')],
            NextDomObj::SCENARIO => [NextDomObj::CMD => ScenarioManager::consystencyCheck(true), 'name' => __('Scénario')],
            NextDomObj::INTERACT_DEF => [NextDomObj::CMD => InteractDefManager::deadCmd(), 'name' => __('Intéraction')],
            NextDomObj::USER => [NextDomObj::CMD => UserManager::deadCmd(), 'name' => __('Utilisateur')],
        ];
        foreach (PluginManager::listPlugin(true) as $plugin) {
            $plugin_id = $plugin->getId();
            $result[$plugin_id] = [NextDomObj::CMD => [], 'name' => 'Plugin ' . $plugin->getName()];
            if (method_exists($plugin_id, 'deadCmd')) {
                $result[$plugin_id][NextDomObj::CMD] = $plugin_id::deadCmd();
            } else {
                $result[$plugin_id][NextDomObj::CMD] = EqLogicManager::deadCmdGeneric($plugin_id);
            }
        }
        $this->ajax->success($result);
    }
}
