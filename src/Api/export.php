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

use NextDom\Enums\AjaxParams;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\EqLogicManager;

require_once __DIR__ . '/../../src/core.php';

AuthentificationHelper::init();
AuthentificationHelper::isConnectedOrFail();

$type = Utils::init(AjaxParams::TYPE);
$id = Utils::init('id');
switch ($type) {
    case 'cmdHistory':
        /** @var Cmd $cmd */
        $cmd = CmdManager::byId($id);
        if (!is_object($cmd)) {
            throw new Exception(__('Commande introuvable : ') . $id);
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . str_replace(' ', '_', $cmd->getHumanName()) . '.csv');
        $histories = $cmd->getHistory();
        foreach ($histories as $history) {
            echo $history->getDatetime() . ';' . str_replace('.', ',', $history->getValue()) . "\n";
        }
        break;
    case 'eqLogic':
        /** @var EqLogic $eqLogic */
        $eqLogic = EqLogicManager::byId(init('id'));
        if (!is_object($eqLogic)) {
            throw new Exception(__('Commande introuvable : ') . $id);
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $eqLogic->getHumanName() . '.json');
        echo json_encode($eqLogic->export(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        break;
    default:
        break;
}
