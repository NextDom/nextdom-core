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
use NextDom\Managers\ListenerManager;

class ListenerAjax extends BaseAjax
{
    /**
     * @var string
     */
    protected $NEEDED_RIGHTS     = UserRight::ADMIN;
    /**
     * @var bool
     */
    protected $MUST_BE_CONNECTED = true;
    /**
     * @var bool
     */
    protected $CHECK_AJAX_TOKEN = true;

    /**
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        Utils::unautorizedInDemo();
        Utils::processJsonObject('listener', Utils::init('listeners'));
        AjaxHelper::success();
    }

    /**
     * @throws CoreException
     */
    public function remove()
    {
        Utils::unautorizedInDemo();
        $listener = ListenerManager::byId(Utils::init('id'));
        if (!is_object($listener)) {
            throw new CoreException(__('Listerner id inconnu'));
        }
        $listener->remove();
        AjaxHelper::success();
    }

    /**
     * @throws \ReflectionException
     */
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
        AjaxHelper::success($listeners);
    }

}
