<?php
/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

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

namespace NextDom\Managers;

use NextDom\Enums\CmdSubType;
use NextDom\Enums\CmdType;
use NextDom\Enums\Common;
use NextDom\Enums\ConfigKey;
use NextDom\Enums\LogTarget;
use NextDom\Enums\NextDomObj;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Model\Entity\EqLogic;
use NextDom\Model\Entity\InteractQuery;
use NextDom\Model\Entity\JeeObject;
use NextDom\Model\Entity\Listener;

require_once NEXTDOM_ROOT . '/core/class/cache.class.php';

/**
 * Class InteractQueryManager
 * @package NextDom\Managers
 */
class InteractQueryManager
{

    const CLASS_NAME = InteractQuery::class;
    const DB_CLASS_NAME = '`interactQuery`';

    /**
     * @param $_interactDef_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byInteractDefId($_interactDef_id)
    {
        $values = [
            'interactDef_id' => $_interactDef_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE interactDef_id=:interactDef_id
                ORDER BY `query`';
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param $_action
     * @return InteractQuery[]
     * @throws \Exception
     */
    public static function searchActions($_action)
    {
        if (!is_array($_action)) {
            $values = [
                'actions' => '%' . $_action . '%',
            ];
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE actions LIKE :actions';
        } else {
            $values = [
                'actions' => '%' . $_action[0] . '%',
            ];
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE actions LIKE :actions';
            for ($i = 1; $i < count($_action); $i++) {
                $values['actions' . $i] = '%' . $_action[$i] . '%';
                $sql .= ' OR actions LIKE :actions' . $i;
            }
        }
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param $_interactDef_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function removeByInteractDefId($_interactDef_id)
    {
        $values = [
            'interactDef_id' => $_interactDef_id,
        ];
        $sql = 'DELETE FROM ' . self::DB_CLASS_NAME . '
                WHERE interactDef_id = :interactDef_id';
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param JeeObject $a
     * @param JeeObject $b
     * @return int
     */
    public static function cmp_objectName($a, $b)
    {
        return (strlen($a->getName()) < strlen($b->getName())) ? +1 : -1;
    }

    /**
     * @param $_options
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function warnMeExecute($_options)
    {
        $warnMeCmd = (isset($_options[Common::REPLY_CMD])) ? $_options[Common::REPLY_CMD] : ConfigManager::byKey('interact::warnme::defaultreturncmd');
        if (!isset($_options['test']) || $_options['test'] == '' || $warnMeCmd == '') {
            ListenerManager::byId($_options['listener_id'])->remove();
            return;
        }
        $result = NextDomHelper::evaluateExpression(str_replace('#value#', $_options['value'], $_options['test']));
        if ($result) {
            ListenerManager::byId($_options['listener_id'])->remove();
            $cmd = CmdManager::byId(str_replace('#', '', $warnMeCmd));
            if (!is_object($cmd)) {
                return;
            }
            $cmd->execCmd([
                'title' => __('Alerte : ') . str_replace('#value#', $_options['name'], $_options['test']) . __(' valeur : ') . $_options['value'],
                'message' => __('Alerte : ') . str_replace('#value#', $_options['name'], $_options['test']) . __(' valeur : ') . $_options['value'],
            ]);
        }
    }

    /**
     * @param $_query
     * @param array $_parameters
     * @return array|bool|null|string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function tryToReply($_query, $_parameters = [])
    {
        if (trim($_query) == '') {
            return ['reply' => ''];
        }
        $_parameters['identifier'] = '';
        if (isset($_parameters['plugin'])) {
            $_parameters['identifier'] = $_parameters['plugin'];
        } else {
            $_parameters['identifier'] = 'unknown';
        }
        if (isset($_parameters[Common::PROFILE])) {
            $_parameters['identifier'] .= '::' . $_parameters[Common::PROFILE];
        }
        $_parameters['dictation'] = $_query;
        if (isset($_parameters[Common::PROFILE])) {
            $_parameters[Common::PROFILE] = strtolower($_parameters[Common::PROFILE]);
        }
        $reply = '';
        $words = str_word_count($_query, 1);
        $startContextual = explode(';', ConfigManager::byKey('interact::contextual::startpriority'));
        if (is_array($startContextual) && count($startContextual) > 0 && ConfigManager::byKey('interact::contextual::enable') == 1 && isset($words[0]) && in_array(strtolower($words[0]), $startContextual)) {
            $reply = self::contextualReply($_query, $_parameters);
            LogHelper::addDebug(LogTarget::INTERACT, 'Je cherche interaction contextuel (prioritaire) : ' . print_r($reply, true));
        }
        $startWarnMe = explode(';', ConfigManager::byKey('interact::warnme::start'));
        if (is_array($startWarnMe) && count($startWarnMe) > 0 && ConfigManager::byKey('interact::warnme::enable') == 1 && Utils::strContainsOneOf(strtolower(Utils::sanitizeAccent($_query)), $startWarnMe)) {
            $reply = self::warnMe($_query, $_parameters);
            LogHelper::addDebug(LogTarget::INTERACT, 'Je cherche interaction "previens-moi" : ' . print_r($reply, true));
        }
        if (ConfigManager::byKey('interact::contextual::splitword') != '') {
            $splitWords = explode(';', ConfigManager::byKey('interact::contextual::splitword'));
            $queries = [];
            foreach ($splitWords as $split) {
                if (in_array($split, $words)) {
                    $queries = array_merge($queries, explode(' ' . $split . ' ', $_query));
                }
            }
            if (count($queries) > 1) {
                $reply = self::tryToReply($queries[0], $_parameters);
                if ($reply != '') {
                    array_shift($queries);
                    foreach ($queries as $query) {
                        $tmp = self::contextualReply($query, $_parameters);
                        if (is_array($tmp)) {
                            foreach ($tmp as $key => $value) {
                                if (!isset($reply[$key])) {
                                    $reply[$key] = $value;
                                    continue;
                                }
                                if (is_string($value) && $reply[$key] != $value) {
                                    $reply[$key] .= "\n" . $value;
                                }
                                if (is_array($value)) {
                                    $reply[$key] = array_merge($reply[$key], $value);
                                }
                            }
                        } else {
                            $reply['reply'] .= "\n" . $tmp;
                        }
                    }
                    return $reply;
                }
            }
        }
        if ($reply == '') {
            $reply = self::pluginReply($_query, $_parameters);
            if ($reply !== null) {
                LogHelper::addInfo(LogTarget::INTERACT, 'J\'ai reçu : ' . $_query . '. Un plugin a répondu : ' . print_r($reply, true));
                return $reply;
            }
            $interactQuery = self::recognize($_query);
            if (is_object($interactQuery)) {
                $reply = $interactQuery->executeAndReply($_parameters);
                $cmds = $interactQuery->getActions(NextDomObj::CMD);
                if (isset($cmds[0]) && isset($cmds[0][NextDomObj::CMD])) {
                    self::addLastInteract(str_replace('#', '', $cmds[0][NextDomObj::CMD]), $_parameters['identifier']);
                }
                LogHelper::addInfo(LogTarget::INTERACT, 'J\'ai reçu : ' . $_query . ". J'ai compris : " . $interactQuery->getQuery() . ". J'ai répondu : " . $reply);
                return ['reply' => ucfirst($reply)];
            }
        }
        if ($reply == '' && ConfigManager::byKey('interact::autoreply::enable') == 1) {
            $reply = self::autoInteract($_query, $_parameters);
            LogHelper::addDebug(LogTarget::INTERACT, 'Je cherche dans les interactions automatiques, résultat : ' . $reply);
        }
        if ($reply == '' && ConfigManager::byKey('interact::noResponseIfEmpty', 'core', 0) == 0 && (!isset($_parameters['emptyReply']) || $_parameters['emptyReply'] == 0)) {
            $reply = self::dontUnderstand($_parameters);
            LogHelper::addInfo(LogTarget::INTERACT, 'J\'ai reçu : ' . $_query . ". Je n'ai rien compris. J'ai répondu : " . $reply);
        }
        if (!is_array($reply)) {
            $reply = ['reply' => ucfirst($reply)];
        }
        LogHelper::addInfo(LogTarget::INTERACT, 'J\'ai reçu : ' . $_query . ". Je réponds : " . print_r($reply, true));
        if (isset($_parameters[Common::REPLY_CMD]) && is_object($_parameters[Common::REPLY_CMD]) && isset($_parameters['force_reply_cmd'])) {
            $_parameters[Common::REPLY_CMD]->execCmd(['message' => $reply['reply']]);
            return true;
        }
        return $reply;
    }

    /**
     * @param $_query
     * @param array $_parameters
     * @param null $_lastCmd
     * @return array|null|string
     * @throws \Exception
     */
    public static function contextualReply($_query, $_parameters = [], $_lastCmd = null)
    {
        $return = '';
        if (!isset($_parameters['identifier'])) {
            $_parameters['identifier'] = '';
        }
        if ($_lastCmd === null) {
            $last = CacheManager::byKey('interact::lastCmd::' . $_parameters['identifier']);
            if ($last->getValue() == '') {
                return $return;
            }
            $lastCmd = $last->getValue();
        } else {
            $lastCmd = $_lastCmd;
        }
        $current = [];
        $current[NextDomObj::CMD] = CmdManager::byId($lastCmd);
        if (is_object($current[NextDomObj::CMD])) {
            $current[NextDomObj::EQLOGIC] = $current[NextDomObj::CMD]->getEqLogicId();
            if (!is_object($current[NextDomObj::EQLOGIC])) {
                return $return;
            }
            $current[NextDomObj::OBJECT] = $current[NextDomObj::EQLOGIC]->getObject();
            $humanName = $current[NextDomObj::CMD]->getHumanName();
        } else {
            $humanName = strtolower(Utils::sanitizeAccent($lastCmd));
            $current = self::findInQuery(NextDomObj::OBJECT, $humanName);
            $current = array_merge($current, self::findInQuery(Common::SUMMARY, $current[Common::QUERY], $current));
        }

        $data = self::findInQuery(NextDomObj::OBJECT, $_query);
        $data = array_merge($data, self::findInQuery(NextDomObj::EQLOGIC, $data[Common::QUERY], $data));
        $data = array_merge($data, self::findInQuery(NextDomObj::CMD, $data[Common::QUERY], $data));
        if (isset($data[NextDomObj::OBJECT]) && is_object($current[NextDomObj::OBJECT])) {
            $humanName = self::replaceForContextual($current[NextDomObj::OBJECT]->getName(), $data[NextDomObj::OBJECT]->getName(), $humanName);
        }
        if (isset($data[NextDomObj::CMD]) && is_object($current[NextDomObj::CMD])) {
            $humanName = self::replaceForContextual($current[NextDomObj::CMD]->getName(), $data[NextDomObj::CMD]->getName(), $humanName);
        }
        if (isset($data[NextDomObj::EQLOGIC]) && is_object($current[NextDomObj::EQLOGIC])) {
            $humanName = self::replaceForContextual($current[NextDomObj::EQLOGIC]->getName(), $data[NextDomObj::EQLOGIC]->getName(), $humanName);
        }
        $reply = self::pluginReply($humanName, $_parameters);
        if ($reply !== null) {
            return $reply;
        }
        $return = self::autoInteract(str_replace(['][', '[', ']'], [' ', '', ''], $humanName), $_parameters);
        if ($return == '' && $_lastCmd === null) {
            $last = CacheManager::byKey('interact::lastCmd2::' . $_parameters['identifier']);
            if ($last->getValue() != '') {
                $return = self::contextualReply($_query, $_parameters, $last->getValue());
            }
        }
        return $return;
    }

    /**
     * @param $_type
     * @param $_query
     * @param null $_data
     * @return array
     * @throws \Exception
     */
    public static function findInQuery($_type, $_query, $_data = null)
    {
        $return = [];
        $return[Common::QUERY] = strtolower(Utils::sanitizeAccent($_query));
        $return[$_type] = null;
        $synonyms = self::getQuerySynonym($return[Common::QUERY], $_type);
        if ($_type == NextDomObj::OBJECT) {
            $jeeObjects = JeeObjectManager::all();
        } elseif ($_type == NextDomObj::EQLOGIC) {
            if ($_data !== null && is_object($_data[NextDomObj::OBJECT])) {
                $jeeObjects = $_data[NextDomObj::OBJECT]->getEqLogic();
            } else {
                $jeeObjects = EqLogicManager::all(true);
            }
        } elseif ($_type == NextDomObj::CMD) {
            if ($_data !== null && is_object($_data[NextDomObj::EQLOGIC])) {
                $jeeObjects = $_data[NextDomObj::EQLOGIC]->getCmd();
            } elseif ($_data !== null && is_object($_data[NextDomObj::OBJECT])) {
                $jeeObjects = [];
                foreach ($_data[NextDomObj::OBJECT]->getEqLogic() as $eqLogic) {
                    if ($eqLogic->getIsEnable() == 0) {
                        continue;
                    }
                    foreach ($eqLogic->getCmd() as $cmd) {
                        $jeeObjects[] = $cmd;
                    }
                }
            } else {
                $jeeObjects = CmdManager::all();
            }
        } elseif ($_type == Common::SUMMARY) {
            foreach (ConfigManager::byKey(ConfigKey::OBJECT_SUMMARY) as $summary) {
                if (count($synonyms) > 0 && in_array(strtolower($summary['name']), $synonyms)) {
                    $return[$_type] = $summary;
                    break;
                }
                if (self::autoInteractWordFind($return[Common::QUERY], $summary['name'])) {
                    $return[$_type] = $summary;
                    $return[Common::QUERY] = str_replace(strtolower(Utils::sanitizeAccent($summary['name'])), '', $return[Common::QUERY]);
                    break;
                }
            }
            if (count($synonyms) > 0) {
                foreach ($synonyms as $summary) {
                    $return[Common::QUERY] = str_replace(strtolower(Utils::sanitizeAccent($summary)), '', $return[Common::QUERY]);
                }
            }
            return $return;
        }
        usort($jeeObjects, ["interactQuery", "cmp_objectName"]);
        foreach ($jeeObjects as $jeeObject) {
            if ($jeeObject->getConfiguration('interact::auto::disable', 0) == 1) {
                continue;
            }
            if (count($synonyms) > 0 && in_array(strtolower($jeeObject->getName()), $synonyms)) {
                $return[$_type] = $jeeObject;
                break;
            }
            if (self::autoInteractWordFind($return[Common::QUERY], $jeeObject->getName())) {
                $return[$_type] = $jeeObject;
                break;
            }
        }
        if ($_type != NextDomObj::EQLOGIC && is_object($return[$_type])) {
            $return[Common::QUERY] = str_replace(strtolower(Utils::sanitizeAccent($return[$_type]->getName())), '', $return[Common::QUERY]);
            if (count($synonyms) > 0) {
                foreach ($synonyms as $summary) {
                    $return[Common::QUERY] = str_replace(strtolower(Utils::sanitizeAccent($summary)), '', $return[Common::QUERY]);
                }
            }
        }
        return $return;
    }

    /**
     * @param $_query
     * @param $_for
     * @return array
     * @throws \Exception
     */
    public static function getQuerySynonym($_query, $_for)
    {
        $return = [];
        $base_synonyms = explode(';', ConfigManager::byKey('interact::autoreply::' . $_for . '::synonym'));
        if (count($base_synonyms) == 0) {
            return $return;
        }
        foreach ($base_synonyms as $synonyms) {
            if (trim($synonyms) == '') {
                continue;
            }
            $synonyms = explode('|', $synonyms);
            foreach ($synonyms as $synonym) {
                if (self::autoInteractWordFind($_query, $synonym)) {
                    $return = array_merge($return, $synonyms);
                }
            }
        }
        return $return;
    }

    /**
     * @param $_string
     * @param $_word
     * @return false|int
     */
    public static function autoInteractWordFind($_string, $_word)
    {
        return preg_match(
            '/( |^)' . preg_quote(strtolower(Utils::sanitizeAccent($_word)), '/') . '( |$)/',
            str_replace("'", ' ', strtolower(Utils::sanitizeAccent($_string)))
        );
    }

    /**
     * @param $_replace
     * @param $_by
     * @param $_in
     * @return mixed
     */
    public static function replaceForContextual($_replace, $_by, $_in)
    {
        return str_replace(strtolower(Utils::sanitizeAccent($_replace)), strtolower(Utils::sanitizeAccent($_by)), str_replace($_replace, $_by, $_in));
    }

    /**
     * @param $_query
     * @param array $_parameters
     * @return array|null
     * @throws \Exception
     */
    public static function pluginReply($_query, $_parameters = [])
    {
        try {
            foreach (PluginManager::listPlugin(true) as $plugin) {
                if (ConfigManager::byKey('functionality::interact::enable', $plugin->getId(), 1) == 0) {
                    continue;
                }
                if (method_exists($plugin->getId(), NextDomObj::INTERACT)) {
                    $plugin_id = $plugin->getId();
                    $reply = $plugin_id::interact($_query, $_parameters);
                    if ($reply !== null || is_array($reply)) {
                        $reply['reply'] = '[' . $plugin_id . '] ' . $reply['reply'];
                        self::addLastInteract($_query, $_parameters['identifier']);
                        LogHelper::addDebug(LogTarget::INTERACT, 'Le plugin ' . $plugin_id . ' a répondu');
                        return $reply;
                    }
                }
            }
        } catch (\Exception $e) {
            return ['reply' => __('Erreur : ') . $e->getMessage()];
        }
        return null;
    }

    /**
     * @param $_lastCmd
     * @param string $_identifier
     * @throws \Exception
     */
    public static function addLastInteract($_lastCmd, $_identifier = 'unknown')
    {
        $last = CacheManager::byKey('interact::lastCmd::' . $_identifier);
        if ($last->getValue() == '') {
            CacheManager::set('interact::lastCmd2::' . $_identifier, $last->getValue(), 300);
        }
        CacheManager::set('interact::lastCmd::' . $_identifier, str_replace('#', '', $_lastCmd), 300);
    }

    /**
     * @param $_query
     * @param array $_parameters
     * @return string
     * @throws \Exception
     */
    public static function autoInteract($_query, $_parameters = [])
    {
        if (!isset($_parameters['identifier'])) {
            $_parameters['identifier'] = '';
        }
        $data = self::findInQuery(NextDomObj::OBJECT, $_query);
        $data[Common::CMD_PARAMETERS] = [];
        /** @var EqLogic[] $data */
        $data = array_merge($data, self::findInQuery(NextDomObj::EQLOGIC, $data[Common::QUERY], $data));
        $data = array_merge($data, self::findInQuery(NextDomObj::CMD, $data[Common::QUERY], $data));
        if (isset($data[NextDomObj::EQLOGIC]) && is_object($data[NextDomObj::EQLOGIC]) && (!isset($data[NextDomObj::CMD]) || !is_object($data[NextDomObj::CMD]))) {
            foreach ($data[NextDomObj::EQLOGIC]->getCmd(CmdType::ACTION) as $cmd) {
                if ($cmd->isSubType(CmdSubType::SLIDER)) {
                    break;
                }
            }
            if (is_object($cmd)) {
                if (preg_match_all('/' . ConfigManager::byKey('interact::autoreply::cmd::slider::max') . '/i', $data[Common::QUERY])) {
                    $data[NextDomObj::CMD] = $cmd;
                    $data[Common::CMD_PARAMETERS][CmdSubType::SLIDER] = $cmd->getConfiguration('maxValue', 100);
                }
                if (preg_match_all('/' . ConfigManager::byKey('interact::autoreply::cmd::slider::min') . '/i', $data[Common::QUERY])) {
                    $data[NextDomObj::CMD] = $cmd;
                    $data[Common::CMD_PARAMETERS][CmdSubType::SLIDER] = $cmd->getConfiguration('minValue', 0);
                }
            }
        }
        if (!isset($data[NextDomObj::CMD]) || !is_object($data[NextDomObj::CMD])) {
            $data = array_merge($data, self::findInQuery(Common::SUMMARY, $data[Common::QUERY], $data));
            LogHelper::addDebug(LogTarget::INTERACT, print_r($data, true));
            if (!isset($data[Common::SUMMARY])) {
                return '';
            }
            $return = $data[Common::SUMMARY][Common::NAME];
            $value = '';
            if (is_object($data[NextDomObj::OBJECT])) {
                $return .= ' ' . $data[NextDomObj::OBJECT]->getName();
                $value = $data[NextDomObj::OBJECT]->getSummary($data[Common::SUMMARY][Common::KEY]);
            }
            if (trim($value) === '') {
                $value = JeeObjectManager::getGlobalSummary($data[Common::SUMMARY][Common::KEY]);
            }
            if (trim($value) === '') {
                return '';
            }
            self::addLastInteract($_query, $_parameters['identifier']);
            return $return . ' ' . $value . ' ' . $data[Common::SUMMARY]['unit'];
        }
        self::addLastInteract($data[NextDomObj::CMD]->getId(), $_parameters['identifier']);
        if ($data[NextDomObj::CMD]->isType(CmdType::INFO)) {
            return trim($data[NextDomObj::CMD]->getHumanName() . ' ' . $data[NextDomObj::CMD]->execCmd() . ' ' . $data[NextDomObj::CMD]->getUnite());
        } else {
            if ($data[NextDomObj::CMD]->getSubtype() == CmdSubType::SLIDER) {
                preg_match_all('/(\d+)/', strtolower(Utils::sanitizeAccent($data[Common::QUERY])), $matches);
                if (isset($matches[0]) && isset($matches[0][0])) {
                    $data[Common::CMD_PARAMETERS][CmdSubType::SLIDER] = $matches[0][0];
                }
            }
            if ($data[NextDomObj::CMD]->getSubtype() == CmdSubType::COLOR) {
                $colors = array_change_key_case(ConfigManager::byKey('convertColor'));
                foreach ($colors as $name => $value) {
                    if (strpos($data[Common::QUERY], $name) !== false) {
                        $data[Common::CMD_PARAMETERS]['color'] = $value;
                        break;
                    }
                }
            }
            $data[NextDomObj::CMD]->execCmd($data[Common::CMD_PARAMETERS]);
            $return = __('C\'est fait') . ' (';
            $eqLogic = $data[NextDomObj::CMD]->getEqLogic();
            if (is_object($eqLogic)) {
                $linkedObject = $eqLogic->getObject();
                if (is_object($linkedObject)) {
                    $return .= $linkedObject->getName();
                }
                $return .= ' ' . $data[NextDomObj::CMD]->getEqLogic()->getName();
            }
            $return .= ' ' . $data[NextDomObj::CMD]->getName();
            if (isset($data[Common::CMD_PARAMETERS][CmdSubType::SLIDER])) {
                $return .= ' => ' . $data[Common::CMD_PARAMETERS][CmdSubType::SLIDER] . '%';
            }
            return $return . ')';
        }
    }

    /**
     * @param $_query
     * @param array $_parameters
     * @return array|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function warnMe($_query, $_parameters = [])
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        $operator = null;
        $operand = null;
        foreach ($NEXTDOM_INTERNAL_CONFIG[NextDomObj::INTERACT]['test'] as $key => $value) {
            if (Utils::strContainsOneOf(strtolower(Utils::sanitizeAccent($_query)), $value)) {
                $operator .= $key;
                break;
            }
        }
        preg_match_all('!\d+!', strtolower(Utils::sanitizeAccent($_query)), $matches);
        if (isset($matches[0]) && isset($matches[0][0])) {
            $operand = $matches[0][0];
        }
        if ($operand === null || $operator === null) {
            return null;
        }
        $test = '#value# ' . $operator . ' ' . $operand;
        $options = ['test' => $test];
        if (is_object($_parameters[Common::REPLY_CMD])) {
            $options[Common::REPLY_CMD] = $_parameters[Common::REPLY_CMD]->getId();
        }
        $listener = new Listener();
        $listener->setClass(NextDomObj::INTERACT_QUERY);
        $listener->setFunction('warnMeExecute');
        $data = self::findInQuery(NextDomObj::OBJECT, $_query);
        $data = array_merge($data, self::findInQuery(NextDomObj::EQLOGIC, $data[Common::QUERY], $data));
        $data = array_merge($data, self::findInQuery(NextDomObj::CMD, $data[Common::QUERY], $data));
        if (!isset($data[NextDomObj::CMD]) || !is_object($data[NextDomObj::CMD])) {
            return null;
        } else {
            if ($data[NextDomObj::CMD]->isType(CmdType::ACTION)) {
                return null;
            }
            $options['type'] = NextDomObj::CMD;
            $options['cmd_id'] = $data[NextDomObj::CMD]->getId();
            $options['name'] = $data[NextDomObj::CMD]->getHumanName();
            $listener->addEvent($data[NextDomObj::CMD]->getId());
            $listener->setOption($options);
            $listener->save(true);
            return ['reply' => __('C\'est noté : ') . str_replace('#value#', $data[NextDomObj::CMD]->getHumanName(), $test)];
        }
    }

    /**
     * @param $_query
     * @return null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function recognize($_query)
    {
        $_query = InteractDefManager::sanitizeQuery($_query);
        if (trim($_query) == '') {
            return null;
        }
        $query = self::byQuery($_query, null, false);
        if (is_object($query)) {
            if (self::searchCorrespondence($_query, $query)) {
                LogHelper::addDebug(LogTarget::INTERACT, 'Je prends : ' . $query->getQuery());
                return $query;
            }
            return null;
        }

        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . ', MATCH query AGAINST (:query IN NATURAL LANGUAGE MODE) as score
                FROM ' . self::DB_CLASS_NAME . '
                GROUP BY id
                HAVING score > 1';
        $queries = DBHelper::getAllObjects($sql, [Common::QUERY => $_query], self::CLASS_NAME);
        if (count($queries) == 0) {
            $query = self::byQuery($_query);
            if (is_object($query)) {
                if (self::searchCorrespondence($_query, $query)) {
                    return $queries;
                }
                return null;
            }
            $queries = self::all();
        }
        $shortest = 999;
        foreach ($queries as $query) {
            $input = InteractDefManager::sanitizeQuery($query->getQuery());
            $tags = InteractDefManager::getTagFromQuery($query->getQuery(), $_query);
            if (count($tags) > 0) {
                foreach ($tags as $value) {
                    if ($value == "") {
                        continue (2);
                    }
                }
                $input = str_replace(array_keys($tags), $tags, $input);
            }
            $lev = levenshtein($input, $_query);
            LogHelper::addDebug(LogTarget::INTERACT, 'Je compare : ' . $_query . ' avec ' . $input . ' => ' . $lev);
            if (trim($_query) == trim($input)) {
                $shortest = 0;
                $closest = $query;
                break;
            }
            if ($lev == 0) {
                $shortest = 0;
                $closest = $query;
                break;
            }
            if ($lev <= $shortest || $shortest < 0) {
                $closest = $query;
                $shortest = $lev;
            }
        }
        if ($shortest < 0) {
            LogHelper::addDebug(LogTarget::INTERACT, __('Aucune correspondance trouvée'));
            return null;
        }
        $weigh = [
            1 => ConfigManager::byKey('interact::weigh1'),
            2 => ConfigManager::byKey('interact::weigh2'),
            3 => ConfigManager::byKey('interact::weigh3'),
            4 => ConfigManager::byKey('interact::weigh4')];

        foreach (str_word_count($_query, 1) as $word) {
            if (isset($weigh[strlen($word)])) {
                $value = intval($weigh[strlen($word)]);
                $shortest += $value;
            }
        }
        if (str_word_count($_query) == 1 && ConfigManager::byKey('interact::confidence1') > 0 && $shortest > ConfigManager::byKey('interact::confidence1')) {
            LogHelper::addDebug(LogTarget::INTERACT, __('Correspondance trop éloigné : ') . $shortest);
            return null;
        } else if (str_word_count($_query) == 2 && ConfigManager::byKey('interact::confidence2') > 0 && $shortest > ConfigManager::byKey('interact::confidence2')) {
            LogHelper::addDebug(LogTarget::INTERACT, __('Correspondance trop éloigné : ') . $shortest);
            return null;
        } else if (str_word_count($_query) == 3 && ConfigManager::byKey('interact::confidence3') > 0 && $shortest > ConfigManager::byKey('interact::confidence3')) {
            LogHelper::addDebug(LogTarget::INTERACT, __('Correspondance trop éloigné : ') . $shortest);
            return null;
        } else if (str_word_count($_query) > 3 && ConfigManager::byKey('interact::confidence') > 0 && $shortest > ConfigManager::byKey('interact::confidence')) {
            LogHelper::addDebug(LogTarget::INTERACT, __('Correspondance trop éloigné : ') . $shortest);
            return null;
        }
        if (!is_object($closest)) {
            LogHelper::addDebug(LogTarget::INTERACT, __('Aucune phrase trouvée'));
            return null;
        }
        $interactDef = $closest->getInteractDef();
        if ($interactDef->getOptions('mustcontain') != '' && !preg_match($interactDef->getOptions('mustcontain'), $_query)) {
            LogHelper::addDebug(LogTarget::INTERACT, __('Correspondance trouvée : ') . $closest->getQuery() . __(' mais ne contient pas : ') . InteractDefManager::sanitizeQuery($interactDef->getOptions('mustcontain')));
            return null;
        }
        LogHelper::addDebug(LogTarget::INTERACT, __('J\'ai une correspondance  : ') . $closest->getQuery() . __(' avec ') . $shortest);
        return $closest;
    }

    /**
     * @param $_query
     * @param null $_interactDef_id
     * @param bool $caseSensitive Set to false for insensitive case comparaison
     * @return InteractQuery
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byQuery($_query, $_interactDef_id = null, $caseSensitive = true)
    {
        $values = [
            Common::QUERY => $_query,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME;
        if ($caseSensitive) {
            $sql .= ' WHERE query=:query';
        } else {
            $sql .= ' WHERE LOWER(query)=LOWER(:query)';
        }
        if ($_interactDef_id !== null) {
            $values['interactDef_id'] = $_interactDef_id;
            $sql .= ' AND interactDef_id=:interactDef_id';
        }
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param string $baseQuery
     * @param InteractQuery $interactQuery
     * @return bool
     * @throws \Exception
     */
    private static function searchCorrespondence($baseQuery, $interactQuery)
    {
        $interactDef = $interactQuery->getInteractDef();
        if ($interactDef->getOptions('mustcontain') != '' && !preg_match($interactDef->getOptions('mustcontain'), $baseQuery)) {
            LogHelper::addDebug(LogTarget::INTERACT, __('Correspondance trouvée : ') . $interactQuery->getQuery() . __(' mais ne contient pas : ') . InteractDefManager::sanitizeQuery($interactDef->getOptions('mustcontain')));
            return false;
        }
        return true;
    }

    /**
     * @return InteractQuery|null
     * @throws \Exception
     */
    public static function all()
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                ORDER BY id';
        return DBHelper::getAllObjects($sql, [], self::CLASS_NAME);
    }

    /**
     * @param $_parameters
     * @return mixed
     * @throws \Exception
     */
    public static function dontUnderstand($_parameters)
    {
        $notUnderstood = [
            __('Désolé je n\'ai pas compris'),
            __('Désolé je n\'ai pas compris la demande'),
            __('Désolé je ne comprends pas la demande'),
            __('Je ne comprends pas'),
        ];
        if (isset($_parameters[Common::PROFILE])) {
            $notUnderstood[] = __('Désolé ') . $_parameters[Common::PROFILE] . __(' je n\'ai pas compris');
            $notUnderstood[] = __('Désolé ') . $_parameters[Common::PROFILE] . __(' je n\'ai pas compris ta demande');
        }
        $random = rand(0, count($notUnderstood) - 1);
        return $notUnderstood[$random];
    }

    /**
     * @param $_query
     * @param $_parameters
     * @return string
     */
    public static function brainReply($_query, $_parameters)
    {
        global $PROFILE;
        $PROFILE = '';
        if (isset($_parameters[Common::PROFILE])) {
            $PROFILE = $_parameters[Common::PROFILE];
        }
        require_once NEXTDOM_DATA . '/config/bot.config.php';
        global $BRAINREPLY;
        $shortest = 999;
        foreach ($BRAINREPLY as $word => $response) {
            $lev = levenshtein(strtolower($_query), strtolower($word));
            if ($lev == 0) {
                $closest = $word;
                $shortest = 0;
                break;
            }
            if ($lev <= $shortest || $shortest < 0) {
                $closest = $word;
                $shortest = $lev;
            }
        }
        if (isset($closest) && is_array($BRAINREPLY[$closest])) {
            $random = rand(0, count($BRAINREPLY[$closest]) - 1);
            return $BRAINREPLY[$closest][$random];
        }
        return '';
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function replyOk()
    {
        $reply = [
            __('C\'est fait'),
            __('Ok'),
            __('Voila, c\'est fait'),
            __('Bien compris'),
        ];
        $random = rand(0, count($reply) - 1);
        return $reply[$random];
    }

    /**
     * @param $_params
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function doIn($_params)
    {
        $interactQuery = self::byId($_params['interactQuery_id']);
        if (!is_object($interactQuery)) {
            return;
        }
        $_params['execNow'] = 1;
        $interactQuery->executeAndReply($_params);
    }

    /**
     * @param $_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byId($_id)
    {
        $values = [
            'id' => $_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id=:id';

        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }
}
