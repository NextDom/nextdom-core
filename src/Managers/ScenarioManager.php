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

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Managers;

use NextDom\Enums\ScenarioState;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Model\Entity\Scenario;

// @TODO: DBHelper::buildField(ScenarioEntity::className) à factoriser

/**
 * Class ScenarioManager
 * @package NextDom\Managers
 */
class ScenarioManager
{
    const DB_CLASS_NAME = 'scenario';
    const CLASS_NAME = Scenario::class;
    const INITIAL_TRANSLATION_FILE = '';

    /**
     * Get scenario by his name
     *
     * @param string $name Name of the scenario
     *
     * @return Scenario|null Requested scenario or null
     *
     * @throws \Exception
     */
    public static function byName(string $name)
    {
        $values = ['name' => $name];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . ' FROM ' . self::DB_CLASS_NAME . ' WHERE name = :name';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * Obtenir un objet scenario
     *
     * @param string $scenarioName Chaine identifiant le scénario
     *
     * @param $commandNotFoundString
     * @return Scenario Objet demandé
     *
     * @throws \ReflectionException
     * @throws CoreException
     */
    public static function byString(string $scenarioName, $commandNotFoundString)
    {
        $scenario = self::byId(str_replace('#scenario', '', self::fromHumanReadable($scenarioName)));
        if (!is_object($scenario)) {
            throw new CoreException($commandNotFoundString . $scenarioName . ' => ' . self::fromHumanReadable($scenarioName));
        }
        return $scenario;
    }

    /**
     * Get scenario by his id
     *
     * @param int $id Identifiant du scénario
     *
     * @return Scenario Objet demandé
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        $values = ['id' => $id];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . ' FROM ' . self::DB_CLASS_NAME . ' WHERE id = :id';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * @TODO: Ca fait l'inverse, mais je sais pas quoi
     *
     * @param $input
     * @return array|mixed
     * @throws \ReflectionException
     */
    public static function fromHumanReadable($input)
    {
        $isJson = false;
        if (Utils::isJson($input)) {
            $isJson = true;
            $input = json_decode($input, true);
        }
        if (is_object($input)) {
            $reflections = [];
            $uuid = spl_object_hash($input);
            if (!isset($reflections[$uuid])) {
                $reflections[$uuid] = new \ReflectionClass($input);
            }
            $reflection = $reflections[$uuid];
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $value = $property->getValue($input);
                $property->setValue($input, self::fromHumanReadable($value));
                $property->setAccessible(false);
            }
            return $input;
        }
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::fromHumanReadable($value);
            }
            if ($isJson) {
                return json_encode($input, JSON_UNESCAPED_UNICODE);
            }
            return $input;
        }
        $text = $input;

        preg_match_all("/#\[(.*?)\]\[(.*?)\]\[(.*?)\]#/", $text, $matches);
        if (count($matches) == 4) {
            $countMatches = count($matches[0]);
            for ($i = 0; $i < $countMatches; $i++) {
                if (isset($matches[1][$i]) && isset($matches[2][$i]) && isset($matches[3][$i])) {
                    $scenario = self::byObjectNameGroupNameScenarioName($matches[1][$i], $matches[2][$i], $matches[3][$i]);
                    if (is_object($scenario)) {
                        $text = str_replace($matches[0][$i], '#scenario' . $scenario->getId() . '#', $text);
                    }
                }
            }
        }

        return $text;
    }

    /**
     * Liste des scénario triés par objet, group et nom du scénario
     *
     * @param $objectName
     * @param $groupName
     * @param $scenarioName
     *
     * @return mixed
     * @throws \Exception
     */
    public static function byObjectNameGroupNameScenarioName($objectName, $groupName, $scenarioName)
    {
        $values = [
            'scenario_name' => html_entity_decode($scenarioName),
        ];

        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 's') . '
                FROM ' . self::DB_CLASS_NAME . ' s ';

        if ($objectName == __('Aucun')) {
            $sql .= 'WHERE s.name=:scenario_name ';
            if ($groupName == __('Aucun')) {
                $sql .= 'AND (`group` IS NULL OR `group` = ""  OR `group` = "Aucun" OR `group` = "None")
                         AND s.object_id IS NULL';
            } else {
                $values['group_name'] = $groupName;
                $sql .= 'AND s.object_id IS NULL
                         AND `group` = :group_name';
            }
        } else {
            $values['object_name'] = $objectName;
            $sql .= 'INNER JOIN object ob ON s.object_id=ob.id
                     WHERE s.name = :scenario_name
                     AND ob.name = :object_name ';
            if ($groupName == __('Aucun')) {
                $sql .= 'AND (`group` IS NULL OR `group` = ""  OR `group` = "Aucun" OR `group` = "None")';
            } else {
                $values['group_name'] = $groupName;
                $sql .= 'AND `group` = :group_name';
            }
        }
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     *  Obtenir la liste des groupes de scénarios
     *
     * @param string $groupPattern Pattern de recherche
     *
     * @return array|mixed|null [] Liste des groupes
     * @throws \Exception
     */
    public static function listGroup($groupPattern = null)
    {
        $values = [];
        $sql = 'SELECT DISTINCT(`group`)
        FROM ' . self::DB_CLASS_NAME;
        if ($groupPattern !== null) {
            $values['group'] = '%' . $groupPattern . '%';
            $sql .= ' WHERE `group` LIKE :group';
        }
        $sql .= ' ORDER BY `group`';
        return DBHelper::getAll($sql, $values);
    }

    /**
     * Obtenir la liste des scénarios à partir d'un élément @TODO: Kesako
     *
     * @param string $elementId
     * @return mixed
     * @throws \Exception
     */
    public static function byElement(string $elementId)
    {
        // TODO: Vérifier, bizarre les guillemets dans le like
        $values = [
            'element_id' => '%"' . $elementId . '"%',
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE `scenarioElement` LIKE :element_id';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * Obtenir un scénario à partir de l'identifiant d'un objet //@TODO: Comprendre ce que c'est
     *
     * @param int $objectId Identifiant de l'objet
     * @param bool $onlyEnabled Filtrer uniquement les scénarios activés
     * @param bool $onlyVisible Filtrer uniquement les scénarios visibles
     *
     * @return array|mixed|null [] Liste des scénarios
     *
     * @throws \Exception
     */
    public static function byObjectId($objectId, $onlyEnabled = true, $onlyVisible = false)
    {
        $values = [];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME;
        if ($objectId === null) {
            $sql .= ' WHERE object_id IS NULL';
        } else {
            $values['object_id'] = $objectId;
            $sql .= ' WHERE object_id = :object_id';
        }
        if ($onlyEnabled) {
            $sql .= ' AND isActive = 1';
        }
        if ($onlyVisible) {
            $sql .= ' AND isVisible = 1';
        }
        $sql .= ' ORDER BY `order`';
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    /**
     * Vérifier un scénario
     * @TODO: Virer les strings
     *
     * @param string $event Evènement déclencheur
     * @param bool $forceSyncMode Forcer le mode synchrone
     *
     * @return bool Renvoie toujours true //@TODO: A voir
     * @throws \Exception
     */
    public static function check($event = null, $forceSyncMode = false)
    {
        $message = '';
        $scenarios = [];
        if ($event !== null) {
            // @TODO: Event ne peut pas être un objet
            if (is_object($event)) {
                $eventScenarios = self::byTrigger($event->getId());
                $trigger = '#' . $event->getId() . '#';
                $message = __('Scénario exécuté automatiquement sur événement venant de : ') . $event->getHumanName();
            } else {
                $eventScenarios = self::byTrigger($event);
                $trigger = $event;
                $message = __('Scénario exécuté sur événement : #') . $event . '#';
            }
            if (is_array($eventScenarios) && count($eventScenarios) > 0) {
                foreach ($eventScenarios as $scenario) {
                    if ($scenario->testTrigger($trigger)) {
                        $scenarios[] = $scenario;
                    }
                }
            }
        } else {
            $message = __('Scénario exécuté automatiquement sur programmation');
            $scheduledScenarios = self::schedule();
            $trigger = 'schedule';
            if (NextDomHelper::isDateOk()) {
                foreach ($scheduledScenarios as $scenario) {
                    if ($scenario->getState() != ScenarioState::IN_PROGRESS && $scenario->isDue()) {
                        $scenarios[] = $scenario;
                    }
                }
            }
        }
        if (count($scenarios) > 0) {
            foreach ($scenarios as $scenario) {
                $scenario->launch($trigger, $message, $forceSyncMode);
            }
        }
        return true;
    }

    /**
     * Obtenir la liste des scénarios en fonction d'un déclencheur
     *
     * @param string $cmdId Identifiant du déclencheur
     * @param bool $onlyEnabled Filtrer sur les scénarios activés
     *
     * @return array|mixed|null [] Liste des scénarios
     * @throws \Exception
     */
    public static function byTrigger($cmdId, $onlyEnabled = true)
    {
        $values = ['cmd_id' => '%#' . $cmdId . '#%'];
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE mode != "schedule" AND `trigger` LIKE :cmd_id';
        if ($onlyEnabled) {
            $sql .= ' AND isActive = 1';
        }
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    /**
     * Obtenir la liste des scénarios planifiés
     *
     * @return array|mixed|null [scenario] Liste des scénarios planifiés
     * @throws \Exception
     */
    public static function schedule()
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE `mode` != "provoke"
                AND isActive = 1';
        return DBHelper::getAllObjects($sql, [], self::CLASS_NAME);
    }

    /**
     * Contrôle des scénarios // @TODO: ???
     *
     */
    public static function control()
    {
        foreach (self::all() as $scenario) {
            if ($scenario->getState() != ScenarioState::IN_PROGRESS) {
                continue; // @TODO: To be or not to be
            }
            if (!$scenario->running()) {
                $scenario->setState(ScenarioState::ERROR);
                continue; // @TODO: To be or not to be
            }
            $runtime = strtotime('now') - strtotime($scenario->getLastLaunch());
            // @TODO: Optimisation
            if (is_numeric($scenario->getTimeout()) && $scenario->getTimeout() != '' && $scenario->getTimeout() != 0 && $runtime > $scenario->getTimeout()) {
                $scenario->stop();
                $scenario->setLog(__('Arret du scénario car il a dépassé son temps de timeout : ') . $scenario->getTimeout() . 's');
                $scenario->persistLog();
            }
        }
    }

    /**
     * Obtenir tous les objets scenario
     *
     * @param string $groupName Filtrer sur un groupe
     * @param string $type Filtrer sur un type
     *
     * @return  Scenario[] Liste des objets scenario
     * @throws \Exception
     */
    public static function all($groupName = '', $type = null): array
    {
        $values = [];
        $result1 = null;
        $result2 = null;

        $baseSql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 's') . 'FROM ' . self::DB_CLASS_NAME . ' s ';
        $sqlWhereTypeFilter = ' ';
        $sqlAndTypeFilter = ' ';
        if ($type !== null) {
            $sqlWhereTypeFilter = ' WHERE `type` = :type ';
            $sqlAndTypeFilter = ' AND `type` = :type ';
            $values['type'] = $type;
        }

        $sql1 = $baseSql . 'INNER JOIN object ob ON s.object_id = ob.id ';
        if ($groupName === '') {
            $sql1 .= $sqlWhereTypeFilter . 'ORDER BY ob.name, s.group, s.name';
            $sql2 = $baseSql . 'WHERE s.object_id IS NULL' . $sqlAndTypeFilter . 'ORDER BY s.group, s.name';
        } elseif ($groupName === null) {
            $sql1 .= 'WHERE (`group` IS NULL OR `group` = "")' . $sqlAndTypeFilter . 'ORDER BY s.group, s.name';
            $sql2 = $baseSql . 'WHERE (`group` IS NULL OR `group` = "") AND s.object_id IS NULL' . $sqlAndTypeFilter . ' ORDER BY s.name';
        } else {
            $values = ['group' => $groupName];
            $sql1 .= 'WHERE `group` = :group ' . $sqlAndTypeFilter . 'ORDER BY ob.name, s.group, s.name';
            $sql2 = $baseSql . 'WHERE `group` = :group AND s.object_id IS NULL' . $sqlAndTypeFilter . 'ORDER BY s.group, s.name';
        }
        $result1 = DBHelper::getAllObjects($sql1, $values, self::CLASS_NAME);
        $result2 = DBHelper::getAllObjects($sql2, $values, self::CLASS_NAME);
        if (!is_array($result1)) {
            $result1 = [];
        }
        if (!is_array($result2)) {
            $result2 = [];
        }
        return array_merge($result1, $result2);
    }

    /**
     * Fait dedans ??? @TODO: Trouver un nom explicite
     *
     * @param array $options ???
     *
     * @throws \Exception
     */
    public static function doIn(array $options)
    {
        $scenario = self::byId($options['scenario_id']);
        if (is_object($scenario)) {
            if ($scenario->getIsActive() == 0) {
                $scenario->setLog(__('Scénario désactivé non lancement de la sous tâche'));
                $scenario->persistLog();
            } else {
                $scenarioElement = ScenarioElementManager::byId($options['scenarioElement_id']);
                $scenario->setLog(__('************Lancement sous tâche**************'));
                if (isset($options['tags']) && is_array($options['tags']) && count($options['tags']) > 0) {
                    $scenario->setTags($options['tags']);
                    $scenario->setLog(__('Tags : ') . json_encode($scenario->getTags()));
                }
                if (is_object($scenarioElement)) {
                    if (is_numeric($options['second']) && $options['second'] > 0) {
                        sleep($options['second']);
                    }
                    $scenarioElement->getSubElement('do')->execute($scenario);
                    $scenario->setLog(__('************FIN sous tâche**************'));
                    $scenario->persistLog();
                }
            }
        }
    }

    /**
     * Nettoie la table @TODO: Avec l'éponge et grosse optimisation à faire
     */
    public static function cleanTable()
    {
        $ids = [
            'element' => [],
            'subelement' => [],
            'expression' => [],
        ];
        foreach (self::all() as $scenario) {
            foreach ($scenario->getElement() as $element) {
                $result = $element->getAllId();
                $ids['element'] = array_merge($ids['element'], $result['element']);
                $ids['subelement'] = array_merge($ids['subelement'], $result['subelement']);
                $ids['expression'] = array_merge($ids['expression'], $result['expression']);
            }
        }

        $tablesToClean = [
            'scenarioExpression' => 'expression',
            'scenarioSubElement' => 'subelement',
            'scenarioElement' => 'element'
        ];

        foreach ($tablesToClean as $table => $item) {
            $sql = 'DELETE FROM ' . $table . ' WHERE id NOT IN (-1';
            foreach ($ids[$item] as $expressionId) {
                $sql .= ',' . $expressionId;
            }
            $sql .= ')';
            DBHelper::getAll($sql);
        }
    }

    /**
     * Test la validité des scénarios @TODO: Je suppose
     *
     * @param bool $needsReturn Argument à virer
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function consystencyCheck($needsReturn = false)
    {
        $return = [];
        foreach (self::all() as $scenario) {
            if ($scenario->getIsActive() != 1 && !$needsReturn) {
                continue;
            }
            if ($scenario->getMode() == 'provoke' || $scenario->getMode() == 'all') {
                $trigger_list = '';
                foreach ($scenario->getTrigger() as $trigger) {
                    $trigger_list .= CmdManager::cmdToHumanReadable($trigger) . '_';
                }
                preg_match_all("/#([0-9]*)#/", $trigger_list, $matches);
                foreach ($matches[1] as $cmd_id) {
                    if (is_numeric($cmd_id)) {
                        if ($needsReturn) {
                            $return[] = ['detail' => 'Scénario ' . $scenario->getHumanName(), 'help' => 'Déclencheur du scénario', 'who' => '#' . $cmd_id . '#'];
                        } else {
                            LogHelper::addError('scenario', __('Un déclencheur du scénario : ') . $scenario->getHumanName() . __(' est introuvable'));
                        }
                    }
                }
            }
            $expression_list = '';
            foreach ($scenario->getElement() as $element) {
                $expression_list .= CmdManager::cmdToHumanReadable(json_encode($element->getAjaxElement()));
            }
            preg_match_all("/#([0-9]*)#/", $expression_list, $matches);
            foreach ($matches[1] as $cmd_id) {
                if (is_numeric($cmd_id)) {
                    if ($needsReturn) {
                        $return[] = ['detail' => 'Scénario ' . $scenario->getHumanName(), 'help' => 'Utilisé dans le scénario', 'who' => '#' . $cmd_id . '#'];
                    } else {
                        LogHelper::addError('scenario', __('Une commande du scénario : ') . $scenario->getHumanName() . __(' est introuvable'));
                    }
                }
            }
        }
        if ($needsReturn) {
            return $return;
        }
        return null;
    }

    /**
     * /@TODO: Fatigué d'essayer de comprendre à quoi ça sert
     * Méthode appelée de façon recursive
     *
     * @param $input /@TODO: Ca entre en effect
     *
     * @return mixed @TODO: Quelque chose de lisible à priori
     *
     * @throws \Exception
     */
    public static function toHumanReadable($input)
    {
        if (is_object($input)) {
            $reflections = [];
            $uuid = spl_object_hash($input);
            if (!isset($reflections[$uuid])) {
                $reflections[$uuid] = new \ReflectionClass($input);
            }
            $reflection = $reflections[$uuid];
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $value = $property->getValue($input);
                $property->setValue($input, self::toHumanReadable($value));
                $property->setAccessible(false);
            }
            return $input;
        }
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::toHumanReadable($value);
            }
            return $input;
        }
        $text = $input;
        preg_match_all("/#scenario([0-9]*)#/", $text, $matches);
        foreach ($matches[1] as $scenario_id) {
            if (is_numeric($scenario_id)) {
                $scenario = self::byId($scenario_id);
                if (is_object($scenario)) {
                    $text = str_replace('#scenario' . $scenario_id . '#', '#' . $scenario->getHumanName(true) . '#', $text);
                }
            }
        }
        return $text;
    }

    /**
     * @TODO:
     * @param array $searchs
     * @return array
     * @throws \Exception
     */
    public static function searchByUse(array $searchs)
    {
        $return = [];
        $expressions = [];
        $scenarios = [];
        foreach ($searchs as $search) {
            $_cmd_id = str_replace('#', '', $search['action']);
            $return = array_merge($return, self::byTrigger($_cmd_id, false));
            if (!isset($search['and'])) {
                $search['and'] = false;
            }
            if (!isset($search['option'])) {
                $search['option'] = $search['action'];
            }
            $expressions = array_merge($expressions, ScenarioExpressionManager::searchExpression($search['action'], $search['option'], $search['and']));
        }
        if (is_array($expressions) && count($expressions) > 0) {
            foreach ($expressions as $expression) {
                $scenarios[] = $expression->getSubElement()->getElement()->getScenario();
            }
        }
        if (is_array($scenarios) && count($scenarios) > 0) {
            foreach ($scenarios as $scenario) {
                if (is_object($scenario)) {
                    $find = false;
                    foreach ($return as $existScenario) {
                        if ($scenario->getId() == $existScenario->getId()) {
                            $find = true;
                            break;
                        }
                    }
                    if (!$find) {
                        $return[] = $scenario;
                    }
                }
            }
        }
        return $return;
    }

    /**
     *@TODO: PATH pointe vers rien
     *
     * @param string $template
     *
     * @return mixed
     */
    public static function getTemplate($template = '')
    {
        $path = NEXTDOM_DATA . '/data/scenario';
        /**
         * if (isset($template) && $template != '') {
         * // @TODO Magic trixxxxxx
         * }
         */
        return FileSystemHelper::ls($path, '*.json', false, ['files', 'quiet']);
    }

    /**
     * @TODO:
     *
     * @param $market
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function shareOnMarket(&$market)
    {
        $moduleFile = NEXTDOM_DATA . '/data/scenario/' . $market->getLogicalId() . '.json';
        if (!file_exists($moduleFile)) {
            throw new CoreException('Impossible de trouver le fichier de configuration ' . $moduleFile);
        }
        $tmp = NextDomHelper::getTmpFolder('market') . '/' . $market->getLogicalId() . '.zip';
        if (file_exists($tmp)) {
            if (!unlink($tmp)) {
                throw new CoreException(__('Impossible de supprimer : ') . $tmp . __('. Vérifiez les droits'));
            }
        }
        if (!FileSystemHelper::createZip($moduleFile, $tmp)) {
            throw new CoreException(__('Echec de création du zip. Répertoire source : ') . $moduleFile . __(' / Répertoire cible : ') . $tmp);
        }
        return $tmp;
    }

    /**
     * @TODO:
     * @param mixed $market
     * @param mixed $path
     * @throws \Exception
     */
    public static function getFromMarket(&$market, $path)
    {
        $cibDir = NEXTDOM_DATA . '/data/scenario/';
        if (!file_exists($cibDir)) {
            mkdir($cibDir);
        }
        $zip = new \ZipArchive;
        if ($zip->open($path) === true) {
            $zip->extractTo($cibDir . '/');
            $zip->close();
        } else {
            throw new CoreException('Impossible de décompresser l\'archive zip : ' . $path);
        }
    }

    /**
     * @TODO: Trixxxxxx
     * @return array
     */
    public static function listMarketObject()
    {
        return [];
    }

    /**
     * @TODO: Le CSS C'est pour les faibles
     * @param array $event
     * @return array|null
     * @throws \Exception
     */
    public static function timelineDisplay(array $event)
    {
        $return = [];
        $return['date'] = $event['datetime'];
        $return['group'] = 'scenario';
        $return['type'] = $event['type'];
        $scenario = self::byId($event['id']);
        if (!is_object($scenario)) {
            return null;
        }
        $linkedObject = $scenario->getObject();
        $return['object'] = is_object($linkedObject) ? $linkedObject->getId() : 'aucun';
        $return['html'] = '<div class="timeline-item cmd" data-id="' . $event['id'] . '">'
            . '<span class="time"><i class="fa fa-clock-o"></i>' . substr($event['datetime'], -9) . '</span>'
            . '<h3 class="timeline-header">' . $event['name'] . '</h3>'
            . '<div class="timeline-body">'
            . 'Déclenché par ' . $event['trigger']
            . ' <div class="timeline-footer">'
            . '</div>'
            . '</div>';
        return $return;
    }
}
