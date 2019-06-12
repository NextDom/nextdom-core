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
 * NextDom Software is free software: you can redistribute it and/or modify
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

namespace NextDom\Managers;

use NextDom\Helpers\DBHelper;
use NextDom\Helpers\Utils;
use NextDom\Model\Entity\EqLogic;

/**
 * Class EqLogicManager
 * @package NextDom\Managers
 */
class EqLogicManager
{
    const CLASS_NAME = EqLogic::class;
    const DB_CLASS_NAME = '`eqLogic`';

    /**
     * TODO: ???
     *
     * @param $eqRealId
     *
     * @return array|mixed
     * @throws \Exception
     */
    public static function byEqRealId($eqRealId)
    {
        $values = array(
            'eqReal_id' => $eqRealId,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE eqReal_id = :eqReal_id';
        return self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME));
    }

    /**
     * TODO: ???
     * Repasse en private
     * @param EqLogic $inputs
     *
     * @return array|mixed
     */
    public static function cast($inputs)
    {
        if (is_object($inputs)) {
            $targetClassName = $inputs->getEqType_name();
            if (class_exists($targetClassName)) {
                $target = new $targetClassName();
                $target->castFromEqLogic($inputs);
                return $target;
//            return Utils::cast($inputs, $inputs->getEqType_name());
            }
        }
        if (is_array($inputs)) {
            $return = array();
            foreach ($inputs as $input) {
                $return[] = self::cast($input);
            }

            return $return;
        }
        return $inputs;
    }

    /**
     * Get all eqLogics linked to object
     *
     * @param mixed $objectId Object id
     * @param bool $onlyEnable Filter only enabled
     * @param bool $onlyVisible Filter only visible
     * @param null $eqTypeName
     * @param null $logicalId
     * @param bool $orderByName
     *
     * @return EqLogic[] All linked eqLogic
     *
     * @throws \Exception
     */
    public static function byObjectId($objectId, $onlyEnable = true, $onlyVisible = false, $eqTypeName = null, $logicalId = null, $orderByName = false)
    {
        $values = array();
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . ' ';
        if ($objectId === null) {
            $sql .= 'WHERE object_id IS NULL ';
        } else {
            $values['object_id'] = $objectId;
            $sql .= 'WHERE object_id = :object_id ';
        }
        if ($onlyEnable) {
            $sql .= 'AND isEnable = 1 ';
        }
        if ($onlyVisible) {
            $sql .= 'AND isVisible = 1 ';
        }
        if ($eqTypeName !== null) {
            $values['eqType_name'] = $eqTypeName;
            $sql .= 'AND eqType_name = :eqType_name ';
        }
        if ($logicalId !== null) {
            $values['logicalId'] = $logicalId;
            $sql .= 'AND logicalId = :logicalId ';
        }
        if ($orderByName) {
            $sql .= 'ORDER BY `name`';
        } else {
            $sql .= 'ORDER BY `order`, category';
        }
        return self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME));
    }

    /**
     * TODO: ???
     *
     * @param $logicalId
     * @param $eqTypeName
     * @param bool $multiple
     * @return array|mixed
     * @throws \Exception
     */
    public static function byLogicalId($logicalId, $eqTypeName, $multiple = false)
    {

        $values = [
            'logicalId' => $logicalId,
            'eqType_name' => $eqTypeName,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE logicalId = :logicalId
                AND eqType_name = :eqType_name';
        if ($multiple) {
            $data = self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME));
        } else {
            $data = self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME));
        }
        return $data;
    }

    /**
     * TODO: ???
     *
     * @param $eqTypeName
     * @param bool $onlyEnable
     * @return EqLogic[]|null
     * @throws \Exception
     */
    public static function byType($eqTypeName, $onlyEnable = false)
    {
        $values = array(
            'eqType_name' => $eqTypeName,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 'el') . '
                FROM ' . self::DB_CLASS_NAME . '  el
                LEFT JOIN object ob ON el.object_id = ob.id
                WHERE eqType_name = :eqType_name ';
        if ($onlyEnable) {
            $sql .= 'AND isEnable=1 ';
        }
        $sql .= 'ORDER BY ob.name,el.name';
        return self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME));
    }

    /**
     * Get eqLogics objets by category
     *
     * @param $category
     * @return array|mixed
     * @throws \Exception
     */
    public static function byCategory($category)
    {
        $values = array(
            'category' => '%"' . $category . '":1%',
            'category2' => '%"' . $category . '":"1"%',
        );

        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE category LIKE :category
                OR category LIKE :category2
                ORDER BY name';
        return self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME));
    }

    /**
     * TODO: ???
     *
     * @param $eqTypeName
     * @param $configuration
     * @return array|mixed
     * @throws \Exception
     */
    public static function byTypeAndSearhConfiguration($eqTypeName, $configuration)
    {
        $values = array(
            'eqType_name' => $eqTypeName,
            'configuration' => '%' . $configuration . '%',
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE eqType_name = :eqType_name
                AND configuration LIKE :configuration
                ORDER BY name';
        return self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME));
    }

    /**
     * TODO: ???
     *
     * @param $configuration
     * @param null $type
     * @return array|mixed
     * @throws \Exception
     */
    public static function searchConfiguration($configuration, $type = null)
    {
        if (!is_array($configuration)) {
            $values = array(
                'configuration' => '%' . $configuration . '%',
            );
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE configuration LIKE :configuration';
        } else {
            $values = array(
                'configuration' => '%' . $configuration[0] . '%',
            );
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE configuration LIKE :configuration';
            for ($i = 1; $i < count($configuration); $i++) {
                $values['configuration' . $i] = '%' . $configuration[$i] . '%';
                $sql .= ' OR configuration LIKE :configuration' . $i;
            }
        }
        if ($type !== null) {
            $values['eqType_name'] = $type;
            $sql .= ' AND eqType_name=:eqType_name ';
        }
        $sql .= ' ORDER BY name';
        return self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME));
    }

    /**
     * TODO: ??
     *
     * @param $eqTypeName
     * @param $typeCmd
     * @param string $subTypeCmd
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function listByTypeAndCmdType($eqTypeName, $typeCmd, $subTypeCmd = '')
    {
        if ($subTypeCmd == '') {
            $values = array(
                'eqType_name' => $eqTypeName,
                'typeCmd' => $typeCmd,
            );
            $sql = 'SELECT DISTINCT(el.id),el.name
                    FROM ' . self::DB_CLASS_NAME . '  el
                    INNER JOIN cmd c ON c.eqLogic_id = el.id
                    WHERE eqType_name = :eqType_name
                    AND c.type = :typeCmd
                    ORDER BY name';
            return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL);
        } else {
            $values = array(
                'eqType_name' => $eqTypeName,
                'typeCmd' => $typeCmd,
                'subTypeCmd' => $subTypeCmd,
            );
            $sql = 'SELECT DISTINCT(el.id),el.name
                    FROM ' . self::DB_CLASS_NAME . '  el
                    INNER JOIN cmd c ON c.eqLogic_id = el.id
                    WHERE eqType_name = :eqType_name
                    AND c.type = :typeCmd
                    AND c.subType = :subTypeCmd
                    ORDER BY name';
            return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL);
        }
    }

    /**
     * TODO: ???
     *
     * @param $objectId
     * @param $typeCmd
     * @param string $subTypeCmd
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function listByObjectAndCmdType($objectId, $typeCmd, $subTypeCmd = '')
    {
        $values = array();
        $sql = 'SELECT DISTINCT(el.id), el.name
                FROM ' . self::DB_CLASS_NAME . '  el
                INNER JOIN cmd c ON c.eqLogic_id=el.id
                WHERE ';
        if ($objectId === null) {
            $sql .= 'object_id IS NULL ';
        } elseif ($objectId != '') {
            $values['object_id'] = $objectId;
            $sql .= 'object_id = :object_id ';
        } else {
            return null;
        }
        if ($subTypeCmd != '') {
            $values['subTypeCmd'] = $subTypeCmd;
            $sql .= 'AND c.subType = :subTypeCmd ';
        }
        if ($typeCmd != '' && $typeCmd != 'all') {
            $values['type'] = $typeCmd;
            $sql .= 'AND c.type = :type ';
        }
        $sql .= 'ORDER BY name';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL);
    }

    /**
     * TODO: ???
     *
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function allType()
    {
        $sql = 'SELECT distinct(eqType_name) as type
                FROM ' . self::DB_CLASS_NAME . ' ';
        return DBHelper::Prepare($sql, array(), DBHelper::FETCH_TYPE_ALL);
    }

    /**
     * Vérifier si un objet est actif
     */
    public static function checkAlive()
    {
        $selfByTimeout = self::byTimeout(1, true);
        foreach ($selfByTimeout as $eqLogic) {
            $sendReport = false;
            if (count($eqLogic->getCmd()) > 0) {
                $sendReport = true;
            }
            $logicalId = 'noMessage' . $eqLogic->getId();
            if ($sendReport) {
                $noReponseTimeLimit = $eqLogic->getTimeout();
                if (count(MessageManager::byPluginLogicalId('core', $logicalId)) == 0) {
                    if ($eqLogic->getStatus('lastCommunication', date('Y-m-d H:i:s')) < date('Y-m-d H:i:s', strtotime('-' . $noReponseTimeLimit . ' minutes' . date('Y-m-d H:i:s')))) {
                        $message = __('Attention') . ' ' . $eqLogic->getHumanName();
                        $message .= __(' n\'a pas envoyé de message depuis plus de ') . $noReponseTimeLimit . __(' min (vérifiez les piles)');
                        $eqLogic->setStatus('timeout', 1);
                        if (ConfigManager::ByKey('alert::addMessageOnTimeout') == 1) {
                            MessageManager::add('core', $message, '', $logicalId);
                        }
                        $cmds = explode(('&&'), ConfigManager::byKey('alert::timeoutCmd'));
                        if (count($cmds) > 0 && trim(ConfigManager::byKey('alert::timeoutCmd')) != '') {
                            foreach ($cmds as $id) {
                                $cmd = CmdManager::byId(str_replace('#', '', $id));
                                if (is_object($cmd)) {
                                    $cmd->execCmd(array(
                                        'title' => __('[' . ConfigManager::byKey('name', 'core', 'NEXTDOM') . '] ') . $message,
                                        'message' => ConfigManager::byKey('name', 'core', 'NEXTDOM') . ' : ' . $message,
                                    ));
                                }
                            }
                        }
                    }
                } else {
                    if ($eqLogic->getStatus('lastCommunication', date('Y-m-d H:i:s')) > date('Y-m-d H:i:s', strtotime('-' . $noReponseTimeLimit . ' minutes' . date('Y-m-d H:i:s')))) {
                        foreach (MessageManager::byPluginLogicalId('core', $logicalId) as $message) {
                            $message->remove();
                        }
                        $eqLogic->setStatus('timeout', 0);
                    }
                }
            }
        }
    }

    /**
     * TODO: ???
     *
     * @param int $timeout
     * @param bool $onlyEnable
     * @return array|mixed
     * @throws \Exception
     */
    public static function byTimeout($timeout = 0, $onlyEnable = false)
    {
        $values = array(
            'timeout' => $timeout,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE timeout >= :timeout';
        if ($onlyEnable) {
            $sql .= ' AND isEnable = 1';
        }
        return self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME));
    }

    /**
     * TODO: ???
     *
     * @param $input
     * @return array|mixed
     * @throws \ReflectionException
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
            /** @var @var \ReflectionProperty $property */
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
        preg_match_all("/#eqLogic([0-9]*)#/", $text, $matches);
        foreach ($matches[1] as $eqLogic_id) {
            if (is_numeric($eqLogic_id)) {
                $eqLogic = self::byId($eqLogic_id);
                if (is_object($eqLogic)) {
                    $text = str_replace('#eqLogic' . $eqLogic_id . '#', '#' . $eqLogic->getHumanName() . '#', $text);
                }
            }
        }
        return $text;
    }

    /**
     * Get eqLogic object with his id.
     *
     * @param mixed $id EqLogic object id
     *
     * @return EqLogic|null
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        if ($id == '') {
            return null;
        }
        $values = array(
            'id' => $id,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME));
    }

    /**
     * TODO: ???
     */
    public static function clearCacheWidget()
    {
        foreach (self::all() as $eqLogic) {
            $eqLogic->emptyCacheWidget();
        }
    }

    /**
     * Get all eqLogics
     *
     * @param bool $onlyEnable Filter only enabled eqLogics
     *
     * @return EqLogic[]|mixed
     * @throws \Exception
     */
    public static function all($onlyEnable = false)
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 'el') . '
                FROM ' . self::DB_CLASS_NAME . ' el
                LEFT JOIN object ob ON el.object_id = ob.id ';
        if ($onlyEnable) {
            $sql .= 'WHERE isEnable = 1 ';
        }
        $sql .= 'ORDER BY ob.name, el.name';
        return self::cast(DBHelper::Prepare($sql, array(), DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME));
    }

    /**
     * TODO: ???
     *
     * @param $nbLine
     * @param $nbColumn
     * @param array $options
     * @return array
     */
    public static function generateHtmlTable($nbLine, $nbColumn, $options = array())
    {
        $return = array('html' => '', 'replace' => array());
        if (!isset($options['styletd'])) {
            $options['styletd'] = '';
        }
        if (!isset($options['center'])) {
            $options['center'] = 0;
        }
        if (!isset($options['styletable'])) {
            $options['styletable'] = '';
        }
        $return['html'] .= '<table style="' . $options['styletable'] . '" class="tableCmd" data-line="' . $nbLine . '" data-column="' . $nbColumn . '">';
        $return['html'] .= '<tbody>';
        for ($i = 1; $i <= $nbLine; $i++) {
            $return['html'] .= '<tr>';
            for ($j = 1; $j <= $nbColumn; $j++) {
                $styletd = (isset($options['style::td::' . $i . '::' . $j]) && $options['style::td::' . $i . '::' . $j] != '') ? $options['style::td::' . $i . '::' . $j] : $options['styletd'];
                $return['html'] .= '<td style="min-width:30px;height:30px;' . $styletd . '" data-line="' . $i . '" data-column="' . $j . '">';
                if ($options['center'] == 1) {
                    $return['html'] .= '<center>';
                }
                if (isset($options['text::td::' . $i . '::' . $j])) {
                    $return['html'] .= $options['text::td::' . $i . '::' . $j];
                }
                $return['html'] .= '#cmd::' . $i . '::' . $j . '#';
                if ($options['center'] == 1) {
                    $return['html'] .= '</center>';
                }
                $return['html'] .= '</td>';
                $return['tag']['#cmd::' . $i . '::' . $j . '#'] = '';
            }
            $return['html'] .= '</tr>';
        }
        $return['html'] .= '</tbody>';
        $return['html'] .= '</table>';

        return $return;
    }

    /**
     * Obtenir l'ensemble des tags liés aux objets
     *
     * @return array
     * @throws \Exception
     */
    public static function getAllTags()
    {
        $values = array();
        $sql = 'SELECT tags
                FROM ' . self::DB_CLASS_NAME . '
                WHERE tags IS NOT NULL
        	    AND tags!=""';
        $results = DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL);
        $return = array();
        foreach ($results as $result) {
            $tags = explode(',', $result['tags']);
            foreach ($tags as $tag) {
                $return[$tag] = $tag;
            }
        }
        return $return;
    }

    /**
     * @param $_string
     * @return EqLogic|null
     * @throws \Exception
     */
    public static function byString($_string)
    {
        $eqLogic = self::byId(str_replace(array('#','eqLogic'), '', self::fromHumanReadable($_string)));
        if (!is_object($eqLogic)) {
            throw new \Exception(__('L\'équipement n\'a pas pu être trouvé : ') . $_string . __(' => ') . self::fromHumanReadable($_string));
        }
        return $eqLogic;
    }

    /**
     * TODO: ???
     *
     * @param $input
     * @return array|mixed
     * @throws \Exception
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
            /** @var \ReflectionProperty $property */
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
        preg_match_all("/#\[(.*?)\]\[(.*?)\]#/", $text, $matches);
        if (count($matches) == 3) {
            $countMatches = count($matches[0]);
            for ($i = 0; $i < $countMatches; $i++) {
                if (isset($matches[1][$i]) && isset($matches[2][$i])) {
                    $eqLogic = self::byObjectNameEqLogicName($matches[1][$i], $matches[2][$i]);
                    if (isset($eqLogic[0]) && is_object($eqLogic[0])) {
                        $text = str_replace($matches[0][$i], '#eqLogic' . $eqLogic[0]->getId() . '#', $text);
                    }
                }
            }
        }
        return $text;
    }

    /**
     * TODO: ???
     *
     * @param $objectName
     * @param $eqLogicName
     * @return array|mixed
     * @throws \Exception
     */
    public static function byObjectNameEqLogicName($objectName, $eqLogicName)
    {
        if ($objectName == __('Aucun')) {
            $values = [
                'eqLogic_name' => $eqLogicName,
            ];
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE name=:eqLogic_name
                    AND object_id IS NULL';
        } else {
            $values = array(
                'eqLogic_name' => $eqLogicName,
                'object_name' => $objectName,
            );
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 'el') . '
                    FROM ' . self::DB_CLASS_NAME . '  el
                    INNER JOIN object ob ON el.object_id=ob.id
                    WHERE el.name=:eqLogic_name
                    AND ob.name=:object_name';
        }
        return self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME));
    }
}
