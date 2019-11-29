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

use NextDom\Enums\CmdType;
use NextDom\Enums\DateFormat;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Model\Entity\Cmd;
use NextDom\Model\Entity\EqLogic;

/**
 * Manage command
 *
 * @package NextDom\Managers
 */
class CmdManager
{
    /** @var string Class of the commands */
    const CLASS_NAME = Cmd::class;
    /** @var string Table name of the class in database */
    const DB_CLASS_NAME = '`cmd`';

    /**
     * Get historized commands
     *
     * @return array List of historized commands
     *
     * @throws \Exception
     */
    public static function allHistoryCmd()
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 'c') . '
                FROM ' . self::DB_CLASS_NAME . ' c
                INNER JOIN eqLogic el ON c.eqLogic_id=el.id
                INNER JOIN object ob ON el.object_id=ob.id
                WHERE isHistorized = 1
                AND type=\'info\'';
        $sql .= ' ORDER BY ob.position, ob.name, el.name, c.name';
        $result1 = self::cast(DBHelper::getAllObjects($sql, [], self::CLASS_NAME));
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 'c') . '
                FROM ' . self::DB_CLASS_NAME . ' c
                INNER JOIN eqLogic el ON c.eqLogic_id=el.id
                WHERE el.object_id IS NULL
                AND isHistorized = 1
                AND type=\'info\'';
        $sql .= ' ORDER BY el.name, c.name';
        $result2 = self::cast(DBHelper::getAllObjects($sql, [], self::CLASS_NAME));
        return array_merge($result1, $result2);
    }

    /**
     * Cast a command from generic to plugin
     *
     * @param Cmd|Cmd[] $inputs Commands to cast
     * @param EqLogic $eqLogic Link to a specific EqLogic
     *
     * @return array|mixed Casted command
     */
    public static function cast($inputs, $eqLogic = null)
    {
        if (is_object($inputs)) {
            $targetClassName = $inputs->getEqType() . 'Cmd';
            if (class_exists($targetClassName)) {
                if ($eqLogic !== null) {
                    $inputs->_eqLogic = $eqLogic;
                }
                /** @var Cmd $target */
                $target = new $targetClassName();
                $target->castFromCmd($inputs);
                return $target;
            }
        }
        if (is_array($inputs)) {
            $result = [];
            foreach ($inputs as $input) {
                if ($eqLogic !== null) {
                    $input->_eqLogic = $eqLogic;
                }
                $result[] = self::cast($input);
            }
            return $result;
        }
        return $inputs;
    }

    /**
     * Get commands attached to eqLogic objects
     *
     * @param int|array $eqLogicId EqLogic object id or array of EqLogic id
     * @param string|null $_type
     * @param int|null $_visible Only visible if !== null
     * @param null $_eqLogic
     * @param null $_has_generic_type
     *
     * @return Cmd|Cmd[]
     *
     * @throws \Exception
     */
    public static function byEqLogicId($eqLogicId, $_type = null, $_visible = null, $_eqLogic = null, $_has_generic_type = null)
    {
        $values = [];
        if (is_array($eqLogicId)) {
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE eqLogic_id IN (' . trim(preg_replace('/[, ]{2,}/m', ',', implode(',', $eqLogicId)), ',') . ')';
        } else {
            $values = [
                'eqLogic_id' => $eqLogicId,
            ];
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE eqLogic_id = :eqLogic_id ';
        }
        if ($_type !== null) {
            $values['type'] = $_type;
            $sql .= 'AND `type` = :type ';
        }
        if ($_visible !== null) {
            $sql .= 'AND `isVisible` = 1 ';
        }
        if ($_has_generic_type) {
            $sql .= 'AND `generic_type` IS NOT NULL ';
        }
        $sql .= 'ORDER BY `order`,`name`';
        return self::cast(DBHelper::getAllObjects($sql, $values, self::CLASS_NAME), $_eqLogic);
    }

    /**
     * Get command by logical id
     *
     * @param int $logicalId Filter by logical id
     * @param string $type Filter by type
     *
     * @return array|mixed
     *
     * @throws \Exception
     */
    public static function byLogicalId($logicalId, $type = null)
    {
        $values = [
            'logicalId' => $logicalId,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE logicalId = :logicalId ';
        if ($type !== null) {
            $values['type'] = $type;
            $sql .= 'AND `type` = :type ';
        }
        $sql .= 'ORDER BY `order`';
        return self::cast(DBHelper::getAllObjects($sql, $values, self::CLASS_NAME));
    }

    /**
     * Get command by generic type (plugin)
     *
     * @param string $genericType Type of the command
     * @param int $eqLogicId Commands of eqLogic
     * @param bool $one Only the first
     *
     * @return Cmd|Cmd[]
     *
     * @throws \Exception
     */
    public static function byGenericType($genericType, $eqLogicId = null, $one = false)
    {
        if (is_array($genericType)) {
            $in = '';
            foreach ($genericType as $value) {
                $in .= "'" . $value . "',";
            }
            $values = [];
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE generic_type IN (' . trim(preg_replace('/[, ]{2,}/m', ',', $in), ',') . ')';
        } else {
            $values = [
                'generic_type' => $genericType,
            ];
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE generic_type = :generic_type';
        }
        if ($eqLogicId !== null) {
            $values['eqLogic_id'] = $eqLogicId;
            $sql .= ' AND `eqLogic_id` = :eqLogic_id';
        }
        $sql .= ' ORDER BY `order`';
        if ($one) {
            return self::cast(DBHelper::getOneObject($sql, $values, self::CLASS_NAME));
        }
        return self::cast(DBHelper::getAllObjects($sql, $values, self::CLASS_NAME));
    }

    /**
     * Search in commands configuration
     *
     * @param string|array $configuration String to find
     * @param string $eqType Filter by EqLogic type
     *
     * @return Cmd[] List of commands
     *
     * @throws \Exception
     */
    public static function searchConfiguration($configuration, $eqType = null)
    {
        if (!is_array($configuration)) {
            $values = [
                'configuration' => '%' . $configuration . '%',
            ];
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE configuration LIKE :configuration';
        } else {
            $values = [
                'configuration' => '%' . $configuration[0] . '%',
            ];
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE configuration LIKE :configuration';
            for ($i = 1; $i < count($configuration); $i++) {
                $values['configuration' . $i] = '%' . $configuration[$i] . '%';
                $sql .= ' OR configuration LIKE :configuration' . $i;
            }
        }
        if ($eqType !== null) {
            $values['eqType'] = $eqType;
            $sql .= ' AND eqType = :eqType ';
        }
        $sql .= ' ORDER BY name';
        return self::cast(DBHelper::getAllObjects($sql, $values, self::CLASS_NAME));
    }

    /**
     * Search in commands configuration
     *
     * @param int $eqLogicId Filter by eqLogic
     * @param string $configuration String to find
     * @param string $type Command type
     *
     * @return Cmd[]
     *
     * @throws \Exception
     */
    public static function searchConfigurationEqLogic($eqLogicId, $configuration, $type = null)
    {
        $values = [
            'configuration' => '%' . $configuration . '%',
            'eqLogic_id' => $eqLogicId,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE eqLogic_id = :eqLogic_id';
        if ($type !== null) {
            $values['type'] = $type;
            $sql .= ' AND type = :type ';
        }
        $sql .= ' AND configuration LIKE :configuration';
        return self::cast(DBHelper::getAllObjects($sql, $values, self::CLASS_NAME));
    }

    /**
     * Search by template
     *
     * @param string $template Name of template
     * @param string $eqType Filter by eqLogic type
     * @param string $type Filter by type
     * @param string $subtype Filter by subtype
     *
     * @return array|mixed
     *
     * @throws \Exception
     */
    public static function searchTemplate($template, $eqType = null, $type = null, $subtype = null)
    {
        $values = [
            'template' => '%' . $template . '%',
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE template LIKE :template';
        if ($eqType !== null) {
            $values['eqType'] = $eqType;
            $sql .= ' AND eqType = :eqType ';
        }
        if ($type !== null) {
            $values['type'] = $type;
            $sql .= ' AND type = :type ';
        }
        if ($subtype !== null) {
            $values['subType'] = $subtype;
            $sql .= ' AND subType = :subType ';
        }
        $sql .= ' ORDER BY name';
        return self::cast(DBHelper::getAllObjects($sql, $values, self::CLASS_NAME));
    }

    /**
     * Get all commands by eqLogic and command logicalId
     *
     * @param int $eqLogicId EqLogic Id owner
     * @param string $logicalId Command logicial id
     * @param bool $multiple Multiple results
     * @param string $type Filter by type of command
     *
     * @return array|mixed
     *
     * @throws \Exception
     */
    public static function byEqLogicIdAndLogicalId($eqLogicId, $logicalId, $multiple = false, $type = null)
    {
        $values = [
            'eqLogic_id' => $eqLogicId,
            'logicalId' => $logicalId,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE eqLogic_id = :eqLogic_id
                AND logicalId = :logicalId';
        if ($type !== null) {
            $values['type'] = $type;
            $sql .= ' AND type = :type';
        }
        if ($multiple) {
            return self::cast(DBHelper::getAllObjects($sql, $values, self::CLASS_NAME));
        }
        return self::cast(DBHelper::getOneObject($sql, $values, self::CLASS_NAME));
    }

    /**
     * Get all commands by eqLogic and generic type
     *
     * @param int $eqLogicId EqLogic Id owner
     * @param string $genericType Filter by generic type
     * @param bool $multiple Multiple results
     * @param string $type Filter by type
     *
     * @return Cmd|Cmd[]
     *
     * @throws \Exception
     */
    public static function byEqLogicIdAndGenericType($eqLogicId, $genericType, $multiple = false, $type = null)
    {
        $values = [
            'eqLogic_id' => $eqLogicId,
            'generic_type' => $genericType,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE eqLogic_id = :eqLogic_id
                AND generic_type = :generic_type';
        if ($type !== null) {
            $values['type'] = $type;
            $sql .= ' AND type = :type';
        }
        if ($multiple) {
            return self::cast(DBHelper::getAllObjects($sql, $values, self::CLASS_NAME));
        }
        return self::cast(DBHelper::getOneObject($sql, $values, self::CLASS_NAME));
    }

    /**
     * Get commands by value
     *
     * @param int|string $value Filter by value
     * @param string $type Filter by type
     * @param bool $onlyEnable Only enabled commands
     *
     * @return Cmd[]
     *
     * @throws \Exception
     */
    public static function byValue($value, $type = null, $onlyEnable = false)
    {
        $values = [
            'value' => $value,
            'search' => '%#' . $value . '#%',
        ];
        if (strpos($value, 'variable(') !== false) {
            $values['search'] = '%#' . $value . '%';
        }
        if ($onlyEnable) {
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 'c') . '
                    FROM ' . self::DB_CLASS_NAME . ' c
                    INNER JOIN eqLogic el ON c.eqLogic_id=el.id
                    WHERE (value = :value OR value LIKE :search)
                    AND el.isEnable = 1
                    AND c.id != :value';
            if ($type !== null) {
                $values['type'] = $type;
                $sql .= ' AND c.type = :type ';
            }
        } else {
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE (value = :value OR value LIKE :search)
                    AND id != :value';
            if ($type !== null) {
                $values['type'] = $type;
                $sql .= ' AND type = :type ';
            }
        }
        return self::cast(DBHelper::getAllObjects($sql, $values, self::CLASS_NAME));
    }

    /**
     * Get commands by eqLogic type, eqLogic name and cmd name
     *
     * @param string $eqTypeName EqLogic type name
     * @param string $eqLogicName Eqlogic name
     * @param string $cmdName Command name
     *
     * @return Cmd[]
     *
     * @throws \Exception
     */
    public static function byTypeEqLogicNameCmdName($eqTypeName, $eqLogicName, $cmdName)
    {
        $values = [
            'eqType_name' => $eqTypeName,
            'eqLogic_name' => $eqLogicName,
            'cmd_name' => $cmdName,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 'c') . '
                FROM ' . self::DB_CLASS_NAME . ' c
                INNER JOIN eqLogic el ON c.eqLogic_id=el.id
                WHERE c.name = :cmd_name
                AND el.name = :eqLogic_name
                AND el.eqType_name = :eqType_name';
        return self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME));
    }

    /**
     * Get commands by EqLogic Id and command name
     *
     * @param int $eqLogicId EqLogic id
     * @param string $cmdName Command name
     *
     * @return Cmd[]
     *
     * @throws \Exception
     */
    public static function byEqLogicIdCmdName($eqLogicId, $cmdName)
    {
        $values = [
            'eqLogic_id' => $eqLogicId,
            'cmd_name' => $cmdName,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 'c') . '
                FROM ' . self::DB_CLASS_NAME . ' c
                WHERE c.name = :cmd_name
                AND c.eqLogic_id = :eqLogic_id';
        return self::cast(DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME));
    }

    /**
     * Get commands by object and command name
     *
     * @param string $objectName Filter on object name
     * @param string $cmdName Filter by command name
     *
     * @return Cmd[]
     *
     * @throws \Exception
     */
    public static function byObjectNameCmdName($objectName, $cmdName)
    {
        $values = [
            'object_name' => $objectName,
            'cmd_name' => $cmdName,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 'c') . '
                FROM ' . self::DB_CLASS_NAME . ' c
                INNER JOIN eqLogic el ON c.eqLogic_id = el.id
                INNER JOIN object ob ON el.object_id = ob.id
                WHERE c.name = :cmd_name
                AND ob.name = :object_name';
        return self::cast(DBHelper::getOneObject($sql, $values, self::CLASS_NAME));
    }

    /**
     * Get commands by type and subtype
     *
     * @param string $type Command type
     * @param string $subType Command subtype
     *
     * @return Cmd[]
     *
     * @throws \Exception
     */
    public static function byTypeSubType($type, $subType = '')
    {
        $values = [
            'type' => $type,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 'c') . '
                FROM ' . self::DB_CLASS_NAME . ' c
                WHERE c.type = :type';
        if ($subType != '') {
            $values['subtype'] = $subType;
            $sql .= ' AND c.subtype = :subtype';
        }
        return self::cast(DBHelper::getAllObjects($sql, $values, self::CLASS_NAME));
    }

    /**
     * Convert command to human readable format
     *
     * @param Cmd|mixed $input Input data
     * @return string Human readable command
     * @throws \ReflectionException
     */
    public static function cmdToHumanReadable($input)
    {
        if (is_object($input)) {
            $reflections = [];
            $uuid = spl_object_hash($input);
            if (!isset($reflections[$uuid])) {
                $reflections[$uuid] = new \ReflectionClass($input);
            }
            $reflection = $reflections[$uuid];
            /** @var \ReflectionProperty[] $properties */
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $value = $property->getValue($input);
                $property->setValue($input, self::cmdToHumanReadable($value));
                $property->setAccessible(false);
            }
            return $input;
        }
        if (is_array($input)) {
            return json_decode(self::cmdToHumanReadable(json_encode($input)), true);
        }
        $replace = [];
        preg_match_all("/#([0-9]*)#/", $input, $matches);
        if (count($matches[1]) == 0) {
            return $input;
        }
        $cmds = self::byIds($matches[1]);
        foreach ($cmds as $cmd) {
            if (isset($replace['#' . $cmd->getId() . '#'])) {
                continue;
            }
            $replace['#' . $cmd->getId() . '#'] = '#' . $cmd->getHumanName() . '#';
        }
        return str_replace(array_keys($replace), $replace, $input);
    }

    /**
     * Get list of commands by IDs
     *
     * @param array $idsList List of ID
     *
     * @return Cmd[]|null List of commands
     *
     * @throws \Exception
     */
    public static function byIds($idsList)
    {
        if (!is_array($idsList) || count($idsList) == 0) {
            return [];
        }
        $in = trim(preg_replace('/[, ]{2,}/m', ',', implode(',', $idsList)), ',');
        if ($in === '') {
            return [];
        }
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id IN (' . $in . ')';
        return self::cast(DBHelper::getAllObjects($sql, [], self::CLASS_NAME));
    }

    /**
     * Get commands by string in human readeable format
     *
     * @param string $needle String to search
     *
     * @return Cmd
     *
     * @throws \Exception
     */
    public static function byString($needle)
    {
        $cmd = self::byId(str_replace('#', '', self::humanReadableToCmd($needle)));
        if (!is_object($cmd)) {
            throw new CoreException(__('La commande n\'a pas pu être trouvée : ') . $needle . __(' => ') . self::humanReadableToCmd($needle));
        }
        return $cmd;
    }

    /**
     * Get command by his id
     *
     * @param mixed $id Command id
     *
     * @return Cmd|bool Command or false
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        if ($id == '') {
            return null;
        }
        $values = [
            'id' => $id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return self::cast(DBHelper::getOneObject($sql, $values, self::CLASS_NAME));
    }

    /**
     * Convert human readable format to command
     *
     * @param $input
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function humanReadableToCmd($input)
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
            /** @var \ReflectionProperty[] $properties */
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $value = $property->getValue($input);
                $property->setValue($input, self::humanReadableToCmd($value));
                $property->setAccessible(false);
            }
            return $input;
        }
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::humanReadableToCmd($value);
            }
            if ($isJson) {
                return json_encode($input, JSON_UNESCAPED_UNICODE);
            }
            return $input;
        }
        $replace = [];
        preg_match_all("/#\[(.*?)\]\[(.*?)\]\[(.*?)\]#/", $input, $matches);
        if (count($matches) == 4) {
            $countMatches = count($matches[0]);
            for ($i = 0; $i < $countMatches; $i++) {
                if (isset($replace[$matches[0][$i]])) {
                    continue;
                }
                if (isset($matches[1][$i]) && isset($matches[2][$i]) && isset($matches[3][$i])) {
                    $cmd = self::byObjectNameEqLogicNameCmdName($matches[1][$i], $matches[2][$i], $matches[3][$i]);
                    if (is_object($cmd)) {
                        $replace[$matches[0][$i]] = '#' . $cmd->getId() . '#';
                    }
                }
            }
        }
        return str_replace(array_keys($replace), $replace, $input);
    }

    /**
     * Get command by object name, eqLogic name and cmd name
     *
     * @param string $objectName Object name
     * @param string $eqLogicName EqLogic name
     * @param string $cmdName Command name
     *
     * @return Cmd
     *
     * @throws \Exception
     */
    public static function byObjectNameEqLogicNameCmdName($objectName, $eqLogicName, $cmdName)
    {
        $values = [
            'eqLogic_name' => $eqLogicName,
            'cmd_name' => (html_entity_decode($cmdName) != '') ? html_entity_decode($cmdName) : $cmdName,
        ];

        if ($objectName == __('Aucun')) {
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 'c') . '
            FROM ' . self::DB_CLASS_NAME . ' c
            INNER JOIN eqLogic el ON c.eqLogic_id=el.id
            WHERE c.name = :cmd_name
            AND el.name = :eqLogic_name
            AND el.object_id IS NULL';
        } else {
            $values['object_name'] = $objectName;
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME, 'c') . '
            FROM ' . self::DB_CLASS_NAME . ' c
            INNER JOIN eqLogic el ON c.eqLogic_id=el.id
            INNER JOIN object ob ON el.object_id=ob.id
            WHERE c.name = :cmd_name
            AND el.name = :eqLogic_name
            AND ob.name = :object_name';
        }
        return self::cast(DBHelper::getOneObject($sql, $values, self::CLASS_NAME));
    }

    /**
     * Convert command to value
     *
     * @param mixed $input Input data
     * @param bool $quote Quote result
     *
     * @return array|mixed
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function cmdToValue($input, $quote = false)
    {
        if (is_object($input)) {
            $reflections = [];
            $uuid = spl_object_hash($input);
            if (!isset($reflections[$uuid])) {
                $reflections[$uuid] = new \ReflectionClass($input);
            }
            $reflection = $reflections[$uuid];
            /** @var \ReflectionProperty[] $properties */
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $value = $property->getValue($input);
                $property->setValue($input, self::cmdToValue($value, $quote));
                $property->setAccessible(false);
            }
            return $input;
        }
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::cmdToValue($value, $quote);
            }
            return $input;
        }

        $replace = [];
        preg_match_all("/#([0-9]*)#/", $input, $matches);
        foreach ($matches[1] as $cmd_id) {
            if (isset($replace['#' . $cmd_id . '#'])) {
                continue;
            }
            $mc = CacheManager::byKey('cmdCacheAttr' . $cmd_id);
            $cmdCacheAttrValue = $mc->getValue();
            if (Utils::getJsonAttr($cmdCacheAttrValue, 'value', null) !== null) {
                $cmdCacheAttrValue = $mc->getValue();
                $collectDate = Utils::getJsonAttr($cmdCacheAttrValue, 'collectDate', date(DateFormat::FULL));
                $valueDate = Utils::getJsonAttr($cmdCacheAttrValue, 'valueDate', date(DateFormat::FULL));
                $cmd_value = Utils::getJsonAttr($cmdCacheAttrValue, 'value', '');
            } else {
                $cmd = self::byId($cmd_id);
                if (!is_object($cmd) || $cmd->isType(CmdType::INFO)) {
                    continue;
                }
                $cmd_value = $cmd->execCmd(null, true, $quote);
                $collectDate = $cmd->getCollectDate();
                $valueDate = $cmd->getValueDate();
            }
            if ($quote && (strpos($cmd_value, ' ') !== false || preg_match("/[a-zA-Z#]/", $cmd_value) || $cmd_value === '')) {
                $cmd_value = '"' . trim($cmd_value, '"') . '"';
            }
            if (Utils::isJson($input)) {
                $replace['#' . $cmd_id . '#'] = trim(json_encode($cmd_value), '"');
                $replace['#valueDate' . $cmd_id . '#'] = trim(json_encode($valueDate), '"');
                $replace['#collectDate' . $cmd_id . '#'] = trim(json_encode($collectDate), '"');
            } else {
                $replace['"#' . $cmd_id . '#"'] = $cmd_value;
                $replace['#' . $cmd_id . '#'] = $cmd_value;
                $replace['#collectDate' . $cmd_id . '#'] = $collectDate;
                $replace['#valueDate' . $cmd_id . '#'] = $valueDate;
            }
        }
        return str_replace(array_keys($replace), $replace, $input);
    }

    /**
     * Get all used command types
     *
     * @return array|null All commands type
     *
     * @throws \Exception
     */
    public static function allType()
    {
        $sql = 'SELECT DISTINCT(`type`) as type
                FROM ' . self::DB_CLASS_NAME;
        return DBHelper::getAll($sql);
    }

    /**
     * Get all used command sub types
     *
     * @param string $type Filter by type
     *
     * @return array All subtype
     *
     * @throws \Exception
     */
    public static function allSubType($type = '')
    {
        $values = [];
        $sql = 'SELECT distinct(subType) as subtype';
        if ($type != '') {
            $values['type'] = $type;
            $sql .= ' WHERE type = :type';
        }
        $sql .= ' FROM ' . self::DB_CLASS_NAME;
        return DBHelper::getAll($sql, $values);
    }

    /**
     * Get all used unites
     *
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function allUnite()
    {
        $sql = 'SELECT DISTINCT(unite) as unite
                FROM ' . self::DB_CLASS_NAME;
        return DBHelper::getAll($sql);
    }

    /**
     * Convert color to hexadecimal code
     *
     * @param string $color Color identification
     *
     * @return string Hexadecimal format color
     *
     * @throws \Exception
     */
    public static function convertColor($color)
    {
        $colors = ConfigManager::byKey('convertColor');
        if (isset($colors[$color])) {
            return $colors[$color];
        }
        throw new CoreException(__('Impossible de traduire la couleur en code hexadécimal :') . $color);
    }

    /**
     * Check if widget is available
     *
     * @param string $version Display version
     *
     * @return array
     */
    public static function availableWidget($version)
    {
        $path = NEXTDOM_ROOT . '/core/template/' . $version;
        $files = FileSystemHelper::ls($path, 'cmd.*', false, ['files', 'quiet']);
        $result = [];
        foreach ($files as $file) {
            $informations = explode('.', $file);
            if (!isset($result[$informations[1]])) {
                $result[$informations[1]] = [];
            }
            if (!isset($result[$informations[1]][$informations[2]])) {
                $result[$informations[1]][$informations[2]] = [];
            }
            if (isset($informations[3])) {
                $result[$informations[1]][$informations[2]][$informations[3]] = ['name' => $informations[3], 'location' => 'core'];
            }
        }
        $path = NEXTDOM_ROOT . '/plugins/widget/core/template/' . $version;
        if (file_exists($path)) {
            $files = FileSystemHelper::ls($path, 'cmd.*', false, ['files', 'quiet']);
            foreach ($files as $file) {
                $informations = explode('.', $file);
                if (count($informations) > 3) {
                    if (!isset($result[$informations[1]])) {
                        $result[$informations[1]] = [];
                    }
                    if (!isset($result[$informations[1]][$informations[2]])) {
                        $result[$informations[1]][$informations[2]] = [];
                    }
                    if (!isset($result[$informations[1]][$informations[2]][$informations[3]])) {
                        $result[$informations[1]][$informations[2]][$informations[3]] = ['name' => $informations[3], 'location' => 'widget'];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Force command to return state by event
     *
     * @param array $options Options that contains command id
     *
     * @throws \Exception
     */
    public static function returnState($options)
    {
        $cmd = self::byId($options['cmd_id']);
        if (is_object($cmd)) {
            $cmd->event($cmd->getConfiguration('returnStateValue', 0));
        }
    }

    /**
     * List of dead commands
     *
     * @return array Description of all dead commands
     *
     * @throws \Exception
     */
    public static function deadCmd()
    {
        $result = [];
        foreach (self::all() as $cmd) {
            if (is_array($cmd->getConfiguration('actionCheckCmd', ''))) {
                foreach ($cmd->getConfiguration('actionCheckCmd', '') as $actionCmd) {
                    if ($actionCmd['cmd'] != '' && strpos($actionCmd['cmd'], '#') !== false) {
                        if (!self::byId(str_replace('#', '', $actionCmd['cmd']))) {
                            $result[] = ['detail' => 'Commande ' . $cmd->getName() . ' de ' . $cmd->getEqLogicId()->getName() . ' (' . $cmd->getEqLogicId()->getEqType_name() . ')', 'help' => 'Action sur valeur', 'who' => $actionCmd['cmd']];
                        }
                    }
                }
            }
            if (is_array($cmd->getConfiguration('nextdomPostExecCmd', ''))) {
                foreach ($cmd->getConfiguration('nextdomPostExecCmd', '') as $actionCmd) {
                    if ($actionCmd['cmd'] != '' && strpos($actionCmd['cmd'], '#') !== false) {
                        if (!self::byId(str_replace('#', '', $actionCmd['cmd']))) {
                            $result[] = ['detail' => 'Commande ' . $cmd->getName() . ' de ' . $cmd->getEqLogicId()->getName() . ' (' . $cmd->getEqLogicId()->getEqType_name() . ')', 'help' => 'Post Exécution', 'who' => $actionCmd['cmd']];
                        }
                    }
                }
            }
            if (is_array($cmd->getConfiguration('nextdomPreExecCmd', ''))) {
                foreach ($cmd->getConfiguration('nextdomPreExecCmd', '') as $actionCmd) {
                    if ($actionCmd['cmd'] != '' && strpos($actionCmd['cmd'], '#') !== false) {
                        if (!self::byId(str_replace('#', '', $actionCmd['cmd']))) {
                            $result[] = ['detail' => 'Commande ' . $cmd->getName() . ' de ' . $cmd->getEqLogicId()->getName() . ' (' . $cmd->getEqLogicId()->getEqType_name() . ')', 'help' => 'Pré Exécution', 'who' => $actionCmd['cmd']];
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get all commands
     *
     * @return Cmd[] All commands
     *
     * @throws \Exception
     */
    public static function all()
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                ORDER BY id';
        return self::cast(DBHelper::getAllObjects($sql, [], self::CLASS_NAME));
    }

    /**
     * Execute command and alert this execution
     *
     * @param array $options Options that contains command id
     *
     * @throws \Exception
     */
    public static function cmdAlert($options)
    {
        $cmd = self::byId($options['cmd_id']);
        if (!is_object($cmd)) {
            return;
        }
        $value = $cmd->execCmd();
        $check = NextDomHelper::evaluateExpression($value . $cmd->getConfiguration('nextdomCheckCmdOperator') . $cmd->getConfiguration('nextdomCheckCmdTest'));
        if ($check == 1 || $check || $check == '1') {
            $cmd->executeAlertCmdAction();
        }
    }

    /**
     * Show command timeline
     *
     * @param array $event Event data
     *
     * @return array Timeline data
     *
     * @throws \Exception
     */
    public static function timelineDisplay($event)
    {
        $result = [];
        $result['date'] = $event['datetime'];
        $result['type'] = $event['type'];
        $result['group'] = $event['subtype'];
        $cmd = self::byId($event['id']);
        if (!is_object($cmd)) {
            return null;
        }
        $eqLogic = $cmd->getEqLogicId();
        $linkedObject = $eqLogic->getObject();
        $result['object'] = is_object($linkedObject) ? $linkedObject->getId() : 'aucun';
        $result['plugins'] = $eqLogic->getEqType_name();
        $result['category'] = $eqLogic->getCategory();

        if ($event['subtype'] == 'action') {
            $result['html'] = '<div class="timeline-item cmd" data-id="' . $event['id'] . '">'
                . '<span class="time"><i class="fa fa-clock-o"></i>' . substr($event['datetime'], -9) . '</span>'
                . '<h3 class="timeline-header">' . $event['name'] . '</h3>'
                . '<div class="timeline-body">'
                . $event['options']
                . ' <div class="timeline-footer">'
                . '</div>'
                . '</div>';
        } else {
            $result['html'] = '<div class="timeline-item cmd" data-id="' . $event['id'] . '">'
                . '<span class="time"><i class="fa fa-clock-o"></i>' . substr($event['datetime'], -9) . '</span>'
                . '<h3 class="timeline-header">' . $event['name'] . '</h3>'
                . '<div class="timeline-body">'
                . $event['value']
                . ' <div class="timeline-footer">'
                . '</div>'
                . '</div>';
        }
        return $result;
    }
}