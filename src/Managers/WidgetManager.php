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

use NextDom\Enums\Common;
use NextDom\Enums\NextDomObj;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Managers\Parents\BaseManager;
use NextDom\Managers\Parents\CommonManager;
use NextDom\Managers\CmdManager;
use NextDom\Model\Entity\Widget;
use NextDom\Enums\CmdViewType;

/**
 * Class WidgetManager
 * @package NextDom\Managers
 */
class WidgetManager extends BaseManager {

    use CommonManager;

    const CLASS_NAME = Widget::class;
    const DB_CLASS_NAME = '`widget`';


    /**
     * @param string $_type
     * @return Widget[]|null
     * @throws \Exception
     */
    public static function all($_type = '') {
        $values = [];
        if ($_type === '') {
            $sql = static::getBaseSQL();
        } else if ($_type === null) {
            $sql = static::getBaseSQL() . '
                    WHERE (`type` IS NULL OR `type` = "")';
        } else {
            $values['type'] = $_type;
            $sql = static::getBaseSQL() . '
                    WHERE `type` = :type';
        }
        $sql .= ' ORDER BY `name`';
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param string $_id
     * @return Widget|null
     * @throws \Exception
     */
    public static function byId($_id) {
        $values = array(
            'id' => $_id,
        );
        $sql = 'SELECT ' . DBHelper::buildField(static::CLASS_NAME) . ' FROM ' . self::DB_CLASS_NAME . '
                WHERE id=:id';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }


    /**
     * @param string $_type
     * @param string $_subtype
     * @param string $_name
     * @return Widget|null
     * @throws \Exception
     */
    public static function byTypeSubtypeAndName($_type, $_subtype, $_name) {
        $values = array(
            'type' => $_type,
            'subtype' => $_subtype,
            'name' => $_name,
        );
        $sql = 'SELECT ' . DBHelper::buildField(static::CLASS_NAME) . ' FROM ' . self::DB_CLASS_NAME . '
            WHERE type=:type
            AND subtype=:subtype
            AND name=:name';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param null $_type
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function listType($_type = null) {
        $values = [];
        $sql = 'SELECT DISTINCT(`type`)
                FROM ' . self::DB_CLASS_NAME;
        if ($_type !== null) {
            $values['type'] = '%' . $_type . '%';
            $sql .= ' WHERE `type` LIKE :type';
        }
        $sql .= ' ORDER BY `type`';
        return DBHelper::getAll($sql, $values);
    }

    public static function listTemplate() {
        $return = array();
        $files = ls(__DIR__ . '/../template/dashboard', 'cmd.*', false, array('files', 'quiet'));
        foreach ($files as $file) {
            $informations = explode('.', $file);
            if (count($informations) < 4) {
                continue;
            }
            if (stripos($informations[3], 'tmpl') === false) {
                continue;
            }
            if (!file_exists(__DIR__ . '/../template/mobile/' . $file)) {
                continue;
            }
            if (!isset($return[$informations[1]])) {
                $return[$informations[1]] = array();
            }
            if (!isset($return[$informations[1]][$informations[2]])) {
                $return[$informations[1]][$informations[2]] = array();
            }
            if (isset($informations[3])) {
                $return[$informations[1]][$informations[2]][] = $informations[3];
            }
        }
        $files = ls(__DIR__ . '/../../data/customTemplates/dashboard', 'cmd.*', false, array('files', 'quiet'));
        foreach ($files as $file) {
            $informations = explode('.', $file);
            if (count($informations) < 4) {
                continue;
            }
            if (stripos($informations[3], 'tmpl') === false) {
                continue;
            }
            if (!file_exists(__DIR__ . '/../../data/customTemplates/mobile/' . $file)) {
                continue;
            }
            if (!isset($return[$informations[1]])) {
                $return[$informations[1]] = array();
            }
            if (!isset($return[$informations[1]][$informations[2]])) {
                $return[$informations[1]][$informations[2]] = array();
            }
            if (isset($informations[3])) {
                $return[$informations[1]][$informations[2]][] = $informations[3];
            }
        }
        return $return;
    }

    public static function loadConfig($_template) {
        $template = FileSystemHelper::getCoreTemplateFileContent(CmdViewType::DASHBOARD, $_template);
        if (!isset($template)) {
            $templatePath = NEXTDOM_DATA . '/data/customTemplates/dashboard/' . $_template . '.html';
            if (file_exists($templatePath)) {
                $template = file_get_contents($templatePath);
            } else {
                return null;
            }
        }
        $result = ['test' => false];
        if (strpos($template, '#test#') !== false) {
            $result['test'] = true;
        }
        preg_match_all("/#_([a-zA-Z_]*)_#/", $template, $matches);
        if (count($matches[1]) == 0) {
            return $result;
        }
        $result['replace'] = array_values(array_unique($matches[1]));
        return $result;
    }

    public static function replacement($version, $replace, $by) {
        $cmds = CmdManager::searchTemplate($version . '":"' . $replace . '"');
        if (!is_array($cmds) || count($cmds) === 0) {
            return 0;
        }
        $replaceCount = 0;
        foreach ($cmds as $cmd) {
            if ($cmd->getTemplate($version) == $replace) {
                $cmd->setTemplate($version, $by);
                $cmd->save();
                $replaceCount++;
            }
        }
        return $replaceCount;
    }

    /**
     * Check if widget is available
     *
     * @param string $version Display version
     * TODO A factoriser
     * @return array
     * @throws \Exception
     */
    public static function availableWidget($version) {
        global $NEXTDOM_INTERNAL_CONFIG;
        $result = [];
        $lsOptions = ['files', 'quiet'];
        $widgetFileSearch = 'cmd.*';
        $widgetLocationPaths = [];
        foreach ($NEXTDOM_INTERNAL_CONFIG[NextDomObj::CMD][NextDomObj::WIDGET] as $type => $data1) {
            foreach ($data1 as $subtype => $data2) {
                foreach ($data2 as $name => $data3) {
                    if (!isset($result[$type])) {
                        $result[$type] = [];
                    }
                    if (!isset($result[$type][$subtype])) {
                        $result[$type][$subtype] = [];
                    }
                    $result[$type][$subtype][Common::CORE][$name] = [Common::ICON => $data3[Common::ICON], Common::NAME => $name, Common::LOCATION => Common::CORE, Common::TYPE => Common::TEMPLATE];
                }
            }
        }
        foreach (PluginManager::listPlugin(false, false, false) as $plugin) {
            $pluginId = $plugin->getId();
            $widgetLocationPaths[] = ['path' => NEXTDOM_ROOT . '/plugins/' . $pluginId . '/core/template/' . $version, Common::LOCATION => $pluginId, Common::TYPE => Common::PLUGIN . ' - ' . $pluginId];
            if (method_exists($pluginId, 'templateWidget')) {
                foreach ($pluginId::templateWidget() as $type => $data1) {
                    foreach ($data1 as $subtype => $data2) {
                        foreach ($data2 as $name => $data3) {
                            if (!isset($result[$type])) {
                                $result[$type] = [];
                            }
                            if (!isset($result[$type][$subtype])) {
                                $result[$type][$subtype] = [];
                            }
                            $result[$type][$subtype][Common::PLUGIN . ' - ' . $pluginId][$pluginId . '::' . $name] = array(Common::NAME => $name, Common::LOCATION => $pluginId, Common::TYPE => Common::PLUGIN);
                        }
                    }
                }
            }
        }
        $widgetLocationPaths[] = ['path' => NEXTDOM_ROOT . '/views/templates/' . $version, Common::LOCATION => Common::CORE, Common::TYPE => Common::CORE];
        $widgetLocationPaths[] = ['path' => NEXTDOM_ROOT . '/plugins/widget/core/template/' . $version, Common::LOCATION => Common::WIDGET, Common::TYPE => Common::WIDGET];
        foreach ($widgetLocationPaths as $widgetLocationPath) {
            if (file_exists($widgetLocationPath['path'])) {
                $files = FileSystemHelper::ls($widgetLocationPath['path'], $widgetFileSearch, false, $lsOptions);
                foreach ($files as $file) {
                    $informations = explode('.', $file);
                    if (count($informations) > 3 && stripos($informations[3], 'tmpl') === false) {
                        if (!isset($result[$informations[1]])) {
                            $result[$informations[1]] = [];
                        }
                        if (!isset($result[$informations[1]][$informations[2]])) {
                            $result[$informations[1]][$informations[2]] = [];
                        }
                        if (!isset($result[$informations[1]][$informations[2]][$informations[3]])) {
                            $result[$informations[1]][$informations[2]][$widgetLocationPath['type']][$informations[3]] = [Common::NAME => $informations[3], Common::LOCATION => $widgetLocationPath[Common::LOCATION], Common::TYPE => $widgetLocationPath['type']];
                        }
                    }
                }
            }
        }
        foreach (self::all() as $widget) {
            if (!isset($result[$widget->getType()])) {
                $result[$widget->getType()] = [];
            }
            if (!isset($result[$widget->getType()][$widget->getSubtype()])) {
                $result[$widget->getType()][$widget->getSubtype()] = [];
            }
            $result[$widget->getType()][$widget->getSubtype()][Common::CUSTOM][$widget->getName()] = [Common::NAME => $widget->getName(), Common::LOCATION => Common::CUSTOM, Common::TYPE => Common::CUSTOM];
        }
        return $result;
    }
}
