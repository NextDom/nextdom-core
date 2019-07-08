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

use NextDom\Helpers\DBHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\CronManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\ScenarioExpressionManager;

/**
 * Interactquery
 *
 * @ORM\Table(name="interactQuery", indexes={@ORM\Index(name="fk_sarahQuery_sarahDef1_idx", columns={"interactDef_id"}), @ORM\Index(name="query", columns={"query"})})
 * @ORM\Entity
 */
class InteractQuery implements EntityInterface
{

    /**
     * @var integer
     *
     * @ORM\Column(name="interactDef_id", type="integer", nullable=false)
     */
    protected $interactDef_id;

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="text", length=65535, nullable=true)
     */
    protected $query;

    /**
     * @var string
     *
     * @ORM\Column(name="actions", type="text", length=65535, nullable=true)
     */
    protected $actions;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    protected $_changed = false;

    /**
     * @return $this
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        if ($this->getQuery() == '') {
            throw new \Exception(__('La commande vocale ne peut pas être vide'));
        }
        if ($this->getInteractDef_id() == '') {
            throw new \Exception(__('InteractDef_id ne peut pas être vide'));
        }
        DBHelper::save($this);
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param $_query
     * @return $this
     */
    public function setQuery($_query)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->query, $_query);
        $this->query = $_query;
        return $this;
    }

    /**
     * @return int
     */
    public function getInteractDef_id()
    {
        return $this->interactDef_id;
    }

    /**
     * @param $_interactDef_id
     * @return $this
     */
    public function setInteractDef_id($_interactDef_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->interactDef_id, $_interactDef_id);
        $this->interactDef_id = $_interactDef_id;
        return $this;
    }

    /**
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function remove()
    {
        return DBHelper::remove($this);
    }

    /**
     * @param $_parameters
     * @return mixed
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function executeAndReply($_parameters)
    {
        if (isset($_parameters['reply_cmd'])) {
            unset($_parameters['reply_cmd']);
        }
        $interactDef = InteractDefManager::byId($this->getInteractDef_id());
        if (!is_object($interactDef)) {
            return __('Inconsistance de la base de données');
        }
        if (isset($_parameters['profile']) && trim($interactDef->getPerson()) != '') {
            $person = strtolower($interactDef->getPerson());
            $person = explode('|', $person);
            if (!in_array($_parameters['profile'], $person)) {
                return __('Vous n\'êtes pas autorisé à exécuter cette action');
            }
        }
        $reply = $interactDef->selectReply();
        $replace = array('#query#' => $this->getQuery());
        foreach ($_parameters as $key => $value) {
            $replace['#' . $key . '#'] = $value;
        }
        $tags = null;
        if (isset($_parameters['dictation'])) {
            $tags = InteractDefManager::getTagFromQuery($this->getQuery(), $_parameters['dictation']);
            $replace['#dictation#'] = $_parameters['dictation'];
        }
        if (is_array($tags)) {
            $replace = array_merge($replace, $tags);
        }
        $executeDate = null;

        if (isset($replace['#duration#'])) {
            $dateConvert = array(
                'heure' => 'hour',
                'mois' => 'month',
                'semaine' => 'week',
                'année' => 'year',
            );
            $replace['#duration#'] = str_replace(array_keys($dateConvert), $dateConvert, $replace['#duration#']);
            $executeDate = strtotime('+' . $replace['#duration#']);
        }
        if (isset($replace['#time#'])) {
            $time = str_replace(array('h'), array(':'), $replace['#time#']);
            if (strlen($time) == 1) {
                $time .= ':00';
            } else if (strlen($time) == 2) {
                $time .= ':00';
            } else if (strlen($time) == 3) {
                $time .= '00';
            }
            $time = str_replace('::', ':', $time);
            $executeDate = strtotime($time);
            if ($executeDate < strtotime('now')) {
                $executeDate += 3600;
            }
        }
        if ($executeDate !== null && !isset($_parameters['execNow'])) {
            if (date('Y', $executeDate) < 2000) {
                return __('Erreur : impossible de calculer la date de programmation');
            }
            if ($executeDate < (strtotime('now') + 60)) {
                $executeDate = strtotime('now') + 60;
            }
            $crons = CronManager::searchClassAndFunction('interactQuery', 'doIn', '"interactQuery_id":' . $this->getId());
            if (is_array($crons)) {
                foreach ($crons as $cron) {
                    if ($cron->getState() != 'run') {
                        $cron->remove();
                    }
                }
            }
            $cron = new Cron();
            $cron->setClass('interactQuery');
            $cron->setFunction('doIn');
            $cron->setOption(array_merge(array('interactQuery_id' => intval($this->getId())), $_parameters));
            $cron->setLastRun(date('Y-m-d H:i:s'));
            $cron->setOnce(1);
            $cron->setSchedule(CronManager::convertDateToCron($executeDate));
            $cron->save();
            $replace['#valeur#'] = date('Y-m-d H:i:s', $executeDate);
            $result = ScenarioExpressionManager::setTags(str_replace(array_keys($replace), $replace, $reply));
            return $result;
        }
        $replace['#valeur#'] = '';
        $colors = array_change_key_case(ConfigManager::byKey('convertColor'));
        if (is_array($this->getActions('cmd'))) {
            foreach ($this->getActions('cmd') as $action) {
                try {
                    $options = array();
                    if (isset($action['options'])) {
                        $options = $action['options'];
                    }
                    if ($tags !== null) {
                        foreach ($options as &$option) {
                            $option = str_replace(array_keys($replace), $replace, $option);
                        }
                        if (isset($options['color']) && isset($colors[strtolower($options['color'])])) {
                            $options['color'] = $colors[strtolower($options['color'])];
                        }
                    }
                    $cmd = CmdManager::byId(str_replace('#', '', $action['cmd']));
                    if (is_object($cmd)) {
                        $replace['#unite#'] = $cmd->getUnite();
                        $replace['#commande#'] = $cmd->getName();
                        $replace['#objet#'] = '';
                        $replace['#equipement#'] = '';
                        $eqLogic = $cmd->getEqLogicId();
                        if (is_object($eqLogic)) {
                            $replace['#equipement#'] = $eqLogic->getName();
                            $object = $eqLogic->getObject();
                            if (is_object($object)) {
                                $replace['#objet#'] = $object->getName();
                            }
                        }
                    }
                    $tags = array();
                    if (isset($options['tags'])) {
                        $options['tags'] = Utils::arg2array($options['tags']);
                        foreach ($options['tags'] as $key => $value) {
                            $tags['#' . trim(trim($key), '#') . '#'] = ScenarioExpressionManager::setTags(trim($value));
                        }
                    }
                    $options['tags'] = array_merge($replace, $tags);
                    $return = ScenarioExpressionManager::createAndExec('action', $action['cmd'], $options);
                    if (trim($return) !== '' && trim($return) !== null) {
                        $replace['#valeur#'] .= ' ' . $return;
                    }
                } catch (\Exception $e) {
                    LogHelper::addError('interact', __('Erreur lors de l\'exécution de ') . $action['cmd'] . __('. Détails : ') . $e->getMessage());
                }
            }
        }
        if ($interactDef->getOptions('waitBeforeReply') != '' && $interactDef->getOptions('waitBeforeReply') != 0 && is_numeric($interactDef->getOptions('waitBeforeReply'))) {
            sleep($interactDef->getOptions('waitBeforeReply'));
        }
        $reply = NextDomHelper::evaluateExpression($reply);
        $replace['#valeur#'] = trim($replace['#valeur#']);
        $replace['#profile#'] = isset($_parameters['profile']) ? $_parameters['profile'] : '';
        if ($interactDef->getOptions('convertBinary') != '') {
            $convertBinary = explode('|', $interactDef->getOptions('convertBinary'));
            if (is_array($convertBinary) && count($convertBinary) == 2) {
                $replace['1'] = $convertBinary[1];
                $replace['0'] = $convertBinary[0];
            }
        }
        foreach ($replace as $key => $value) {
            if (is_array($value)) {
                unset($replace[$key]);
            }
        }
        if ($replace['#valeur#'] == '') {
            $replace['#valeur#'] = __('aucune valeur');
        }
        $replace['"'] = '';
        return str_replace(array_keys($replace), $replace, $reply);
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
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getActions($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->actions, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setActions($_key, $_value)
    {
        $actions = Utils::setJsonAttr($this->actions, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->actions, $actions);
        $this->actions = $actions;
        return $this;
    }

    /**
     * @return InteractDef
     */
    public function getInteractDef()
    {
        return InteractDefManager::byId($this->interactDef_id);
    }

    /**
     * @param $_replace
     * @param $_by
     * @param $_in
     */
    public function replaceForContextual($_replace, $_by, $_in)
    {
        Interactquery::replaceForContextual($_replace, $_by, $_in);
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

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'interactQuery';
    }
}
