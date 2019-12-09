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

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\Utils;
use NextDom\Model\Entity\ScenarioElement;
use NextDom\Model\Entity\ScenarioExpression;
use NextDom\Model\Entity\ScenarioSubElement;

/**
 * Class ScenarioElementManager
 * @package NextDom\Managers
 */
class ScenarioElementManager
{
    const DB_CLASS_NAME = '`scenarioElement`';
    const CLASS_NAME = ScenarioElement::class;

    /**
     * Sauvegarder un élément Ajax @TODO: ???
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
            $elementDb = new ScenarioElement();
        }
        if (!isset($elementDb) || !is_object($elementDb)) {
            throw new CoreException(__('Elément inconnu. Vérifiez l\'ID : ') . $ajaxElement['id']);
        }
        Utils::a2o($elementDb, $ajaxElement);
        $elementDb->save();
        $subElementOrder = 0;
        $subElementList = $elementDb->getSubElement();
        $enabledSubElement = [];
        foreach ($ajaxElement['subElements'] as $ajaxSubElement) {
            if (isset($ajaxSubElement['id']) && $ajaxSubElement['id'] != '') {
                $subElementDb = ScenarioSubElementManager::byId($ajaxSubElement['id']);
            } else {
                $subElementDb = new ScenarioSubElement();
            }
            if (!isset($subElementDb) || !is_object($subElementDb)) {
                throw new CoreException(__('Elément inconnu. Vérifiez l\'ID : ') . $ajaxSubElement['id']);
            }
            Utils::a2o($subElementDb, $ajaxSubElement);
            $subElementDb->setScenarioElement_id($elementDb->getId());
            $subElementDb->setOrder($subElementOrder);
            $subElementDb->save();
            $subElementOrder++;
            $enabledSubElement[$subElementDb->getId()] = true;

            $expressionsList = $subElementDb->getExpression();
            $expressionOrder = 0;
            $enabledExpression = [];
            foreach ($ajaxSubElement['expressions'] as &$expression_ajax) {
                if (isset($expression_ajax['scenarioSubElement_id']) && $expression_ajax['scenarioSubElement_id'] != $subElementDb->getId() && isset($expression_ajax['id']) && $expression_ajax['id'] != '') {
                    $expression_ajax['id'] = '';
                }
                if (isset($expression_ajax['id']) && $expression_ajax['id'] != '') {
                    $expression_db = ScenarioExpressionManager::byId($expression_ajax['id']);
                } else {
                    $expression_db = new ScenarioExpression();
                }
                if (!isset($expression_db) || !is_object($expression_db)) {
                    throw new CoreException(__('Expression inconnue. Vérifiez l\'ID : ') . $expression_ajax['id']);
                }
                $expression_db->emptyOptions();
                Utils::a2o($expression_db, $expression_ajax);
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

    /**
     * Get the element of a scenario from its identifier
     * @param mixed $id Identifier of the scenario element
     * @return ScenarioElement
     * @throws \Exception
     */
    public static function byId($id)
    {
        $values = [
            'id' => $id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }
}
