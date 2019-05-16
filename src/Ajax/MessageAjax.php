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
use NextDom\Helpers\Utils;
use NextDom\Managers\MessageManager;

class MessageAjax extends BaseAjax
{
    /**
     * @var string
     */
    protected $NEEDED_RIGHTS     = UserRight::USER;
    /**
     * @var bool
     */
    protected $MUST_BE_CONNECTED = true;
    /**
     * @var bool
     */
    protected $CHECK_AJAX_TOKEN = true;

    /**
     *
     */
    public function clearMessage()
    {
        MessageManager::removeAll(Utils::init('plugin'));
        AjaxHelper::success();
    }

    /**
     *
     */
    public function nbMessage()
    {
        AjaxHelper::success(MessageManager::nbMessage());
    }

    /**
     * @throws \ReflectionException
     */
    public function all()
    {
        if (Utils::init('plugin') == '') {
            $messages = Utils::o2a(MessageManager::all());
        } else {
            $messages = Utils::o2a(MessageManager::byPlugin(Utils::init('plugin')));
        }
        foreach ($messages as &$message) {
            $message['message'] = htmlentities($message['message']);
        }
        AjaxHelper::success($messages);
    }

    /**
     * @throws CoreException
     */
    public function removeMessage()
    {
        $message = MessageManager::byId(Utils::init('id'));
        if (!is_object($message)) {
            throw new CoreException(__('Message inconnu. Vérifiez l\'ID'));
        }
        $message->remove();
        AjaxHelper::success();
    }
}
