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

class ScenarioElementManager
{
    const DB_CLASS_NAME = 'scenarioElement';
    const CLASS_NAME = 'scenarioElement';

    /**
     * Get the element of a scenario from its identifier
     * @param mixed $id Identifier of the scenario element
     * @return mixed
     */
    public static function byId($id)
    {
        $values = array(
            'id' => $id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Sauvegarder un élément Ajax TODO: ???
     *
     * @param mixed $ajaxElement ????
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public static function saveAjaxElement($ajaxElement)
    {
        if (isset($ajaxElement['id']) && $ajaxElement['id'] != '') {
            $elementDb = self::byId($ajaxElement['id']);
        } else {
            $elementDb = new \scenarioElement();
        }
        if (!isset($elementDb) || !is_object($elementDb)) {
            throw new \Exception(__('Elément inconnu. Vérifiez l\'ID : ') . $ajaxElement['id']);
        }
        \utils::a2o($elementDb, $ajaxElement);
        $elementDb->save();
        $subElementOrder = 0;
        $subElementList = $elementDb->getSubElement();
        $enabledSubElement = array();
        foreach ($ajaxElement['subElements'] as $ajaxSubElement) {
            if (isset($ajaxSubElement['id']) && $ajaxSubElement['id'] != '') {
                $subElementDb = ScenarioSubElementManager::byId($ajaxSubElement['id']);
            } else {
                $subElementDb = new \scenarioSubElement();
            }
            if (!isset($subElementDb) || !is_object($subElementDb)) {
                throw new \Exception(__('Elément inconnu. Vérifiez l\'ID : ') . $ajaxSubElement['id']);
            }
            \utils::a2o($subElementDb, $ajaxSubElement);
            $subElementDb->setScenarioElement_id($elementDb->getId());
            $subElementDb->setOrder($subElementOrder);
            $subElementDb->save();
            $subElementOrder++;
            $enabledSubElement[$subElementDb->getId()] = true;

            $expressionsList = $subElementDb->getExpression();
            $expressionOrder = 0;
            $enabledExpression = array();
            foreach ($ajaxSubElement['expressions'] as &$expression_ajax) {
                if (isset($expression_ajax['scenarioSubElement_id']) && $expression_ajax['scenarioSubElement_id'] != $subElementDb->getId() && isset($expression_ajax['id']) && $expression_ajax['id'] != '') {
                    $expression_ajax['id'] = '';
                }
                if (isset($expression_ajax['id']) && $expression_ajax['id'] != '') {
                    $expression_db = ScenarioExpressionManager::byId($expression_ajax['id']);
                } else {
                    $expression_db = new \scenarioExpression();
                }
                if (!isset($expression_db) || !is_object($expression_db)) {
                    throw new \Exception(__('Expression inconnue. Vérifiez l\'ID : ') . $expression_ajax['id']);
                }
                $expression_db->emptyOptions();
                \utils::a2o($expression_db, $expression_ajax);
                $expression_db->setScenarioSubElement_id($subElementDb->getId());
                if ($expression_db->getType() == 'element') {
                    $expression_db->setExpression(self::saveAjaxElement($expression_ajax['element']));
                }
                $expression_db->setOrder($expressionOrder);
                $expression_db->save();
                $expressionOrder++;
                $enabledExpression[$expression_db->getId()] = true;
            }
            foreach ($expressionsList as $expresssion) {
                if (!isset($enabledExpression[$expresssion->getId()])) {
                    $expresssion->remove();
                }
            }
        }
        foreach ($subElementList as $subElement) {
            if (!isset($enabledSubElement[$subElement->getId()])) {
                $subElement->remove();
            }
        }

        return $elementDb->getId();
    }
}
