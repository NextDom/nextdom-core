<?php
/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Model\Entity;

use NextDom\Enums\CmdSubType;
use NextDom\Enums\DateFormat;
use NextDom\Enums\ScenarioExpressionAction;
use NextDom\Enums\ScenarioExpressionType;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\ReportHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\CronManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\EventManager;
use NextDom\Managers\MessageManager;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ScenarioElementManager;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\ScenarioSubElementManager;
use NextDom\Managers\ViewManager;

/**
 * Scenarioexpression
 *
 * @ORM\Table(name="scenarioExpression", indexes={@ORM\Index(name="fk_scenarioExpression_scenarioSubElement1_idx", columns={"scenarioSubElement_id"})})
 * @ORM\Entity
 */
class ScenarioExpression implements EntityInterface
{

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=true)
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=true)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="subtype", type="string", length=127, nullable=true)
     */
    protected $subtype;

    /**
     * @var string
     *
     * @ORM\Column(name="expression", type="text", length=65535, nullable=true)
     */
    protected $expression;

    /**
     * @var string
     *
     * @ORM\Column(name="options", type="text", length=65535, nullable=true)
     */
    protected $options;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var integer
     *
     * ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Scenariosubelement")
     * ORM\JoinColumns({
     *   ORM\JoinColumn(name="scenarioSubElement_id", referencedColumnName="id")
     * })
     */
    protected $scenarioSubElement_id;
    protected $_changed = false;

    /**
     * Execute a scenario
     *
     * @param scenario|null $scenario Scenario to execute
     *
     * @return mixed|null|string
     * @throws \Exception
     */
    public function execute(&$scenario = null)
    {
        if ($scenario !== null && !$scenario->getDo()) {
            return null;
        }
        if ($this->getOptions('enable', 1) == 0) {
            return null;
        }
        $this->checkBackground();
        if ($this->getOptions('background', 0) == 1) {
            $key = 'scenarioElement' . ConfigManager::genKey(10);
            while (CacheManager::exists($key)) {
                $key = 'scenarioElement' . ConfigManager::genKey(10);
            }
            CacheManager::set($key, ['scenarioExpression' => $this, 'scenario' => $scenario], 60);
            $cmd = NEXTDOM_ROOT . '/src/Api/start_scenario_expr.php';
            $cmd .= ' key=' . $key;
            $this->setLog($scenario, __('Execution du lancement en arriere plan : ') . $key);
            SystemHelper::php($cmd . ' >> /dev/null 2>&1 &');
            return null;
        }
        $message = '';
        try {
            if ($this->getType() == ScenarioExpressionType::ELEMENT) {
                $element = ScenarioElementManager::byId($this->getExpression());
                if (is_object($element)) {
                    $this->setLog($scenario, __('Exécution d\'un bloc élément : ') . $this->getExpression());
                    return $element->execute($scenario);
                }
                return null;
            }
            $options = $this->getOptions();
            if (isset($options['enable'])) {
                unset($options['enable']);
            }
            if (is_array($options) && $this->getExpression() != ScenarioExpressionAction::WAIT) {
                foreach ($options as $key => $value) {
                    if ($this->getExpression() == ScenarioExpressionAction::EVENT && $key == ScenarioExpressionAction::CMD) {
                        continue;
                    }
                    if (is_string($value)) {
                        $options[$key] = str_replace('"', '', ScenarioExpressionManager::setTags($value, $scenario));
                    }
                }
            }
            if ($this->getType() == ScenarioExpressionType::ACTION) {
                $this->executeAction($scenario, $options);
            } elseif ($this->getType() == ScenarioExpressionType::CONDITION) {
                $expression = ScenarioExpressionManager::setTags($this->getExpression(), $scenario, true);
                $message = __('Evaluation de la condition : [') . $expression . '] = ';
                $result = Utils::evaluate($expression);
                if (is_bool($result)) {
                    if ($result) {
                        $message .= __('Vrai');
                    } else {
                        $message .= __('Faux');
                    }
                } else {
                    $message .= $result;
                }
                $this->setLog($scenario, $message);
                return $result;
            } elseif ($this->getType() == ScenarioExpressionType::CODE) {
                $this->setLog($scenario, __('Exécution d\'un bloc code'));
                return eval($this->getExpression());
            }
        } catch (\Exception $e) {
            $this->setLog($scenario, $message . $e->getMessage());
        }
        return null;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getOptions($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->options, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     * @throws \Exception
     */
    public function setOptions($_key, $_value)
    {
        $options = Utils::setJsonAttr($this->options, $_key, NextDomHelper::fromHumanReadable($_value));
        $this->_changed = Utils::attrChanged($this->_changed, $this->options, $options);
        $this->options = $options;
        return $this;
    }

    public function checkBackground()
    {
        if ($this->getOptions('background', 0) == 0) {
            return;
        }
        if (in_array($this->getExpression(), [ScenarioExpressionAction::WAIT, ScenarioExpressionAction::SLEEP, ScenarioExpressionAction::STOP, ScenarioExpressionAction::SCENARIO_RETURN])) {
            $this->setOptions('background', 0);
        }
        return;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param $_expression
     * @return $this
     * @throws \Exception
     */
    public function setExpression($_expression)
    {
        $_expression = NextDomHelper::fromHumanReadable($_expression);
        $this->_changed = Utils::attrChanged($this->_changed, $this->expression, $_expression);
        $this->expression = $_expression;
        return $this;
    }

    /**
     * @param Scenario $_scenario
     * @param $log
     */
    public function setLog(&$_scenario, $log)
    {
        if ($_scenario !== null && is_object($_scenario)) {
            $_scenario->setLog($log);
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $_type
     * @return $this
     */
    public function setType($_type)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->type, $_type);
        $this->type = $_type;
        return $this;
    }

    /**
     * @param $scenario
     * @param $options
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected function executeAction(&$scenario, $options)
    {
        switch ($this->getExpression()) {
            case ScenarioExpressionAction::ICON:
                $this->executeActionIcon($scenario);
                break;
            case ScenarioExpressionAction::WAIT:
                $this->executeActionWait($scenario, $options);
                break;
            case ScenarioExpressionAction::SLEEP:
                $this->executeActionSleep($scenario, $options);
                break;
            case ScenarioExpressionAction::STOP:
                $this->executeActionStop($scenario);
                break;
            case ScenarioExpressionAction::LOG:
                $this->executeActionLog($scenario, $options);
                break;
            case ScenarioExpressionAction::EVENT:
                $this->executeActionEvent($scenario, $options);
                break;
            case ScenarioExpressionAction::MESSAGE:
                $this->executeActionMessage($scenario, $options);
                break;
            case ScenarioExpressionAction::ALERT:
                $this->executeActionAlert($scenario, $options);
                break;
            case ScenarioExpressionAction::POPUP:
                $this->executeActionPopup($scenario, $options);
                break;
            case ScenarioExpressionAction::EQUIPMENT:
            case ScenarioExpressionAction::EQUIPEMENT:
                $this->executeActionEquipment($scenario);
                break;
            case ScenarioExpressionAction::GOTODESIGN:
                $this->executeActionGotoDesign($scenario, $options);
                break;
            case ScenarioExpressionAction::SCENARIO:
                $this->executeActionScenario($scenario);
                break;
            case ScenarioExpressionAction::VARIABLE:
                $this->executeActionVariable($scenario, $options);
                break;
            case ScenarioExpressionAction::DELETE_VARIABLE:
                $this->executeActionDeleteVariable($scenario, $options);
                break;
            case ScenarioExpressionAction::ASK:
                $this->executeActionAsk($scenario, $options);
                break;
            case ScenarioExpressionAction::NEXTDOM_POWEROFF:
                $this->executeActionNextDomPowerOff($scenario);
                break;
            case ScenarioExpressionAction::NEXTDOM_REBOOT:
                $this->executeActionNextDomReboot($scenario);
                break;
            case ScenarioExpressionAction::SCENARIO_RETURN:
                $this->executeActionScenarioReturn($scenario, $options);
                break;
            case ScenarioExpressionAction::REMOVE_INAT:
                $this->executeActionRemoveInat($scenario);
                break;
            case ScenarioExpressionAction::REPORT:
                $this->executeActionReport($scenario, $options);
                break;
            case ScenarioExpressionAction::TAG:
                $this->executeActionTag($scenario, $options);
                break;
            default:
                $this->executeActionOthers($scenario, $options);
                break;
        }
    }

    /**
     * @param Scenario $scenario
     * @throws \Exception
     */
    protected function executeActionIcon(&$scenario)
    {
        if ($scenario !== null) {
            $options = $this->getOptions();
            $this->setLog($scenario, __('Changement de l\'icone du scénario : ') . $options['icon']);
            $scenario->setDisplay('icon', $options['icon']);
            $scenario->save();
        }
    }

    /**
     * @param $scenario
     * @param $options
     * @return null
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected function executeActionWait(&$scenario, $options)
    {
        if (!isset($options['condition'])) {
            return null;
        }
        $result = false;
        $occurence = 0;
        $limit = 7200;
        if (isset($options['timeout'])) {
            $timeout = NextDomHelper::evaluateExpression($options['timeout']);
            $limit = (is_numeric($timeout)) ? $timeout : 7200;
        }
        $expression = '';
        while (!$result) {
            $expression = ScenarioExpressionManager::setTags($options['condition'], $scenario, true);
            $result = Utils::evaluate($expression);
            if ($occurence > $limit) {
                $this->setLog($scenario, __('[Wait] Condition valide par dépassement de temps : ') . $expression . ' => ' . $result);
                return null;
            }
            $occurence++;
            sleep(1);
        }
        $this->setLog($scenario, __('[Wait] Condition valide : ') . $expression . ' => ' . $result);
        return null;
    }

    /**
     * @param $scenario
     * @param $options
     * @return int|null|void
     * @throws \Exception
     */
    protected function executeActionSleep(&$scenario, $options)
    {
        if (isset($options['duration'])) {
            try {
                $options['duration'] = floatval(Utils::evaluate($options['duration']));
            } catch (\Exception $e) {
                $this->setLog($scenario, __('La durée n\'est pas valide : ') . $options['duration']);
            }
            if (is_numeric($options['duration']) && $options['duration'] > 0) {
                $this->setLog($scenario, __('Pause de ') . $options['duration'] . __(' seconde(s)'));
                if ($options['duration'] < 1) {
                    usleep($options['duration'] * 1000000);
                } else {
                    sleep($options['duration']);
                }
            }
        } else {
            $this->setLog($scenario, __('Aucune durée trouvée pour l\'action sleep : ') . $options['duration']);
        }
    }

    /**
     * @param Scenario $scenario
     * @return null
     * @throws \Exception
     */
    protected function executeActionStop(&$scenario)
    {
        if ($scenario !== null) {
            $this->setLog($scenario, __('Action stop'));
            $scenario->setDo(false);
            return null;
        }
        die();
    }

    /**
     * @param Scenario $scenario
     * @param $options
     */
    protected function executeActionLog(&$scenario, $options)
    {
        if ($scenario !== null) {
            $scenario->setLog('Log : ' . $options['message']);
        }
    }

    /**
     * @param Scenario $scenario
     * @param $options
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected function executeActionEvent(&$scenario, $options)
    {
        $cmd = CmdManager::byId(trim(str_replace('#', '', $options['cmd'])));
        if (!is_object($cmd)) {
            $this->setLog($scenario, __('Changement de ') . $cmd->getHumanName() . __(' à ') . $options['value']);
            throw new CoreException(__('Commande introuvable : ') . $options['cmd']);
        }
        $cmd->event(NextDomHelper::evaluateExpression($options['value']));
    }

    /**
     * @param $scenario
     * @param $options
     * @throws \Exception
     */
    protected function executeActionMessage(&$scenario, $options)
    {
        MessageManager::add('scenario', $options['message']);
        $this->setLog($scenario, __('Ajout du message suivant dans le centre de message : ') . $options['message']);
    }

    /**
     * @param $scenario
     * @param $options
     * @throws \Exception
     */
    protected function executeActionAlert(&$scenario, $options)
    {
        EventManager::add('nextdom::alert', $options);
        $this->setLog($scenario, __('Ajout de l\'alerte : ') . $options['message']);
    }

    /**
     * @param $scenario
     * @param $options
     * @throws \Exception
     */
    protected function executeActionPopup(&$scenario, $options)
    {
        EventManager::add('nextdom::alertPopup', $options['message']);
        $this->setLog($scenario, __('Affichage du popup : ') . $options['message']);
    }

    /**
     * @param $scenario
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected function executeActionEquipment(&$scenario)
    {
        $eqLogic = EqLogicManager::byId(str_replace(['#eqLogic', '#'], '', $this->getOptions('eqLogic')));
        if (!is_object($eqLogic)) {
            throw new CoreException(__('Action sur l\'équipement impossible. Equipement introuvable - Vérifiez l\'id : ') . $this->getOptions('eqLogic'));
        }
        switch ($this->getOptions('action')) {
            case 'show':
                $this->setLog($scenario, __('Equipement visible : ') . $eqLogic->getHumanName());
                $eqLogic->setIsVisible(1);
                $eqLogic->save();
                break;
            case 'hide':
                $this->setLog($scenario, __('Equipement masqué : ') . $eqLogic->getHumanName());
                $eqLogic->setIsVisible(0);
                $eqLogic->save();
                break;
            case 'deactivate':
                $this->setLog($scenario, __('Equipement désactivé : ') . $eqLogic->getHumanName());
                $eqLogic->setIsEnable(0);
                $eqLogic->save();
                break;
            case 'activate':
                $this->setLog($scenario, __('Equipement activé : ') . $eqLogic->getHumanName());
                $eqLogic->setIsEnable(1);
                $eqLogic->save();
                break;
        }
    }

    /**
     * @param $scenario
     * @param $options
     * @throws \Exception
     */
    protected function executeActionGotoDesign(&$scenario, $options)
    {
        $this->setLog($scenario, __('Changement design : ') . $options['plan_id']);
        EventManager::add('nextdom::gotoplan', $options['plan_id']);
    }

    /**
     * @param Scenario $scenario
     * @return null
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected function executeActionScenario(&$scenario)
    {
        if ($scenario !== null && $this->getOptions('scenario_id') == $scenario->getId()) {
            $actionScenario = &$scenario;
        } else {
            $actionScenario = ScenarioManager::byId($this->getOptions('scenario_id'));
        }
        if (!is_object($actionScenario)) {
            throw new CoreException(__('Action sur scénario impossible. Scénario introuvable - Vérifiez l\'id : ') . $this->getOptions('scenario_id'));
        }
        switch ($this->getOptions('action')) {
            case 'start':
                if ($this->getOptions('tags') != '' && !is_array($this->getOptions('tags'))) {
                    $tags = [];
                    $args = Utils::arg2array($this->getOptions('tags'));
                    foreach ($args as $key => $value) {
                        $tags['#' . trim(trim($key), '#') . '#'] = ScenarioExpressionManager::setTags(trim($value), $scenario);
                    }
                    $actionScenario->setTags($tags);
                }
                if (is_array($this->getOptions('tags'))) {
                    $actionScenario->setTags($this->getOptions('tags'));
                }
                $this->setLog($scenario, __('Lancement du scénario : ') . $actionScenario->getName() . __(' options : ') . json_encode($actionScenario->getTags()));
                if ($scenario !== null) {
                    return $actionScenario->launch('scenario', __('Lancement provoqué par le scénario  : ') . $scenario->getHumanName());
                } else {
                    return $actionScenario->launch('other', __('Lancement provoqué'));
                }
                break;
            case 'startsync':
                if ($this->getOptions('tags') != '' && !is_array($this->getOptions('tags'))) {
                    $tags = [];
                    $args = Utils::arg2array($this->getOptions('tags'));
                    foreach ($args as $key => $value) {
                        $tags['#' . trim(trim($key), '#') . '#'] = ScenarioExpressionManager::setTags(trim($value), $scenario);
                    }
                    $actionScenario->setTags($tags);
                }
                if (is_array($this->getOptions('tags'))) {
                    $actionScenario->setTags($this->getOptions('tags'));
                }
                $this->setLog($scenario, __('Lancement du scénario : ') . $actionScenario->getName() . __(' options : ') . json_encode($actionScenario->getTags()));
                if ($scenario !== null) {
                    return $actionScenario->launch('scenario', __('Lancement provoqué par le scénario  : ') . $scenario->getHumanName(), true);
                } else {
                    return $actionScenario->launch('other', __('Lancement provoqué'), true);
                }
                break;
            case 'stop':
                $this->setLog($scenario, __('Arrêt forcé du scénario : ') . $actionScenario->getName());
                $actionScenario->stop();
                break;
            case 'deactivate':
                $this->setLog($scenario, __('Désactivation du scénario : ') . $actionScenario->getName());
                $actionScenario->setIsActive(0);
                $actionScenario->save();
                break;
            case 'activate':
                $this->setLog($scenario, __('Activation du scénario : ') . $actionScenario->getName());
                $actionScenario->setIsActive(1);
                $actionScenario->save();
                break;
            case 'resetRepeatIfStatus':
                $this->setLog($scenario, __('Remise à zero des status du status des SI du scénario : ') . $actionScenario->getName());
                $actionScenario->resetRepeatIfStatus();
                break;
        }
        return null;
    }

    /**
     * @param $scenario
     * @param $options
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected function executeActionVariable(&$scenario, $options)
    {
        $options['value'] = ScenarioExpressionManager::setTags($options['value'], $scenario);
        try {
            $result = Utils::evaluate($options['value']);
            if (!is_numeric($result)) {
                $result = $options['value'];
            }
        } catch (\Exception $ex) {
            $result = $options['value'];
        }
        $this->setLog($scenario, __('Affectation de la variable ') . $this->getOptions('name') . __(' => ') . $options['value'] . ' = ' . $result);
        $dataStore = new DataStore();
        $dataStore->setKey($this->getOptions('name'));
        $dataStore->setValue($result);
        $dataStore->setType('scenario');
        $dataStore->setLink_id(-1);
        $dataStore->save();

    }

    /**
     * @param Scenario $scenario
     * @param $options
     * @return null
     * @throws \Exception
     */
    protected function executeActionDeleteVariable(&$scenario, $options)
    {
        $scenario->removeData($options['name']);
        $this->setLog($scenario, __('Suppression de la variable ') . $this->getOptions('name'));
        return null;
    }

    /**
     * @param Scenario $scenario
     * @param $options
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected function executeActionAsk(&$scenario, $options)
    {
        $dataStore = new DataStore();
        $dataStore->setType('scenario');
        $dataStore->setKey($this->getOptions('variable'));
        $dataStore->setValue('');
        $dataStore->setLink_id(-1);
        $dataStore->save();
        $limit = (isset($options['timeout'])) ? $options['timeout'] : 300;
        $options_cmd = ['title' => $options['question'], 'message' => $options['question'], 'answer' => explode(';', $options['answer']), 'timeout' => $limit, 'variable' => $this->getOptions('variable')];
        //Recuperation des tags
        $tags = $scenario->getTags();
        if (isset($tags['#profile#']) === true) {
            //Remplacement du pattern #profile# par le profile utilisateur
            //si la commande contient #profile#
            $this->setOptions('cmd', str_replace('#profile#', $tags['#profile#'], $this->getOptions('cmd')));
        }

        // Recherche de la commandeId avec le bon user
        $cmd = CmdManager::byId(str_replace('#', '', $this->getOptions('cmd')));
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande introuvable - Vérifiez l\'id : ') . $this->getOptions('cmd'));
        }
        $this->setLog($scenario, __('Demande ') . json_encode($options_cmd));
        $cmd->setCache('ask::variable', $this->getOptions('variable'));
        $cmd->setCache('ask::endtime', strtotime('now') + $limit);
        $cmd->execCmd($options_cmd);
        $occurence = 0;
        $value = '';
        while (true) {
            $dataStore = DataStoreManager::byTypeLinkIdKey('scenario', -1, $this->getOptions('variable'));
            if (is_object($dataStore)) {
                $value = $dataStore->getValue();
            }
            if ($value != '') {
                break;
            }
            if ($occurence > $limit) {
                break;
            }
            $occurence++;
            sleep(1);
        }
        if ($value == '') {
            $value = __('Aucune réponse');
            $cmd->setCache('ask::variable', 'none');
            $dataStore = DataStoreManager::byTypeLinkIdKey('scenario', -1, $this->getOptions('variable'));
            $dataStore->setValue($value);
            $dataStore->save();
        }
        $this->setLog($scenario, __('Réponse ') . $value);
    }

    /**
     * @param Scenario $scenario
     * @throws \Exception
     */
    protected function executeActionNextDomPowerOff(&$scenario)
    {
        $this->setLog($scenario, __('Lancement de l\'arret de NextDom'));
        $scenario->persistLog();
        NextDomHelper::haltSystem();
    }

    /**
     * @param Scenario $scenario
     * @throws \Exception
     */
    protected function executeActionNextDomReboot(&$scenario)
    {
        $this->setLog($scenario, __('Lancement du redémarrage de NextDom'));
        $scenario->persistLog();
        NextDomHelper::rebootSystem();
    }

    /**
     * @param Scenario $scenario
     * @param $options
     * @throws \Exception
     */
    protected function executeActionScenarioReturn(&$scenario, $options)
    {
        $this->setLog($scenario, __('Demande de retour d\'information : ') . $options['message']);
        if ($scenario->getReturn() === true) {
            $scenario->setReturn($options['message']);
        } else {
            $scenario->setReturn($scenario->getReturn() . ' ' . $options['message']);
        }
    }

    /**
     * @param Scenario $scenario
     * @throws \Exception
     */
    protected function executeActionRemoveInat(&$scenario)
    {
        if ($scenario !== null) {
            $this->setLog($scenario, __('Suppression des blocs DANS et A programmés du scénario '));
            $crons = CronManager::searchClassAndFunction('scenario', 'doIn', '"scenario_id":' . $scenario->getId() . ',');
            if (is_array($crons)) {
                foreach ($crons as $cron) {
                    if ($cron->getState() != 'run') {
                        $cron->remove();
                    }
                }
            }
        }
    }

    /**
     * @param $scenario
     * @param $options
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected function executeActionReport(&$scenario, $options)
    {
        $cmd_parameters = ['files' => null];
        $this->setLog($scenario, __('Génération d\'un rapport de type ') . $options['type']);
        switch ($options['type']) {
            case 'view':
                $view = ViewManager::byId($options['view_id']);
                if (!is_object($view)) {
                    throw new CoreException(__('Vue introuvable - Vérifiez l\'id : ') . $options['view_id']);
                }
                $this->setLog($scenario, __('Génération du rapport ') . $view->getName());
                $cmd_parameters['files'] = [$view->report($options['export_type'], $options)];
                $cmd_parameters['title'] = __('[' . ConfigManager::byKey('name') . '] Rapport ') . $view->getName() . __(' du ') . date(DateFormat::FULL);
                $cmd_parameters['message'] = __('Veuillez trouver ci-joint le rapport ') . $view->getName() . __(' généré le ') . date(DateFormat::FULL);
                break;
            case 'plan':
                $plan = PlanHeaderManager::byId($options['plan_id']);
                if (!is_object($plan)) {
                    throw new CoreException(__('Design introuvable - Vérifiez l\'id : ') . $options['plan_id']);
                }
                $this->setLog($scenario, __('Génération du rapport ') . $plan->getName());
                $cmd_parameters['files'] = [$plan->report($options['export_type'], $options)];
                $cmd_parameters['title'] = __('[' . ConfigManager::byKey('name') . '] Rapport ') . $plan->getName() . __(' du ') . date(DateFormat::FULL);
                $cmd_parameters['message'] = __('Veuillez trouver ci-joint le rapport ') . $plan->getName() . __(' généré le ') . date(DateFormat::FULL);
                break;
            case 'plugin':
                $plugin = PluginManager::byId($options['plugin_id']);
                if (!is_object($plugin)) {
                    throw new CoreException(__('Panel introuvable - Vérifiez l\'id : ') . $options['plugin_id']);
                }
                $this->setLog($scenario, __('Génération du rapport ') . $plugin->getName());
                $cmd_parameters['files'] = [$plugin->report($options['export_type'], $options)];
                $cmd_parameters['title'] = __('[' . ConfigManager::byKey('name') . '] Rapport ') . $plugin->getName() . __(' du ') . date(DateFormat::FULL);
                $cmd_parameters['message'] = __('Veuillez trouver ci-joint le rapport ') . $plugin->getName() . __(' généré le ') . date(DateFormat::FULL);
                break;
            case 'eqAnalyse':
                $url = NetworkHelper::getNetworkAccess('internal') . '/index.php?v=d&p=eqAnalyse&report=1';
                $this->setLog($scenario, __('Génération du rapport ') . $url);
                $cmd_parameters['files'] = [ReportHelper::generate($url, 'other', 'eqAnalyse', $options['export_type'], $options)];
                $cmd_parameters['title'] = __('[' . ConfigManager::byKey('name') . '] Rapport équipement du ') . date(DateFormat::FULL);
                $cmd_parameters['message'] = __('Veuillez trouver ci-joint le rapport équipement généré le ') . date(DateFormat::FULL);
                break;
        }
        if ($cmd_parameters['files'] === null) {
            throw new CoreException(__('Erreur : Aucun rapport généré'));
        }
        if ($this->getOptions('cmd') != '') {
            $cmd = CmdManager::byId(str_replace('#', '', $this->getOptions('cmd')));
            if (!is_object($cmd)) {
                throw new CoreException(__('Commande introuvable veuillez vérifiez l\'id : ') . $this->getOptions('cmd'));
            }
            $this->setLog($scenario, __('Envoi du rapport généré sur ') . $cmd->getHumanName());
            $cmd->execCmd($cmd_parameters);
        }
    }

    /**
     * @param Scenario $scenario
     * @param $options
     * @throws \Exception
     */
    protected function executeActionTag(&$scenario, $options)
    {
        $tags = $scenario->getTags();
        $tags['#' . $options['name'] . '#'] = $options['value'];
        $this->setLog($scenario, __('Mise à jour du tag ') . '#' . $options['name'] . '#' . ' => ' . $options['value']);
        $scenario->setTags($tags);
    }

    /**
     * @param Scenario $scenario
     * @param $options
     * @return mixed
     * @throws CoreException
     * @throws \ReflectionException
     */
    protected function executeActionOthers(&$scenario, $options)
    {
        $cmd = CmdManager::byId(str_replace('#', '', $this->getExpression()));
        if (is_object($cmd)) {
            if ($cmd->isSubType(CmdSubType::SLIDER) && isset($options['slider'])) {
                $options['slider'] = Utils::evaluate($options['slider']);
            }
            if (is_array($options) && (count($options) > 1 || (isset($options['background']) && $options['background'] == 1))) {
                $this->setLog($scenario, __('Exécution de la commande ') . $cmd->getHumanName() . __(" avec comme option(s) : ") . json_encode($options));
            } else {
                $this->setLog($scenario, __('Exécution de la commande ') . $cmd->getHumanName());
            }
            return $cmd->execCmd($options);
        }
        $this->setLog($scenario, __('[Erreur] Aucune commande trouvée pour ') . $this->getExpression());
        return null;
    }

    /*     * **********************Getteur Setteur*************************** */

    public function remove()
    {
        DBHelper::remove($this);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getAllId()
    {
        $return = [
            'element' => [],
            'subelement' => [],
            'expression' => [$this->getId()],
        ];
        $result = [
            'element' => [],
            'subelement' => [],
            'expression' => [],
        ];
        if ($this->getType() == 'element') {
            $element = ScenarioElementManager::byId($this->getExpression());
            if (is_object($element)) {
                $result = $element->getAllId();
            }
        }
        $return['element'] = array_merge($return['element'], $result['element']);
        $return['subelement'] = array_merge($return['subelement'], $result['subelement']);
        $return['expression'] = array_merge($return['expression'], $result['expression']);
        return $return;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $_id
     * @return $this
     */
    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
    }

    /**
     * @param $_scenarioSubElement_id
     * @return int
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function copy($_scenarioSubElement_id)
    {
        $expressionCopy = clone $this;
        $expressionCopy->setId('');
        $expressionCopy->setScenarioSubElement_id($_scenarioSubElement_id);
        $expressionCopy->save();
        if ($expressionCopy->getType() == 'element') {
            $element = ScenarioElementManager::byId($expressionCopy->getExpression());
            if (is_object($element)) {
                $expressionCopy->setExpression($element->copy());
                $expressionCopy->save();
            }
        }
        return $expressionCopy->getId();
    }

    /**
     * @return bool
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        $this->checkBackground();
        DBHelper::save($this);
        return true;
    }

    public function emptyOptions()
    {
        $this->options = '';
    }

    /**
     * @return null
     * @throws \Exception
     */
    public function resetRepeatIfStatus()
    {
        if ($this->getType() != 'element') {
            return null;
        }
        $element = ScenarioElementManager::byId($this->getExpression());
        if (is_object($element)) {
            $element->resetRepeatIfStatus();
        }
        return null;
    }

    /**
     * @return array|string
     * @throws \Exception
     */
    public function export()
    {
        $result = '';
        if ($this->getType() == 'element') {
            $element = ScenarioElementManager::byId($this->getExpression());
            if (is_object($element)) {
                $exports = explode("\n", $element->export());
                foreach ($exports as $export) {
                    $result .= "    " . $export . "\n";
                }
            }
            $result = rtrim($result);
        } else {
            $options = $this->getOptions();
            if ($this->getType() == 'action') {
                if ($this->getExpression() == ScenarioExpressionAction::ICON) {
                    return '';
                } elseif ($this->getExpression() == ScenarioExpressionAction::SLEEP) {
                    return '(sleep) Pause de  : ' . $options['duration'];
                } elseif ($this->getExpression() == ScenarioExpressionAction::STOP) {
                    return '(stop) Arret du scenario';
                } elseif ($this->getExpression() == ScenarioExpressionAction::SCENARIO_RETURN) {
                    $actionScenario = ScenarioManager::byId($this->getOptions('scenario_id'));
                    if (is_object($actionScenario)) {
                        return '(scenario) ' . $this->getOptions('action') . ' de ' . $actionScenario->getHumanName();
                    }
                } elseif ($this->getExpression() == ScenarioExpressionAction::VARIABLE) {
                    return '(variable) Affectation de la variable : ' . $this->getOptions('name') . ' à ' . $this->getOptions('value');
                } else {
                    $result = NextDomHelper::toHumanReadable($this->getExpression());
                    if (is_array($options) && count($options) != 0) {
                        $result .= ' - Options : ' . json_encode(NextDomHelper::toHumanReadable($options));
                    }
                }
            } elseif ($this->getType() == 'condition') {
                $result = NextDomHelper::toHumanReadable($this->getExpression());
            }
        }
        return $result;
    }

    /**
     * @return array|mixed|null
     * @throws \Exception
     */
    public function getSubElement()
    {
        return ScenarioSubElementManager::byId($this->getScenarioSubElement_id());
    }

    /**
     * @return int
     */
    public function getScenarioSubElement_id()
    {
        return $this->scenarioSubElement_id;
    }

    /**
     * @param $_scenarioSubElement_id
     * @return $this
     */
    public function setScenarioSubElement_id($_scenarioSubElement_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->scenarioSubElement_id, $_scenarioSubElement_id);
        $this->scenarioSubElement_id = $_scenarioSubElement_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubtype()
    {
        return $this->subtype;
    }

    /**
     * @param $_subtype
     * @return $this
     */
    public function setSubtype($_subtype)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->subtype, $_subtype);
        $this->subtype = $_subtype;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param $_order
     * @return $this
     */
    public function setOrder($_order)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->order, $_order);
        $this->order = $_order;
        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'scenarioExpression';
    }

    /**
     * @return bool
     */
    public function getChanged()
    {
        return $this->_changed;
    }

    /**
     * @param $_changed
     * @return $this
     */
    public function setChanged($_changed)
    {
        $this->_changed = $_changed;
        return $this;
    }
}
