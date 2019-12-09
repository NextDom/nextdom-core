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

namespace NextDom\Controller\Modals;

use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\Render;

/**
 * Class IconSelector
 * @package NextDom\Controller\Modals
 */
class IconSelector extends BaseAbstractModal
{
    /**
     * Render icon selector modal
     *
     * @return string
     */
    public static function get(): string
    {
        $pageData = [];
        $pageData['iconsList'] = [];
        foreach (FileSystemHelper::ls('public/icon', '*') as $iconDirectory) {
            if (is_dir('public/icon/' . $iconDirectory) && file_exists('public/icon/' . $iconDirectory . '/style.css')) {
                $cssFileContent = file_get_contents('public/icon/' . $iconDirectory . '/style.css');
                $research = strtolower(str_replace('/', '', $iconDirectory));
                $pageData['iconsList'][] = self::getIconsData($iconDirectory, $cssFileContent, "/\." . $research . "-(.*?):/");
            }
        }
        /*
        $nodeModules = [
            ['name' => 'Font-Awesome-5', 'path' => 'vendor/node_modules/@fortawesome/fontawesome-free/css/', 'cssFile' => 'all.css', 'cssPrefix' => 'fa']
        ];
        foreach ($nodeModules as $nodeModule) {
            if (is_dir($nodeModule['path']) && file_exists($nodeModule['path'] . $nodeModule['cssFile'])) {
                $cssFileContent = file_get_contents($nodeModule['path'] . $nodeModule['cssFile']);
                $pageData['iconsList'][] = self::getIconsData($nodeModule['path'], $cssFileContent, "/\." . $nodeModule['cssPrefix'] . "-(.*?):/", $nodeModule['name'], $nodeModule['cssPrefix']);
            }
        }
        */
        // Font Awesome 5
        $fontAwesomeTypes = ['far' => 'regular', 'fas' => 'solid', 'fab' => 'brands'];
        foreach ($fontAwesomeTypes as $cssCode => $svgFolder) {
            $data = [];
            $data['name'] = 'Font-Awesome-5-' . $svgFolder;
            $fileList = FileSystemHelper::ls('vendor/node_modules/@fortawesome/fontawesome-free/svgs/' . $svgFolder, '*');
            $data['height'] = (ceil(count($fileList) / 14) * 40) + 80;
            $data['list'] = [];
            foreach ($fileList as $svgFile) {
                $data['list'][] = $cssCode . ' fa-' . str_replace('.svg', '', $svgFile);
            }
            $pageData['iconsList'][] = $data;
        }

        return Render::getInstance()->get('/modals/icon.selector.html.twig', $pageData);
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
        preg_match_all($matchPattern, $cssContent, $matchResults, PREG_SET_ORDER);
        if ($name === null) {
            $data['name'] = str_replace('/', '', $path);
        } else {
            $data['name'] = $name;
        }
        $data['height'] = (ceil(count($matchResults) / 14) * 40) + 80;
        $data['list'] = [];
        foreach ($matchResults as $result) {
            if (isset($result[0])) {
                if ($cssClass === null) {
                    $data['list'][] = str_replace([':', '.'], ' ', $result[0]);
                } else {
                    $data['list'][] = $cssClass . str_replace([':', '.'], ' ', $result[0]);
                }
            }
        }
        return $data;
    }
}
