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

namespace NextDom\Controller\Tools\Markets;

use NextDom\Controller\BaseController;
use NextDom\Helpers\Render;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\MessageManager;

/**
 * Class MarketController
 * @package NextDom\Controller\Tools\Markets
 */
class MarketController extends BaseController
{
    /**
     * Render market page
     *
     * @param array $pageData Page data
     *
     * @return string Content of market page
     *
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        global $NEXTDOM_INTERNAL_CONFIG;

        $sourcesList = [];

        foreach ($NEXTDOM_INTERNAL_CONFIG['nextdom']['sources'] as $source) {
            // TODO: Limiter les requêtes
            if (ConfigManager::byKey('nextdom::' . $source['code']) == 1) {
                $sourcesList[] = $source;
            }
        }

        $pageData['JS_VARS']['github'] = ConfigManager::byKey('github::enable');
        $pageData['JS_VARS_RAW']['sourcesList'] = Utils::getArrayToJQueryJson($sourcesList);
        $pageData['JS_VARS']['moreInformationsStr'] = __("Plus d'informations");
        $pageData['JS_VARS']['updateStr'] = __("Mettre à jour");
        $pageData['JS_VARS']['updateAllStr'] = __("Voulez-vous mettre à jour tous les plugins ?");
        $pageData['JS_VARS']['updateThisStr'] = __("Voulez-vous mettre à jour ce plugin ?");
        $pageData['JS_VARS']['installedPluginStr'] = __("Plugin installé");
        $pageData['JS_VARS']['updateAvailableStr'] = __("Mise à jour disponible");
        $pageData['marketSourcesList'] = $sourcesList;
        $pageData['marketSourcesFilter'] = ConfigManager::byKey('nextdom::show_sources_filters');

        // Affichage d'un message à un utilisateur
        if (isset($_GET['message'])) {
            $messages = [
                __('La mise à jour du plugin a été effecutée.'),
                __('Le plugin a été supprimé')
            ];

            $messageIndex = intval($_GET['message']);
            if ($messageIndex < count($messages)) {
                MessageManager::add('core', $messages[$messageIndex]);
            }
        }
        $pageData['CSS_POOL'][] = '/public/css/pages/markets.css';
        $pageData['JS_END_POOL'][] = '/public/js/desktop/tools/markets/market.js';

        return Render::getInstance()->get('/desktop/tools/markets/market.html.twig', $pageData);
    }

}
