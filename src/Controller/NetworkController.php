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

use NextDom\Helpers\Status;

use NextDom\Helpers\Render;
use NextDom\Managers\UpdateManager;

class NetworkController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }
    
    /**
     * Render network page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of network page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {

        $pageContent['adminReposList'] = UpdateManager::listRepo();
        $keys = array('dns::token', 'market::allowDNS');
        foreach ($pageContent['adminReposList'] as $key => $value) {
            $keys[] = $key . '::enable';
        }
        $pageContent['adminConfigs'] = \config::byKeys($keys);
        $pageContent['adminNetworkInterfaces'] = [];
        foreach (\network::getInterfaces() as $interface) {
            $intData = [];
            $intData['name'] = $interface;
            $intData['mac'] = \network::getInterfaceMac($interface);
            $intData['ip'] = \network::getInterfaceIp($interface);
            $pageContent['adminNetworkInterfaces'][] = $intData;
        }
        $pageContent['adminDnsRun'] = \network::dns_run();
        $pageContent['adminNetworkExternalAccess'] = \network::getNetworkAccess('external');

        $pageContent['JS_END_POOL'][] = '/public/js/desktop/admin/network.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/admin/network.html.twig', $pageContent);
    }
}
