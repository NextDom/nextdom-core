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

use NextDom\Helpers\Utils;
use NextDom\Helpers\PagesController;
use NextDom\Helpers\Render;
use NextDom\Helpers\Status;

class MarketController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        Status::isConnectedAdminOrFail();
    }
    
   /**
     * Render market page
     *
     * @param Render $render Render engine
     * @param array $pageContent Page data
     *
     * @return string Content of market page
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render, array &$pageContent): string
    {

        global $NEXTDOM_INTERNAL_CONFIG;

        $sourcesList = [];

        foreach ($NEXTDOM_INTERNAL_CONFIG['nextdom_market']['sources'] as $source) {
            // TODO: Limiter les requêtes
            if (\config::byKey('nextdom_market::' . $source['code']) == 1) {
                $sourcesList[] = $source;
            }
        }

        $pageContent['JS_VARS']['github'] = \config::byKey('github::enable');
        $pageContent['JS_VARS_RAW']['sourcesList'] = Utils::getArrayToJQueryJson($sourcesList);
        $pageContent['JS_VARS']['moreInformationsStr'] = \__("Plus d'informations");
        $pageContent['JS_VARS']['updateStr'] = \__("Mettre à jour");
        $pageContent['JS_VARS']['updateAllStr'] = \__("Voulez-vous mettre à jour tous les plugins ?");
        $pageContent['JS_VARS']['updateThisStr'] = \__("Voulez-vous mettre à jour ce plugin ?");
        $pageContent['JS_VARS']['installedPluginStr'] = \__("Plugin installé");
        $pageContent['JS_VARS']['updateAvailableStr'] = \__("Mise à jour disponible");
        $pageContent['marketSourcesList'] = $sourcesList;
        $pageContent['marketSourcesFilter'] = \config::byKey('nextdom_market::show_sources_filters');

        // Affichage d'un message à un utilisateur
        if (isset($_GET['message'])) {
            $messages = [
                \__('La mise à jour du plugin a été effecutée.'),
                \__('Le plugin a été supprimé')
            ];

            $messageIndex = intval($_GET['message']);
            if ($messageIndex < count($messages)) {
                \message::add('core', $messages[$messageIndex]);
            }
        }

        $pageContent['CSS_POOL'][] = '/public/css/market.css';
        $pageContent['JS_END_POOL'][] = '/public/js/desktop/Market/market.js';
        $pageContent['JS_END_POOL'][] = '/public/js/adminlte/utils.js';

        return $render->get('/desktop/market.html.twig', $pageContent);
    }
    
}
