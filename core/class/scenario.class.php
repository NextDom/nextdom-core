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

/*
Chemin d'un scénario
scenario->launch(trigger, message, forceSyncMode);
 - Test si le scénario est activé
 - Si mode syncmode
   - scenario->execute(trigger, message)
   Sinon
     - Fait un truc avec les tags
     - lancement en mode asynchrone avec jeeScenario en ligne de commande scenarioId, trigger, message

scenario->execute(trigger, message)
 - Fait un truc avec les tags
 - Test si le scenario est actif
 - Vérifie la date !!!! Peut amener un délai de 3s
 - Récupère la commande du trigger
 - Fait des trucs et des bidules avec une histoire de timeline
 - Fait des trucs encore plus bizarres
 - Boucle sur les éléments
   - Appel récursif à cette commande !!!! et recheck tout le merdier
   - Break si $this->getDo() sur l'élément
 - Fait encore un truc bizarre avec le PID
 */


use NextDom\Managers\ScenarioManager;
use NextDom\Managers\ScenarioElementManager;
use NextDom\Managers\EqLogicManager;

/* * ***************************Includes********************************* */
require_once NEXTDOM_ROOT . '/core/php/core.inc.php';

class scenario extends \NextDom\Model\Entity\Scenario
{
    /**
     * Renvoie un objet scenario
     * @param int $_id id du scenario voulu
     * @return scenario object scenario
     */
    public static function byId($_id)
    {
        return ScenarioManager::byId($_id);
    }

    public static function byString($_string)
    {
        return ScenarioManager::byString($_string, __('La commande n\'a pas pu être trouvée : ', __FILE__));
    }

    /**
     * Renvoie tous les objets scenario
     * @return [] scenario object scenario
     */
    public static function all($_group = '', $_type = null)
    {
        return ScenarioManager::all($_group, $_type);
    }

    /**
     *
     * @return type
     */
    public static function schedule()
    {
        return ScenarioManager::schedule();
    }

    /**
     *
     * @param type $_group
     * @return type
     */
    public static function listGroup($_group = null)
    {
        return ScenarioManager::listGroup($_group);
    }

    /**
     *
     * @param type $_cmd_id
     * @return type
     */
    public static function byTrigger($_cmd_id, $_onlyEnable = true)
    {
        return ScenarioManager::byTrigger($_cmd_id, $_onlyEnable);
    }

    /**
     *
     * @param type $_element_id
     * @return type
     */
    public static function byElement($_element_id)
    {
        return ScenarioManager::byElement($_element_id);
    }

    /**
     *
     * @param type $_object_id
     * @param type $_onlyEnable
     * @param type $_onlyVisible
     * @return type
     */
    public static function byObjectId($_object_id, $_onlyEnable = true, $_onlyVisible = false)
    {
        return ScenarioManager::byObjectId($_object_id, $_onlyEnable, $_onlyVisible);
    }

    /**
     *
     * @param type $_event
     * @param type $_forceSyncMode
     * @return boolean
     */
    public static function check($_event = null, $_forceSyncMode = false)
    {
        return ScenarioManager::check($_event, $_forceSyncMode);
    }

    public static function control()
    {
        ScenarioManager::control();
    }

    /**
     *
     * @param array $_options
     * @return type
     */
    public static function doIn($_options)
    {
        ScenarioManager::doIn($_options, __FILE__);
    }

    /**
     *
     */
    public static function cleanTable()
    {
        ScenarioManager::cleanTable();
    }

    /**
     *
     */
    public static function consystencyCheck($_needsReturn = false)
    {
        ScenarioManager::consystencyCheck($_needsReturn);
    }

    /**
     * @name byObjectNameGroupNameScenarioName()
     * @param object $_object_name
     * @param type $_group_name
     * @param type $_scenario_name
     * @return type
     */
    public static function byObjectNameGroupNameScenarioName($_object_name, $_group_name, $_scenario_name)
    {
        ScenarioManager::byObjectNameGroupNameScenarioName($_object_name, $_group_name, $_scenario_name);
    }

    /**
     * @name toHumanReadable()
     * @param object $_input
     * @return string
     */
    public static function toHumanReadable($_input)
    {
        return ScenarioManager::toHumanReadable($_input);
    }

    /**
     *
     * @param mixed $_input
     * @return mixed
     */
    public static function fromHumanReadable($_input)
    {
        return ScenarioManager::fromHumanReadable($_input);
    }

    /**
     *
     * @param mixed $searchs
     * @return array
     */
    public static function searchByUse($searchs)
    {
        return ScenarioManager::searchByUse($searchs);
    }

    /**
     *
     * @param type $_template
     * @return type
     */
    public static function getTemplate($_template = '')
    {
        return ScenarioManager::getTemplate($_template);
    }

    public static function shareOnMarket(&$market)
    {
        return ScenarioManager::shareOnMarket($market);
    }

    /**
     *
     * @param mixed $market
     * @param string $_path
     * @throws Exception
     */
    public static function getFromMarket(&$market, $_path)
    {
        ScenarioManager::getFromMarket($market, $_path);
    }

    public static function removeFromMarket(&$market)
    {
        trigger_error('This method is deprecated', E_USER_DEPRECATED);
    }

    public static function listMarketObject()
    {
        return ScenarioManager::listMarketObject();
    }

    public static function timelineDisplay($_event)
    {
        return ScenarioManager::timelineDisplay($_event);
    }

}
