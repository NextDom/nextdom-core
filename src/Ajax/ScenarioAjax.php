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

    public function changeState()
    {
        $scenario = ScenarioManager::byId(Utils::init('id'));
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario ID inconnu : ') . Utils::init('id'));
        }
        if (!$scenario->hasRight('x')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        switch (Utils::init('state')) {
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
        AjaxHelper::success();
    }

    public function listScenarioHtml()
    {
        $return = array();
        foreach (ScenarioManager::all() as $scenario) {
            if ($scenario->getIsVisible() == 1) {
                $return[] = $scenario->toHtml(Utils::init('version'));
            }
        }
        AjaxHelper::success($return);
    }

    public function setOrder()
    {
        $scenarios = json_decode(Utils::init('scenarios'), true);
        foreach ($scenarios as $scenario_json) {
            if (!isset($scenario_json['id']) || trim($scenario_json['id']) == '') {
                continue;
            }
            $scenario = ScenarioManager::byId($scenario_json['id']);
            if (!is_object($scenario)) {
                continue;
            }
            Utils::a2o($scenario, $scenario_json);
            $scenario->save();
        }
        AjaxHelper::success();
    }

    public function testExpression()
    {
        $return = array();
        $scenario = null;
        $return['evaluate'] = ScenarioExpressionManager::setTags(NextDomHelper::fromHumanReadable(Utils::init('expression')), $scenario, true);
        $return['result'] = evaluate($return['evaluate']);
        $return['correct'] = 'ok';
        if (trim($return['result']) == trim($return['evaluate'])) {
            $return['correct'] = 'nok';
        }
        AjaxHelper::success($return);
    }

    public function getTemplate()
    {
        AjaxHelper::success(ScenarioManager::getTemplate());
    }

    public function convertToTemplate()
    {
        $scenario = ScenarioManager::byId(Utils::init('id'));
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario ID inconnu : ') . Utils::init('id'));
        }
        $path = __DIR__ . '/../config/scenario';
        if (!file_exists($path)) {
            mkdir($path);
        }
        if (trim(Utils::init('template')) == '' || trim(Utils::init('template')) == '.json') {
            throw new CoreException(__('Le nom du template ne peut être vide '));
        }
        $name = Utils::init('template');
        file_put_contents($path . '/' . $name, json_encode($scenario->export('array'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if (!file_exists($path . '/' . $name)) {
            throw new CoreException(__('Impossible de créer le template, vérifiez les droits : ') . $path . '/' . $name);
        }
        AjaxHelper::success();
    }

    public function removeTemplate()
    {
        $path = __DIR__ . '/../config/scenario';
        if (file_exists($path . '/' . Utils::init('template'))) {
            unlink($path . '/' . Utils::init('template'));
        }
        AjaxHelper::success();
    }

    public function loadTemplateDiff()
    {
        $path = NEXTDOM_DATA . '/config/scenario';
        if (!file_exists($path . '/' . Utils::init('template'))) {
            throw new CoreException('Fichier non trouvé : ' . $path . '/' . Utils::init('template'));
        }
        $return = array();
        foreach (preg_split("/((\r?\n)|(\r\n?))/", file_get_contents($path . '/' . Utils::init('template'))) as $line) {
            preg_match_all("/#\[(.*?)\]\[(.*?)\]\[(.*?)\]#/", $line, $matches, PREG_SET_ORDER);
            if (count($matches) > 0) {
                foreach ($matches as $match) {
                    $return[$match[0]] = '';
                    $cmd = null;
                    try {
                        $cmd = CmdManager::byString($match[0]);
                        if (is_object($cmd)) {
                            $return[$match[0]] = '#' . $cmd->getHumanName() . '#';
                        }
                    } catch (\Exception $e) {

                    }
                }
            }
            preg_match_all("/#\[(.*?)\]\[(.*?)\]#/", $line, $matches, PREG_SET_ORDER);
            if (count($matches) > 0) {
                foreach ($matches as $match) {
                    $return[$match[0]] = '';
                    try {
                        $eqLogic = EqLogicManager::byString($match[0]);
                        if (is_object($cmd)) {
                            $return[$match[0]] = '#' . $eqLogic->getHumanName() . '#';
                        }
                    } catch (\Exception $e) {

                    }
                }
            }
            preg_match_all("/variable\((.*?)\)/", $line, $matches, PREG_SET_ORDER);
            if (count($matches) > 0) {
                foreach ($matches as $match) {
                    $return[$match[1]] = $match[1];
                }
            }
        }
        AjaxHelper::success($return);
    }

    public function applyTemplate()
    {
        $path = NEXTDOM_DATA . '/config/scenario';
        if (!file_exists($path . '/' . Utils::init('template'))) {
            throw new CoreException('Fichier non trouvé : ' . $path . '/' . Utils::init('template'));
        }
        foreach (json_decode(Utils::init('convert'), true) as $value) {
            if (trim($value['end']) == '') {
                throw new CoreException(__('La conversion suivante ne peut être vide : ') . $value['begin']);
            }
            $converts[$value['begin']] = $value['end'];
        }
        $content = str_replace(array_keys($converts), $converts, file_get_contents($path . '/' . Utils::init('template')));
        $scenario_ajax = json_decode($content, true);
        if (isset($scenario_ajax['name'])) {
            unset($scenario_ajax['name']);
        }
        if (isset($scenario_ajax['group'])) {
            unset($scenario_ajax['group']);
        }
        $scenario_db = ScenarioManager::byId(Utils::init('id'));
        if (!is_object($scenario_db)) {
            throw new CoreException(__('Scénario ID inconnu : ') . Utils::init('id'));
        }
        if (!$scenario_db->hasRight('w')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        $scenario_db->setTrigger(array());
        $scenario_db->setSchedule(array());
        Utils::a2o($scenario_db, $scenario_ajax);
        $scenario_db->save();
        $scenario_element_list = array();
        if (isset($scenario_ajax['elements'])) {
            foreach ($scenario_ajax['elements'] as $element_ajax) {
                $scenario_element_list[] = ScenarioElementManager::saveAjaxElement($element_ajax);
            }
            $scenario_db->setScenarioElement($scenario_element_list);
        }
        $scenario_db->save();
        AjaxHelper::success();
    }

    public function all()
    {
        $scenarios = ScenarioManager::all();
        $return = array();
        foreach ($scenarios as $scenario) {
            $info_scenario = Utils::o2a($scenario);
            $info_scenario['humanName'] = $scenario->getHumanName();
            $return[] = $info_scenario;
        }
        AjaxHelper::success($return);
    }

    public function saveAll()
    {
        $scenarios = json_decode(Utils::init('scenarios'), true);
        if (is_array($scenarios)) {
            foreach ($scenarios as $scenario_ajax) {
                $scenario = ScenarioManager::byId($scenario_ajax['id']);
                if (!is_object($scenario)) {
                    continue;
                }
                Utils::a2o($scenario, $scenario_ajax);
                $scenario->save();
            }
        }
        AjaxHelper::success();
    }

    public function autoCompleteGroup()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $return = array();
        foreach (ScenarioManager::listGroup(Utils::init('term')) as $group) {
            $return[] = $group['group'];
        }
        AjaxHelper::success($return);
    }

    public function toHtml()
    {
        if (Utils::init('id') == 'all' || is_json(Utils::init('id'))) {
            if (is_json(Utils::init('id'))) {
                $scenario_ajax = json_decode(Utils::init('id'), true);
                $scenarios = array();
                foreach ($scenario_ajax as $id) {
                    $scenarios[] = ScenarioManager::byId($id);
                }
            } else {
                $scenarios = ScenarioManager::all();
            }
            $return = array();
            foreach ($scenarios as $scenario) {
                $return[] = $scenario->toHtml(Utils::init('version'));
            }
            AjaxHelper::success($return);
        } else {
            $scenario = ScenarioManager::byId(Utils::init('id'));
            if (is_object($scenario)) {
                AjaxHelper::success($scenario->toHtml(Utils::init('version')));
            }
        }
        AjaxHelper::success();
    }

    public function remove()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $scenario = ScenarioManager::byId(Utils::init('id'));
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario ID inconnu'));
        }
        if (!$scenario->hasRight('w')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        $scenario->remove();
        AjaxHelper::success();
    }

    public function emptyLog()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $scenario = ScenarioManager::byId(Utils::init('id'));
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario ID inconnu'));
        }
        if (!$scenario->hasRight('w')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        if (file_exists(NEXTDOM_LOG . '/scenarioLog/scenario' . $scenario->getId() . '.log')) {
            unlink(NEXTDOM_LOG . '/scenarioLog/scenario' . $scenario->getId() . '.log');
        }
        AjaxHelper::success();
    }

    public function copy()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $scenario = ScenarioManager::byId(Utils::init('id'));
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario ID inconnu'));
        }
        AjaxHelper::success(Utils::o2a($scenario->copy(Utils::init('name'))));
    }

    public function get()
    {
        $scenario = ScenarioManager::byId(Utils::init('id'));
        if (!is_object($scenario)) {
            throw new CoreException(__('Scénario ID inconnu'));
        }
        $return = Utils::o2a($scenario);
        $return['trigger'] = NextDomHelper::toHumanReadable($return['trigger']);
        $return['forecast'] = $scenario->calculateScheduleDate();
        $return['elements'] = array();
        foreach ($scenario->getElement() as $element) {
            $return['elements'][] = $element->getAjaxElement();
        }

        AjaxHelper::success($return);
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
        if (!is_json(Utils::init('scenario'))) {
            throw new CoreException(__('Champs json invalide'));
        }
        // Check if scenario has time dependency
        $timeDependency = 0;
        foreach (array('#time#', '#seconde#', '#heure#', '#minute#', '#jour#', '#mois#', '#annee#', '#timestamp#', '#date#', '#semaine#', '#sjour#', '#njour#', '#smois#') as $keyword) {
            if (strpos(Utils::init('scenario'), $keyword) !== false) {
                $timeDependency = 1;
                break;
            }
        }

        // Check if scenario must return a value
        // Loop for futur cases ?
        $hasReturn = 0;
        foreach (['scenario_return'] as $keyword) {
            if (strpos(Utils::init('scenario'), $keyword) !== false) {
                $hasReturn = 1;
                break;
            }
        }

        // Prepare object from Ajax data
        $scenarioAjaxData = json_decode(Utils::init('scenario'), true);
        if (isset($scenarioAjaxData['id'])) {
            $targetScenario = ScenarioManager::byId($scenarioAjaxData['id']);
        }
        if (!isset($targetScenario) || !is_object($targetScenario)) {
            $targetScenario = new Scenario();
        } elseif (!$targetScenario->hasRight('w')) {
            throw new CoreException(__('Vous n\'êtes pas autorisé à faire cette action'));
        }
        if (!isset($scenarioAjaxData['trigger'])) {
            $targetScenario->setTrigger([]);
        }
        if (!isset($scenarioAjaxData['schedule'])) {
            $targetScenario->setSchedule([]);
        }
        Utils::a2o($targetScenario, $scenarioAjaxData);
        $targetScenario->setConfiguration('timeDependency', $timeDependency);
        $targetScenario->setConfiguration('has_return', $hasReturn);

        // Save scenario elements
        $scenarioElementList = array();
        if (isset($scenarioAjaxData['elements'])) {
            foreach ($scenarioAjaxData['elements'] as $elementData) {
                $scenarioElementList[] = ScenarioElementManager::saveAjaxElement($elementData);
            }
            $targetScenario->setScenarioElement($scenarioElementList);
        }
        
        $targetScenario->save();
        AjaxHelper::success(Utils::o2a($targetScenario));
    }

    public function actionToHtml()
    {
        if (Utils::init('params') != '' && is_json(Utils::init('params'))) {
            $return = array();
            $params = json_decode(Utils::init('params'), true);
            foreach ($params as $param) {
                if (!isset($param['options'])) {
                    $param['options'] = array();
                }
                $html = ScenarioExpressionManager::getExpressionOptions($param['expression'], $param['options']);
                if (!isset($html['html']) || $html['html'] == '') {
                    continue;
                }
                $return[] = array(
                    'html' => $html,
                    'id' => $param['id'],
                );
            }
            AjaxHelper::success($return);
        }
        AjaxHelper::success(ScenarioExpressionManager::getExpressionOptions(Utils::init('expression'), Utils::init('option')));
    }

    public function templateupload()
    {
        $uploadDir = sprintf("%s/config/scenario/", NEXTDOM_DATA);
        Utils::readUploadedFile($_FILES, "file", $uploadDir, 10, array(".json"));
        AjaxHelper::success();
    }
}
