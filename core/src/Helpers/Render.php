<?php
/* This file is part of NextDom.
*
* NextDom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* NextDom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with NextDom. If not, see <http://www.gnu.org/licenses/>.
*/

namespace NextDom\Helpers;


use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Extensions_Extension_I18n;
use Symfony\Bridge\Twig\Extension\TranslationExtension;

class Render
{
    const DEFAULT_LANGUAGE = 'fr';

    private $translator;

    private $twig;

    private static $instance;

    private function __construct()
    {
        $language = \config::byKey('language', 'core', 'fr_FR');
        $this->initTranslation($language);
        $this->initRenderer();
    }

    private function initTranslation($language) {
        $this->translator = new Translator($language,  null, NEXTDOM_ROOT.'/var/i10n');
        $this->translator->addLoader('yaml', new YamlFileLoader());
        $filename = NEXTDOM_ROOT.'/translations/'.$language.'.yml';
        if (file_exists($filename)) {
            $this->translator->addResource('yaml', $filename, $language);
        }
    }

    private function initRenderer() {
        $loader = new Twig_Loader_Filesystem(realpath('views'));
        $this->twig   = new Twig_Environment($loader, [
//            'cache' => NEXTDOM_ROOT.'/var/cache/twig'
        ]);
        $this->twig->addExtension(new Twig_Extensions_Extension_I18n());
        $this->twig->addExtension(new TranslationExtension($this->translator));
    }

    public static function getInstance(): Render {
        if (is_null(self::$instance)) {
            self::$instance = new Render();
        }
        return self::$instance;
    }

    public function get($view, $data = array()) {
        return $this->twig->render($view, $data);
    }

    public function show($view, $data = array())
    {
        echo $this->twig->render($view, $data);
    }

    public function getCssHtmlTag($url) {
        return '<link href="'.$url.'" rel="stylesheet"/>';
    }

    public function getJsHtmlTag($url) {
        return '<script src="'.$url.'"></script>';
    }
}