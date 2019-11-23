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
use NextDom\Managers\ListenerManager;

/**
 * Class ListenerAjax
 * @package NextDom\Ajax
 */
class ListenerAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function save()
    {
        Utils::processJsonObject('listener', Utils::init('listeners'));
        $this->ajax->success();
    }

    public function remove()
    {
        $listener = ListenerManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($listener)) {
            throw new CoreException(__('Listerner id inconnu'));
        }
        $listener->remove();
        $this->ajax->success();
    }

    public function all()
    {
        $listeners = Utils::o2a(ListenerManager::all());
        foreach ($listeners as &$listener) {
            $listener['event_str'] = '';
            foreach ($listener['event'] as $event) {
                $listener['event_str'] .= $event . ',';
            }
            $listener['event_str'] = NextDomHelper::toHumanReadable(trim($listener['event_str'], ','));
        }
        $this->ajax->success($listeners);
    }

}