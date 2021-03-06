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
use NextDom\Enums\Common;
use NextDom\Enums\NextdomObj;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\WidgetManager;
use NextDom\Model\Entity\Widget;

/**
 * Class WidgetAjax
 * @package NextDom\Ajax
 */
class WidgetAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function all()
    {
        $results = Utils::o2a(WidgetManager::all());
        foreach ($results as &$result) {
            if (isset($result['link_type']) && $result['link_type'] == NextdomObj::CMD && $result['link_id'] != '') {
                $linkId = '';
                foreach (explode('&&', $result['link_id']) as $cmdId) {
                    $cmd = CmdManager::byId($cmdId);
                    if (is_object($cmd)) {
                        $linkId .= CmdManager::cmdToHumanReadable('#' . $cmd->getId() . '# && ');
                    }

                }
                $result['link_id'] = trim(trim($linkId), '&&');
            }
        }
        $this->ajax->success($results);
    }

    public function byId()
    {
        $widget = WidgetManager::byId(Utils::init(AjaxParams::ID));
        $result = Utils::o2a($widget);
        if (is_object($widget)) {
            $usedByList = $widget->getUsedBy();
            foreach ($usedByList as $cmd) {
              $result['usedByList'][$cmd->getId()] = $cmd->getHumanName();
            }
        }
        $this->ajax->success(NextDomHelper::toHumanReadable($result));
    }

    public function getPreview($usedByCmdForPreview = null)
    {
        if($usedByCmdForPreview === null) {
            $widget = WidgetManager::byId(Utils::init(AjaxParams::ID));
            if (is_object($widget))
            {
                $usedByCmdList = $widget->getUsedBy();
                if (!empty($usedByCmdList)) {
                    $usedByCmdForPreview = $usedByCmdList[0];
                }
            }
        }
        if(isset($usedByCmdForPreview)){
            $this->ajax->success(['html' => $usedByCmdForPreview->getEqLogic()->toHtml('dashboard')]);
        }
        $this->ajax->error();
    }

    public function loadConfig()
    {
        $this->ajax->success(WidgetManager::loadConfig(Utils::init(AjaxParams::TEMPLATE)));
    }

    public function save()
    {
        $widget_json = NextDomHelper::fromHumanReadable(json_decode(Utils::init(AjaxParams::WIDGET), true));
        if (isset($widget_json['id'])) {
            $widget = WidgetManager::byId($widget_json['id']);
        }
        if (!isset($widget) || !is_object($widget)) {
            $widget = new Widget();
        }
        $widget->emptyTest();
        Utils::a2o($widget, $widget_json);
        $widget->save();
        $this->ajax->success(Utils::o2a($widget));
    }

    public function remove()
    {
        $widget = WidgetManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($widget)) {
            throw new CoreException(__('Widget inconnue. Vérifiez l\'ID'));
        }
        $widget->remove();
        $this->ajax->success();
    }

    public function replacement()
    {
        $this->ajax->success(WidgetManager::replacement(Utils::init(Common::VERSION), Utils::init(Common::REPLACE), Utils::init('by')));
    }
}