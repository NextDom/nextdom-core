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
use NextDom\Enums\FoldersAndFilesReferential;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\ScenarioElementManager;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Model\Entity\Scenario;

/**
 * Class ScenarioAjax
 * @package NextDom\Ajax
 */
class ScenarioAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    /**
     * Change scenario state
     *
     * @throws CoreException
     */
    public function changeState()
    {
        $scenario = ScenarioManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario ID inconnu : ') . Utils::initInt(AjaxParams::ID));
        }
        if (!$scenario->hasRight('x')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        switch (Utils::init(AjaxParams::STATE)) {
            case 'start':
                if (!$scenario->getIsActive()) {
                    throw new CoreException(__('Impossible de lancer le scénario car il est désactivé. Veuillez l\'activer'));
                }
                $scenario->launch('user', 'Scénario lancé manuellement', 0);
                break;
            case 'stop':
                $scenario->stop();
                break;
            case 'deactivate':
                $scenario->setIsActive(0);
                $scenario->save();
                break;
            case 'activate':
                $scenario->setIsActive(1);
                $scenario->save();
                break;
        }
        $this->ajax->success();
    }

    public function listScenarioHtml()
    {
        $return = [];
        foreach (ScenarioManager::all() as $scenario) {
            if ($scenario->isVisible()) {
                $return[] = $scenario->toHtml(Utils::init(AjaxParams::VERSION));
            }
        }
        $this->ajax->success($return);
    }

    public function setOrder()
    {
        $scenarios = json_decode(Utils::init(AjaxParams::SCENARIOS), true);
        foreach ($scenarios as $scenario_json) {
            if (!isset($scenario_json[Common::ID]) || trim($scenario_json[Common::ID]) == '') {
                continue;
            }
            $scenario = ScenarioManager::byId($scenario_json[Common::ID]);
            if (!is_object($scenario)) {
                continue;
            }
            Utils::a2o($scenario, $scenario_json);
            $scenario->save();
        }
        $this->ajax->success();
    }

    public function testExpression()
    {
        $result = [];
        $scenario = null;
        $result['evaluate'] = ScenarioExpressionManager::setTags(NextDomHelper::fromHumanReadable(Utils::init(AjaxParams::EXPRESSION)), $scenario, true);
        $result['result'] = Utils::evaluate($result['evaluate']);
        $result['correct'] = 'ok';
        if (trim($result['result']) == trim($result['evaluate'])) {
            $result['correct'] = 'nok';
        }
        $this->ajax->success($result);
    }

    public function getTemplate()
    {
        $this->ajax->success(ScenarioManager::getTemplate());
    }

    /**
     * Convert current scenario to template
     * @throws CoreException
     */
    public function convertToTemplate()
    {
        $scenario = ScenarioManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario ID inconnu : ') . Utils::initInt(AjaxParams::ID));
        }
        $path = NEXTDOM_DATA . '/data/scenario';
        if (!file_exists($path)) {
            mkdir($path);
        }
        $template = trim(Utils::initFilename(AjaxParams::TEMPLATE));
        if ($template == '' || $template == '.json') {
            throw new CoreException(__('Le nom du template ne peut être vide '));
        }
        file_put_contents($path . '/' . $template, json_encode($scenario->export('array'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if (!file_exists($path . '/' . $template)) {
            throw new CoreException(__('Impossible de créer le template, vérifiez les droits : ') . $path . '/' . $template);
        }
        $this->ajax->success();
    }

    public function removeTemplate()
    {
        $templateFile = FoldersAndFilesReferential::SCENARIO_TEMPLATE_FORLDER . Utils::initFilename(AjaxParams::TEMPLATE);
        if (file_exists($templateFile)) {
            unlink($templateFile);
        }
        $this->ajax->success();
    }

    public function loadTemplateDiff()
    {
        $templateFile = FoldersAndFilesReferential::SCENARIO_TEMPLATE_FORLDER . Utils::initFilename(AjaxParams::TEMPLATE);
        if (!file_exists($templateFile)) {
            throw new CoreException(__('Fichier non trouvé : ') . $templateFile);
        }
        $result = [];
        foreach (preg_split("/((\r?\n)|(\r\n?))/", file_get_contents($templateFile)) as $line) {
            preg_match_all("/#\[(.*?)\]\[(.*?)\]\[(.*?)\]#/", $line, $matches, PREG_SET_ORDER);
            if (count($matches) > 0) {
                foreach ($matches as $match) {
                    $result[$match[0]] = '';
                    $cmd = null;
                    try {
                        $cmd = CmdManager::byString($match[0]);
                        if (is_object($cmd)) {
                            $result[$match[0]] = '#' . $cmd->getHumanName() . '#';
                        }
                    } catch (\Exception $e) {

                    }
                }
            }
            preg_match_all("/#\[(.*?)\]\[(.*?)\]#/", $line, $matches, PREG_SET_ORDER);
            if (count($matches) > 0) {
                foreach ($matches as $match) {
                    $result[$match[0]] = '';
                    try {
                        $eqLogic = EqLogicManager::byString($match[0]);
                        if (is_object($eqLogic)) {
                            $result[$match[0]] = '#' . $eqLogic->getHumanName() . '#';
                        }
                    } catch (\Exception $e) {

                    }
                }
            }
            preg_match_all("/variable\((.*?)\)/", $line, $matches, PREG_SET_ORDER);
            if (count($matches) > 0) {
                foreach ($matches as $match) {
                    $result[$match[1]] = $match[1];
                }
            }
        }
        $this->ajax->success($result);
    }

    public function applyTemplate()
    {
        $templateFile = FoldersAndFilesReferential::SCENARIO_TEMPLATE_FORLDER . Utils::initFilename(AjaxParams::TEMPLATE);
        if (!file_exists($templateFile)) {
            throw new CoreException(__('Fichier non trouvé : ') . $templateFile);
        }
        $converts = [];
        foreach (json_decode(Utils::init('convert'), true) as $value) {
            if (Utils::init('newValues') == '1') {
                if (trim($value['end']) == '') {
                    throw new CoreException(__('La conversion suivante ne peut pas être vide : ') . $value['begin']);
                }
                $converts[$value['begin']] = $value['end'];
            } else {
                $converts[$value['begin']] = $value['begin'];
            }
        }
        $content = str_replace(array_keys($converts), $converts, file_get_contents($templateFile));
        $scenario_ajax = json_decode($content, true);
        if (isset($scenario_ajax[Common::NAME])) {
            unset($scenario_ajax[Common::NAME]);
        }
        if (isset($scenario_ajax[Common::GROUP])) {
            unset($scenario_ajax[Common::GROUP]);
        }
        $scenario_db = ScenarioManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($scenario_db)) {
            throw new CoreException(__('Scénario ID inconnu : ') . Utils::initInt(AjaxParams::ID));
        }
        if (!$scenario_db->hasRight('w')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        $scenario_db->setTrigger([]);
        $scenario_db->setSchedule([]);
        Utils::a2o($scenario_db, $scenario_ajax);
        $scenario_db->save();
        $scenario_element_list = [];
        if (isset($scenario_ajax['elements'])) {
            foreach ($scenario_ajax['elements'] as $element_ajax) {
                $scenario_element_list[] = ScenarioElementManager::saveAjaxElement($element_ajax);
            }
            $scenario_db->setScenarioElement($scenario_element_list);
        }
        $scenario_db->save();
        $this->ajax->success();
    }

    public function all()
    {
        $scenarios = ScenarioManager::all();
        $result = [];
        foreach ($scenarios as $scenario) {
            $info_scenario = Utils::o2a($scenario);
            $info_scenario[Common::HUMAN_NAME] = $scenario->getHumanName();
            $result[] = $info_scenario;
        }
        $this->ajax->success($result);
    }

    public function saveAll()
    {
        $scenarios = json_decode(Utils::init(AjaxParams::SCENARIOS), true);
        if (is_array($scenarios)) {
            foreach ($scenarios as $scenario_ajax) {
                $scenario = ScenarioManager::byId($scenario_ajax[Common::ID]);
                if (!is_object($scenario)) {
                    continue;
                }
                Utils::a2o($scenario, $scenario_ajax);
                $scenario->save();
            }
        }
        $this->ajax->success();
    }

    public function autoCompleteGroup()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $result = [];
        foreach (ScenarioManager::listGroup(Utils::init('term')) as $group) {
            $result[] = $group[Common::GROUP];
        }
        $this->ajax->success($result);
    }

    public function toHtml()
    {
        $target = Utils::init(AjaxParams::ID);
        if ($target == Common::ALL || Utils::isJson($target)) {
            if (Utils::isJson($target)) {
                $scenario_ajax = json_decode($target, true);
                $scenarios = [];
                foreach ($scenario_ajax as $id) {
                    $scenarios[] = ScenarioManager::byId($id);
                }
            } else {
                $scenarios = ScenarioManager::all();
            }
            $result = [];
            foreach ($scenarios as $scenario) {
                $result[] = $scenario->toHtml(Utils::init(AjaxParams::VERSION));
            }
            $this->ajax->success($result);
        } else {
            $scenario = ScenarioManager::byId($target);
            if (is_object($scenario)) {
                $this->ajax->success($scenario->toHtml(Utils::init(AjaxParams::VERSION)));
            }
        }
        $this->ajax->success();
    }

    public function remove()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $scenario = ScenarioManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario ID inconnu'));
        }
        if (!$scenario->hasRight('w')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        $scenario->remove();
        $this->ajax->success();
    }

    public function emptyLog()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $scenario = ScenarioManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario ID inconnu'));
        }
        if (!$scenario->hasRight('w')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        if (file_exists(NEXTDOM_LOG . '/scenarioLog/scenario' . $scenario->getId() . '.log')) {
            unlink(NEXTDOM_LOG . '/scenarioLog/scenario' . $scenario->getId() . '.log');
        }
        $this->ajax->success();
    }

    public function copy()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $scenario = ScenarioManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario ID inconnu'));
        }
        $this->ajax->success(Utils::o2a($scenario->copy(Utils::init(Common::NAME))));
    }

    public function get()
    {
        $scenario = ScenarioManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario ID inconnu'));
        }
        $result = Utils::o2a($scenario);
        $result[Common::TRIGGER] = NextDomHelper::toHumanReadable($result[Common::TRIGGER]);
        $result['forecast'] = $scenario->calculateScheduleDate();
        $result['elements'] = [];
        foreach ($scenario->getElement() as $element) {
            $result['elements'][] = $element->getAjaxElement();
        }
        $result['scenarioLinkBy'] = [NextDomObj::SCENARIO => []];
    		$scenarioUsedBy = $scenario->getUsedBy();
    		foreach ($scenarioUsedBy[NextDomObj::SCENARIO] as $scenarioLink) {
      			if($scenarioLink->getId() == $scenario->getId()){
      				  continue;
      			}
    			  $result['scenarioLinkBy'][NextDomObj::SCENARIO][$scenarioLink->getId()] = [Common::ID => $scenarioLink->getId(),Common::NAME => $scenarioLink->getHumanName(),'isActive' => $scenarioLink->getIsActive(),'isVisible' => $scenarioLink->getIsVisible()];
    		}
        $result['scenarioLinkIn'] = [NextDomObj::SCENARIO => []];
    		$scenarioUse = $scenario->getUse();
    		foreach ($scenarioUse[NextDomObj::SCENARIO] as $scenarioLink) {
      			if($scenarioLink->getId() == $scenario->getId()){
      				  continue;
      			}
      			$result['scenarioLinkIn'][NextDomObj::SCENARIO][$scenarioLink->getId()] = [Common::ID => $scenarioLink->getId(),Common::NAME => $scenarioLink->getHumanName(),'isActive' => $scenarioLink->getIsActive(),'isVisible' => $scenarioLink->getIsVisible()];
    		}
        $this->ajax->success($result);
    }

    /**
     * Save scenario
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        if (!Utils::isJson(Utils::init(AjaxParams::SCENARIO))) {
            throw new CoreException(__('Champs json invalide'));
        }
        // Check if scenario has time dependency
        $timeDependency = 0;
        foreach (['#time#', '#seconde#', '#heure#', '#minute#', '#jour#', '#mois#', '#annee#', '#timestamp#', '#date#', '#semaine#', '#sjour#', '#njour#', '#smois#'] as $keyword) {
            if (strpos(Utils::init(AjaxParams::SCENARIO), $keyword) !== false) {
                $timeDependency = 1;
                break;
            }
        }

        // Check if scenario must return a value
        // Loop for futur cases ?
        $hasReturn = 0;
        foreach (['scenario_return'] as $keyword) {
            if (strpos(Utils::init(AjaxParams::SCENARIO), $keyword) !== false) {
                $hasReturn = 1;
                break;
            }
        }

        // Prepare object from Ajax data
        $scenarioAjaxData = json_decode(Utils::init(AjaxParams::SCENARIO), true);
        if (isset($scenarioAjaxData[Common::ID])) {
            $targetScenario = ScenarioManager::byId($scenarioAjaxData[Common::ID]);
        }
        if (!isset($targetScenario) || !is_object($targetScenario)) {
            $targetScenario = new Scenario();
        } elseif (!$targetScenario->hasRight('w')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        if (!isset($scenarioAjaxData[Common::TRIGGER])) {
            $targetScenario->setTrigger([]);
        }
        if (!isset($scenarioAjaxData['schedule'])) {
            $targetScenario->setSchedule([]);
        }
        Utils::a2o($targetScenario, $scenarioAjaxData);
        $targetScenario->setConfiguration('timeDependency', $timeDependency);
        $targetScenario->setConfiguration('has_return', $hasReturn);

        // Save scenario elements
        $scenarioElementList = [];
        if (isset($scenarioAjaxData['elements'])) {
            foreach ($scenarioAjaxData['elements'] as $elementData) {
                $scenarioElementList[] = ScenarioElementManager::saveAjaxElement($elementData);
            }
            $targetScenario->setScenarioElement($scenarioElementList);
        }

        $targetScenario->save();
        $this->ajax->success(Utils::o2a($targetScenario));
    }

    public function actionToHtml()
    {
        $result = null;
        if (Utils::init(AjaxParams::PARAMS) != '' && Utils::isJson(Utils::init(AjaxParams::PARAMS))) {
            $result = [];
            $params = json_decode(Utils::init(AjaxParams::PARAMS), true);
            foreach ($params as $param) {
                if (!isset($param['options'])) {
                    $param['options'] = [];
                }
                $html = ScenarioExpressionManager::getExpressionOptions($param['expression'], $param['options']);
                if (!isset($html['html']) || $html['html'] == '') {
                    continue;
                }
                $result[] = [
                    'html' => $html,
                    Common::ID => $param[Common::ID],
                ];
            }
        }
        if ($result !== null) {
            $this->ajax->success($result);
        } else {
            $this->ajax->success(ScenarioExpressionManager::getExpressionOptions(Utils::init(AjaxParams::EXPRESSION), Utils::init(AjaxParams::OPTION)));
        }
    }

    public function templateupload()
    {
        $uploadDir = FoldersAndFilesReferential::SCENARIO_TEMPLATE_FORLDER;
        Utils::readUploadedFile($_FILES, "file", $uploadDir, 10, [".json"]);
        $this->ajax->success();
    }
}
