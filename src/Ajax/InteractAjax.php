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

namespace NextDom\Ajax;

use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\InteractQueryManager;
use NextDom\Model\Entity\InteractDef;

class InteractAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function all()
    {
        $results = Utils::o2a(InteractDefManager::all());
        foreach ($results as &$result) {
            // TODO TOus sélectionnés dans tous les cas
            $result['nbInteractQuery'] = count(InteractQueryManager::byInteractDefId($result['id']));
            $result['nbEnableInteractQuery'] = count(InteractQueryManager::byInteractDefId($result['id'], true));
            if ($result['link_type'] == 'cmd' && $result['link_id'] != '') {
                $link_id = '';
                foreach (explode('&&', $result['link_id']) as $cmd_id) {
                    $cmd = CmdManager::byId($cmd_id);
                    if (is_object($cmd)) {
                        $link_id .= CmdManager::cmdToHumanReadable('#' . $cmd->getId() . '# && ');
                    }

                }
                $result['link_id'] = trim(trim($link_id), '&&');
            }
        }
        AjaxHelper::success($results);
    }

    public function byId()
    {
        $result = Utils::o2a(InteractDefManager::byId(Utils::init('id')));
        $result['nbInteractQuery'] = count(InteractQueryManager::byInteractDefId($result['id']));
        $result['nbEnableInteractQuery'] = count(InteractQueryManager::byInteractDefId($result['id'], true));
        AjaxHelper::success(NextDomHelper::toHumanReadable($result));
    }

    public function save()
    {
        Utils::unautorizedInDemo();
        $interact_json = NextDomHelper::fromHumanReadable(json_decode(Utils::init('interact'), true));
        if (isset($interact_json['id'])) {
            $interact = InteractDefManager::byId($interact_json['id']);
        }
        if (!isset($interact) || !is_object($interact)) {
            $interact = new InteractDef();
        }
        Utils::a2o($interact, $interact_json);
        $interact->save();
        AjaxHelper::success(Utils::o2a($interact));
    }

    public function regenerateInteract()
    {
        InteractDefManager::regenerateInteract();
        AjaxHelper::success();
    }

    public function remove()
    {
        Utils::unautorizedInDemo();
        $interact = InteractDefManager::byId(Utils::init('id'));
        if (!is_object($interact)) {
            throw new CoreException(__('Interaction inconnue. Vérifiez l\'ID'));
        }
        $interact->remove();
        AjaxHelper::success();
    }

    public function changeState()
    {
        Utils::unautorizedInDemo();
        $interactQuery = InteractQueryManager::byId(Utils::init('id'));
        if (!is_object($interactQuery)) {
            throw new CoreException(__('InteractQuery ID inconnu'));
        }
        $interactQuery->setEnable(Utils::init('enable'));
        $interactQuery->save();
        AjaxHelper::success();
    }

    public function changeAllState()
    {
        Utils::unautorizedInDemo();
        $interactQueries = InteractQueryManager::byInteractDefId(Utils::init('id'));
        if (is_array($interactQueries)) {
            foreach ($interactQueries as $interactQuery) {
                $interactQuery->setEnable(Utils::init('enable'));
                $interactQuery->save();
            }
        }
        AjaxHelper::success();
    }

    public function execute()
    {
        AjaxHelper::success(InteractQueryManager::tryToReply(Utils::init('query')));
    }
}