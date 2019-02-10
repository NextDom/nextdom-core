<?php

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 *
 * @Support <https://www.nextdom.org>
 * @Email   <admin@nextdom.org>
 * @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
 */

namespace NextDom\Controller;

use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\Render;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UpdateManager;

class NetworkController extends BaseController
{
    /**
     * Render network page
     *
     * @param Render $render Render engine
     * @param array $pageData Page data
     *
     * @return string Content of network page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, &$pageData): string
    {
        $pageData['adminReposList'] = UpdateManager::listRepo();
        $keys = array('dns::token', 'market::allowDNS');
        foreach ($pageData['adminReposList'] as $key => $value) {
            $keys[] = $key . '::enable';
        }
        $pageData['adminConfigs'] = ConfigManager::byKeys($keys);
        $pageData['adminNetworkInterfaces'] = [];
        foreach (NetworkHelper::getInterfacesList() as $interface) {
            $intData = [];
            $intData['name'] = $interface;
            $intData['mac'] = NetworkHelper::getInterfaceMac($interface);
            $intData['ip'] = NetworkHelper::getInterfaceIp($interface);
            $pageData['adminNetworkInterfaces'][] = $intData;
        }
        $pageData['adminDnsRun'] = NetworkHelper::dnsRun();
        $pageData['adminNetworkExternalAccess'] = NetworkHelper::getNetworkAccess('external');

        $pageData['JS_END_POOL'][] = '/public/js/desktop/admin/network.js';
        $pageData['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/network.html.twig', $pageData);
    }
}
