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

use NextDom\Enums\AjaxParams;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\InteractQueryManager;
use NextDom\Model\Entity\InteractDef;

/**
 * Class InteractAjax
 * @package NextDom\Ajax
 */
class InteractAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function all()
    {
        $results = Utils::o2a(InteractDefManager::all());
        foreach ($results as &$result) {
            // @TODO TOus sélectionnés dans tous les cas
            $result['nbInteractQuery'] = count(InteractQueryManager::byInteractDefId($result['id']));
            $result['nbEnableInteractQuery'] = count(InteractQueryManager::byInteractDefId($result['id']));
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
        $this->ajax->success($results);
    }

    public function byId()
    {
        $result = Utils::o2a(InteractDefManager::byId(Utils::init(AjaxParams::ID)));
        $result['nbInteractQuery'] = count(InteractQueryManager::byInteractDefId($result['id']));
        $result['nbEnableInteractQuery'] = count(InteractQueryManager::byInteractDefId($result['id']));
        $this->ajax->success(NextDomHelper::toHumanReadable($result));
    }

    public function save()
    {
        $interact_json = NextDomHelper::fromHumanReadable(json_decode(Utils::init('interact'), true));
        if (isset($interact_json['id'])) {
            $interact = InteractDefManager::byId($interact_json['id']);
        }
        if (!isset($interact) || !is_object($interact)) {
            $interact = new InteractDef();
        }
        Utils::a2o($interact, $interact_json);
        $interact->save();
        $this->ajax->success(Utils::o2a($interact));
    }

    public function regenerateInteract()
    {
        InteractDefManager::regenerateInteract();
        $this->ajax->success();
    }

    public function remove()
    {
        $interact = InteractDefManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($interact)) {
            throw new CoreException(__('Interaction inconnue. Vérifiez l\'ID'));
        }
        $interact->remove();
        $this->ajax->success();
    }

    public function changeState()
    {
        $interactQuery = InteractQueryManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($interactQuery)) {
            throw new CoreException(__('InteractQuery ID inconnu'));
        }
        $interactQuery->setEnable(Utils::init(AjaxParams::ENABLE));
        $interactQuery->save();
        $this->ajax->success();
    }

    public function changeAllState()
    {
        $interactQueries = InteractQueryManager::byInteractDefId(Utils::init(AjaxParams::ID));
        if (is_array($interactQueries)) {
            foreach ($interactQueries as $interactQuery) {
                $interactQuery->setEnable(Utils::init(AjaxParams::ENABLE));
                $interactQuery->save();
            }
        }
        $this->ajax->success();
    }

    public function execute()
    {
        $this->ajax->success(InteractQueryManager::tryToReply(Utils::init('query')));
    }
}