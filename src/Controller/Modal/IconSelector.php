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

namespace NextDom\Controller\Modal;

use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Helpers\FileSystemHelper;

class IconSelector extends BaseAbstractModal
{

    public function __construct()
    {
        parent::__construct();
        Status::isConnectedOrFail();
    }
    
    /**
     * Render icon selector modal
     *
     * @param Render $render Render engine
     *
     * @throws CoreException
     */
    public function get(Render $render): string
    {
        $pageContent = [];
        $pageContent['iconsList'] = [];
        foreach (FileSystemHelper::ls('public/icon', '*') as $dir) {
            if (is_dir('public/icon/' . $dir) && file_exists('public/icon/' . $dir . '/style.css')) {
                $cssContent = file_get_contents('public/icon/' . $dir . '/style.css');
                $research = strtolower(str_replace('/', '', $dir));
                $pageContent['iconsList'][] = self::getIconsData($dir, $cssContent, "/\." . $research . "-(.*?):/");
            }
        }
        $nodeModules = [
//            ['name' => 'Font-Awesome 4', 'path' => 'vendor/node_modules/font-awesome/css/', 'cssFile' => 'font-awesome.css', 'cssPrefix' => 'fa'],
            ['name' => 'Font-Awesome 5', 'path' => 'vendor/node_modules/font-awesome5/css/', 'cssFile' => 'fontawesome-all.css', 'cssPrefix' => 'fa']
        ];
        foreach ($nodeModules as $nodeModule) {
            echo $nodeModule['name'] . '<br/>';
            if (is_dir($nodeModule['path']) && file_exists($nodeModule['path'] . $nodeModule['cssFile'])) {
                $cssContent = file_get_contents($nodeModule['path'] . $nodeModule['cssFile']);
                $pageContent['iconsList'][] = self::getIconsData($nodeModule['path'], $cssContent, "/\." . $nodeModule['cssPrefix'] . "-(.*?):/", $nodeModule['name'], $nodeModule['cssPrefix']);
            }
        }
        return $render->get('/modals/icon.selector.html.twig', $pageContent);
    }

    /**
     * Get icons data from CSS file
     *
     * @param string $path Path to the CSS file
     * @param string $cssContent Content of the CSS file
     * @param string $matchPattern Pattern for icon matchs
     * @param string|null $name Name of the font
     * @param string|null $cssClass CSS class to add
     *
     * @return array
     */
    private static function getIconsData($path, $cssContent, $matchPattern, $name = null, $cssClass = null)
    {
        $data = [];
        preg_match_all($matchPattern, $cssContent, $matches, PREG_SET_ORDER);
        if ($name === null) {
            $data['name'] = str_replace('/', '', $path);
        } else {
            $data['name'] = $name;
        }
        $data['height'] = (ceil(count($matches) / 14) * 40) + 80;
        $data['list'] = [];
        foreach ($matches as $match) {
            if (isset($match[0])) {
                if ($cssClass === null) {
                    $data['list'][] = str_replace(array(':', '.'), '', $match[0]);
                } else {
                    $data['list'][] = $cssClass . ' ' . str_replace(array(':', '.'), '', $match[0]);
                }
            }
        }
        return $data;
    }
}
