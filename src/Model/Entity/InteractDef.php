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
use NextDom\Enums\CmdType;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\InteractQueryManager;
use NextDom\Managers\JeeObjectManager;

/**
 * Interactdef
 *
 * @ORM\Table(name="interactDef")
 * @ORM\Entity
 */
class InteractDef implements EntityInterface
{

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="enable", type="integer", nullable=true)
     */
    protected $enable;

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="text", length=65535, nullable=true)
     */
    protected $query;

    /**
     * @var string
     *
     * @ORM\Column(name="reply", type="text", length=65535, nullable=true)
     */
    protected $reply;

    /**
     * @var string
     *
     * @ORM\Column(name="person", type="string", length=255, nullable=true)
     */
    protected $person;

    /**
     * @var string
     *
     * @ORM\Column(name="options", type="text", length=65535, nullable=true)
     */
    protected $options;

    /**
     * @var string
     *
     * @ORM\Column(name="filtres", type="text", length=65535, nullable=true)
     */
    protected $filtres;

    /**
     * @var string
     *
     * @ORM\Column(name="group", type="string", length=127, nullable=true)
     */
    protected $group;

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
     * @return mixed
     */
    public function selectReply()
    {
        $replies = InteractDefManager::generateTextVariant($this->getReply());
        $random = rand(0, count($replies) - 1);
        return $replies[$random];
    }

    /**
     * @return string
     */
    public function getReply()
    {
        return $this->reply;
    }

    /**
     * @param $_reply
     * @return $this
     */
    public function setReply($_reply)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->reply, $_reply);
        $this->reply = $_reply;
        return $this;
    }

    public function preInsert()
    {
        if ($this->getReply() == '') {
            $this->setReply('#valeur#');
        }
        $this->setEnable(1);
    }

    public function preSave()
    {
        if ($this->getOptions('allowSyntaxCheck') === '') {
            $this->setOptions('allowSyntaxCheck', 1);
        }
        if ($this->getFiltres('eqLogic_id') == '') {
            $this->setFiltres('eqLogic_id', 'all');
        }
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
     */
    public function setOptions($_key, $_value)
    {
        $options = Utils::setJsonAttr($this->options, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->options, $options);
        $this->options = $options;
        return $this;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getFiltres($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->filtres, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setFiltres($_key, $_value)
    {
        $filtres = Utils::setJsonAttr($this->filtres, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->filtres, $filtres);
        $this->filtres = $filtres;
        return $this;
    }

    /**
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        if ($this->getQuery() == '') {
            throw new CoreException(__('La commande (demande) ne peut pas être vide'));
        }
        DBHelper::save($this);
        return true;
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

    public function postSave()
    {
        $queries = $this->generateQueryVariant();
        InteractQueryManager::removeByInteractDefId($this->getId());
        if ($this->getEnable()) {
            DBHelper::beginTransaction();
            foreach ($queries as $query) {
                $query['query'] = InteractDefManager::sanitizeQuery($query['query']);
                if (trim($query['query']) == '') {
                    continue;
                }
                if (!$this->checkQuery($query['query'])) {
                    continue;
                }
                $interactQuery = new InteractQuery();
                $interactQuery->setInteractDef_id($this->getId());
                $interactQuery->setQuery($query['query']);
                $interactQuery->setActions('cmd', $query['cmd']);
                $interactQuery->save();
            }
            DBHelper::commit();
        }
        InteractDefManager::cleanInteract();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function generateQueryVariant()
    {
        $inputs = InteractDefManager::generateTextVariant($this->getQuery());
        $return = [];
        $object_filter = $this->getFiltres('object');
        $type_filter = $this->getFiltres('type');
        $subtype_filter = $this->getFiltres('subtype');
        $unite_filter = $this->getFiltres('unite');
        $plugin_filter = $this->getFiltres('plugin');
        $visible_filter = $this->getFiltres('visible');
        $category_filter = $this->getFiltres('category');
        foreach ($inputs as $input) {
            preg_match_all("/#(.*?)#/", $input, $matches);
            $matches = $matches[1];
            if (in_array('commande', $matches) || (in_array('objet', $matches) || in_array('equipement', $matches))) {
                foreach (JeeObjectManager::all() as $jeeObject) {
                    if (isset($object_filter[$jeeObject->getId()]) && $object_filter[$jeeObject->getId()] == 0) {
                        continue;
                    }
                    if (isset($visible_filter['object']) && $visible_filter['object'] == 1 && $jeeObject->getIsVisible() != 1) {
                        continue;
                    }
                    foreach ($jeeObject->getEqLogic() as $eqLogic) {
                        if ($this->getFiltres('eqLogic_id', 'all') != 'all' && $eqLogic->getId() != $this->getFiltres('eqLogic_id')) {
                            continue;
                        }
                        if (isset($plugin_filter[$eqLogic->getEqType_name()]) && $plugin_filter[$eqLogic->getEqType_name()] == 0) {
                            continue;
                        }
                        if (isset($visible_filter['eqLogic']) && $visible_filter['eqLogic'] == 1 && !$eqLogic->isVisible()) {
                            continue;
                        }

                        $category_ok = true;
                        if (is_array($category_filter)) {
                            $category_ok = false;
                            foreach ($category_filter as $category => $value) {
                                if ($value == 1) {
                                    if ($eqLogic->getCategory($category) == 1) {
                                        $category_ok = true;
                                        break;
                                    }
                                    if ($category == 'noCategory' && $eqLogic->getPrimaryCategory() == '') {
                                        $category_ok = true;
                                        break;
                                    }
                                }
                            }
                        }
                        if (!$category_ok) {
                            continue;
                        }
                        foreach ($eqLogic->getCmd() as $cmd) {
                            if (isset($visible_filter['cmd']) && $visible_filter['cmd'] == 1 && !$cmd->isVisible()) {
                                continue;
                            }
                            if (isset($subtype_filter[$cmd->getSubType()]) && $subtype_filter[$cmd->getSubType()] == 0) {
                                continue;
                            }
                            if (isset($type_filter[$cmd->getType()]) && $type_filter[$cmd->getType()] == 0) {
                                continue;
                            }
                            if ($cmd->getUnite() == '') {
                                if (isset($unite_filter['none']) && $unite_filter['none'] == 0) {
                                    continue;
                                }
                            } else {
                                if (isset($unite_filter[$cmd->getUnite()]) && $unite_filter[$cmd->getUnite()] == 0) {
                                    continue;
                                }
                            }

                            $replace = [
                                '#objet#' => strtolower($jeeObject->getName()),
                                '#commande#' => strtolower($cmd->getName()),
                                '#equipement#' => strtolower($eqLogic->getName()),
                            ];
                            $options = [];
                            if ($cmd->isType(CmdType::ACTION)) {
                                if ($cmd->isSubType(CmdSubType::COLOR)) {
                                    $options['color'] = '#color#';
                                }
                                if ($cmd->isSubType(CmdSubType::SLIDER)) {
                                    $options['slider'] = '#slider#';
                                }
                                if ($cmd->isSubType(CmdSubType::MESSAGE)) {
                                    $options['message'] = '#message#';
                                    $options['title'] = '#title#';
                                }
                            }
                            $query = str_replace(array_keys($replace), $replace, $input);
                            $return[$query] = [
                                'query' => $query,
                                'cmd' => [['cmd' => '#' . $cmd->getId() . '#', 'options' => $options]],

                            ];
                        }
                    }
                }
            }
        }

        if (count($return) == 0) {
            foreach ($inputs as $input) {
                $return[] = [
                    'query' => $input,
                    'cmd' => $this->getActions('cmd'),
                ];
            }
        }
        if ($this->getOptions('synonymes') != '') {
            $synonymes = [];
            foreach (explode('|', $this->getOptions('synonymes')) as $value) {
                $values = explode('=', $value);
                if (count($values) != 2) {
                    continue;
                }
                $synonymes[InteractDefManager::sanitizeQuery($values[0])] = explode(',', InteractDefManager::sanitizeQuery($values[1]));
            }
            foreach ($return as $query) {
                $results = InteractDefManager::generateSynonymeVariante(InteractDefManager::sanitizeQuery($query['query']), $synonymes);
                if (count($results) == 0) {
                    continue;
                }
                foreach ($results as $result) {
                    $query_info = $query;
                    $query_info['query'] = $result;
                    $return[$result] = $query_info;
                }
            }
        }
        return $return;
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
     * @return int
     */
    public function getEnable()
    {
        return $this->enable;
    }

    /**
     * @param $_enable
     * @return $this
     */
    public function setEnable($_enable)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->enable, $_enable);
        $this->enable = $_enable;
        return $this;
    }

    /**
     * @param $_query
     * @return bool
     * @throws \Exception
     */
    public function checkQuery($_query)
    {
        if ($this->getOptions('allowSyntaxCheck', 1) == 1) {
            $exclude_regexp = "/l'(z|r|t|p|q|s|d|f|g|j|k|l|m|w|x|c|v|b|n|y| )|( |^)la (a|e|u|i|o)|( |^)le (a|e|u|i|o)|( |^)du (a|e|u|i|o)/i";
            if (preg_match($exclude_regexp, $_query)) {
                return false;
            }
            $disallow = [
                'le salle',
                'le chambre',
                'la dressing',
                'la salon',
                'le cuisine',
                'la jours',
                'la total',
                '(le|la) dehors',
                'la balcon',
                'du chambre',
                'du salle',
                'du cuisine',
                'la homecinema',
                'la led',
                'le led',
                'la pc',
                'la sol',
                'la conseil',
                '(la|les) lave\-vaisselle',
                '(la|les) lave\-linge',
                'la sonos',
                '(la|le) humidité',
                'la genre',
                'la résumé',
                'le bouton',
                'la status',
                'la volume',
                'le piste',
                'le consommation',
                'le position',
                'le puissance',
                'le luminosité',
                'le température',
                '(la|les) micro\-onde',
                'la mirroir',
                'la lapin',
                'la greenmomit',
                'le prise',
                'le frigo',
                'le (petite | )lumière',
                'la boutton',
                'la sommeil',
                'la temps',
                'la poids',
                '(la|les) heartbeat',
                '(la|le) heure',
                'la nombre',
                'la coût',
                'la titre',
                'la type',
                'la demain',
                'la pas',
                'la démarré',
                'la relai',
                '(la|le) vacance',
                'la coucher',
                'la lever',
                'la kodi',
                'la frigo',
                'la citronier',
                'la basilique',
                'la plante',
                'la mouvement',
                'la mode',
                'la statut',
                'la dns',
                'la thym',
                'lumière cuisine',
                'lumière salon',
                'lumière chambre',
                'lumière salle de bain',
                'la thumbnail',
                'la bouton',
                'la co',
                'la co2',
                'la répéter',
                '(fait-il|combien) chambre',
                '(fait-il|combien) salon',
                '(fait-il|combien) cuisine',
                '(fait-il|combien) salle',
                '(fait-il|combien) entrée',
                '(fait-il|combien) balcon',
                '(fait-il|combien) appartement',
                'dans le balcon',
                'le calorie',
                'le chansons',
                'le charge',
                'le demain',
                'le démarré',
                'le direction',
                'le distance',
                'le masse',
                'le mémoire',
                'le pr(é|e)sence',
                'le répéter',
                'le taille',
                'le fumée',
                'le pression',
                'le vitesse',
                'le condition',
                'les pc',
                'la tetris',
                'le bougies',
                'le myfox',
                'les homecinema',
                'les kodi',
                'les appartement',
                'le maison',
                'du maison',
                'le buanderie',
                'du buanderie',
                'la bureau',
                'de salon',
                'de maison',
                'de chambre',
                'de cuisine',
                'de espace',
                'de salle de bain',
                '(dans|quelqu\'un) entr(é|e)e',
            ];
            if (preg_match('/( |^)' . implode('( |$)|( |^)', $disallow) . '( |$)/i', $_query)) {
                return false;
            }
        }
        if ($this->getOptions('exclude_regexp') != '' && preg_match($this->getOptions('exclude_regexp'), $_query)) {
            return false;
        }
        if (ConfigManager::byKey('interact::regexpExcludGlobal') != '' && preg_match(ConfigManager::byKey('interact::regexpExcludGlobal'), $_query)) {
            return false;
        }
        return true;
    }

    public function remove()
    {
        DBHelper::remove($this);
    }

    public function preRemove()
    {
        InteractQueryManager::removeByInteractDefId($this->getId());
    }

    public function postRemove()
    {
        InteractDefManager::cleanInteract();
    }

    /**
     * @return string
     */
    public function getLinkToConfiguration()
    {
        return 'index.php?v=d&p=interact&id=' . $this->getId();
    }

    /**
     * @param array $_data
     * @param int $_level
     * @param int $_drill
     * @return array|null
     */
    public function getLinkData(&$_data = ['node' => [], 'link' => []], $_level = 0, $_drill = 3)
    {
        if (isset($_data['node']['interactDef' . $this->getId()])) {
            return null;
        }
        $_level++;
        if ($_level > $_drill) {
            return $_data;
        }
        $icon = Utils::findCodeIcon('fa-comments-o');
        $_data['node']['interactDef' . $this->getId()] = [
            'id' => 'interactDef' . $this->getId(),
            'name' => substr($this->getHumanName(), 0, 20),
            'icon' => $icon['icon'],
            'fontfamily' => $icon['fontfamily'],
            'fontsize' => '1.5em',
            'fontweight' => ($_level == 1) ? 'bold' : 'normal',
            'texty' => -14,
            'textx' => 0,
            'title' => $this->getHumanName(),
            'url' => 'index.php?v=d&p=interact&id=' . $this->getId(),
        ];
        return null;
    }

    /**
     * @return string
     */
    /**
     * @return string
     */
    /**
     * @return string
     */
    public function getHumanName()
    {
        if ($this->getName() != '') {
            return $this->getName();
        }
        return $this->getQuery();
    }

    /**
     * @return string
     */
    /**
     * @return string
     */
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $_name
     * @return $this
     */
    /**
     * @param $_name
     * @return $this
     */
    /**
     * @param $_name
     * @return $this
     */
    public function setName($_name)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->name, $_name);
        $this->name = $_name;
        return $this;
    }

    /**
     * @return string
     */
    /**
     * @return string
     */
    /**
     * @return string
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param $_person
     * @return $this
     */
    /**
     * @param $_person
     * @return $this
     */
    /**
     * @param $_person
     * @return $this
     */
    public function setPerson($_person)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->person, $_person);
        $this->person = $_person;
        return $this;
    }

    /**
     * @return string
     */
    /**
     * @return string
     */
    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param $_group
     * @return $this
     */
    /**
     * @param $_group
     * @return $this
     */
    /**
     * @param $_group
     * @return $this
     */
    public function setGroup($_group)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->group, $_group);
        $this->group = $_group;
        return $this;
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
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
    /**
     * @param $_changed
     * @return $this
     */
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
    /**
     * @return string
     */
    /**
     * @return string
     */
    public function getTableName()
    {
        return 'interactDef';
    }
}
