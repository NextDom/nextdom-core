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
use NextDom\Helpers\NetworkHelper;
use NextDom\Managers\ConfigManager;

/**
 * Class NetworkAjax
 * @package NextDom\Ajax
 */
class NetworkAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function restartDns()
    {
        ConfigManager::save('market::allowDNS', 1);
        NetworkHelper::dnsStart();
        $this->ajax->success();
    }

    public function stopDns()
    {
        ConfigManager::save('market::allowDNS', 0);
        NetworkHelper::dnsStop();
        $this->ajax->success();
    }

}