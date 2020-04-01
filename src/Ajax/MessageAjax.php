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
use NextDom\Helpers\Utils;
use NextDom\Managers\MessageManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ConfigManager;

/**
 * Class MessageAjax
 * @package NextDom\Ajax
 */
class MessageAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;
    private $iconsCache = ['scenario' => '/public/img/NextDom_Scenario_Gray.png'];

    /**
     * Remove all message
     * @throws \Exception
     */
    public function clearMessage()
    {
        MessageManager::removeAll(Utils::init(AjaxParams::PLUGIN));
        $this->ajax->success();
    }

    public function nbMessage()
    {
        $this->ajax->success(MessageManager::nbMessage());
    }

    /**
     * Add icon data
     * @param $message
     * @throws \Exception
     */
    private function addIcon(&$message) {
        $pluginId = $message['plugin'];
        $message['iconClass'] = '';
        $defaultIcon = '/public/img/NextDom/NextDom_Square_' . ConfigManager::byKey('nextdom::user-icon') . '.png';
        if (!isset($this->iconsCache[$pluginId])) {
            $this->iconsCache[$pluginId] = $defaultIcon;
            try {
                $plugin = PluginManager::byId($pluginId);
                if (is_object($plugin)) {
                    $this->iconsCache[$pluginId] = '/' . $plugin->getPathImgIcon();
                }
            }
            catch (\Throwable $t) {

            }
        }
        if ($this->iconsCache[$pluginId] === $defaultIcon) {
            $message['iconClass'] = 'iconCore';
        }
        $message['icon'] = $this->iconsCache[$pluginId];
    }

    public function all()
    {
        if (Utils::init(AjaxParams::PLUGIN) == '') {
            $messages = Utils::o2a(MessageManager::all());
        } else {
            $messages = Utils::o2a(MessageManager::byPlugin(Utils::init(AjaxParams::PLUGIN)));
        }
        foreach ($messages as &$message) {
            $message['message'] = htmlentities($message['message']);
            $this->addIcon($message);
        }
        $this->ajax->success($messages);
    }

    public function removeMessage()
    {
        $message = MessageManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($message)) {
            throw new CoreException(__('Message inconnu. Vérifiez l\'ID'));
        }
        $message->remove();
        $this->ajax->success();
    }
}
