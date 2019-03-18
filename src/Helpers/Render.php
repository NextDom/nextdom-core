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

use DebugBar\DataCollector;
use DebugBar\StandardDebugBar;
use NextDom\Managers\ConfigManager;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Twig\Extensions\DateExtension;
use Twig\Extensions\I18nExtension;
use Twig\Extensions\TextExtension;
use Twig_Environment;
use Twig_Error_Loader;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;

class Render
{
    const DEFAULT_LANGUAGE = 'fr';

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Twig_Environment
     */
    private $twig;

    private $twigLoader;

    private static $instance;

    private function __construct()
    {
        $language = ConfigManager::byKey('language', 'core', 'fr_FR');
        $this->initTranslation($language);
        $this->initRenderer();
    }

    private function initTranslation($language)
    {
        $this->translator = new Translator($language, null, NEXTDOM_ROOT . '/var/cache/i18n');
        $this->translator->addLoader('yaml', new YamlFileLoader());
        $filename = NEXTDOM_ROOT . '/translations/' . $language . '.yml';
        if (file_exists($filename)) {
            $this->translator->addResource('yaml', $filename, $language);
        }
    }

    /**
     *
     */
    private function initRenderer()
    {
        $developerMode = AuthentificationHelper::isInDeveloperMode();
        $loader = new Twig_Loader_Filesystem(realpath('views'));
        $this->twigLoader = $loader;
        $twigConfig = [
            'cache' => NEXTDOM_ROOT . '/var/cache/twig',
            'debug' => $developerMode,
        ];

        if ($developerMode) {
            $twigConfig['auto_reload'] = true;
        }

        $this->twig = new Twig_Environment($loader, $twigConfig);
        $this->twig->addExtension(new I18nExtension());
        $this->twig->addExtension(new DateExtension($this->translator));
        $this->twig->addExtension(new TextExtension());
        $this->twig->addExtension(new TranslationExtension($this->translator));
        if ($developerMode) {
            $this->twig->addExtension(new Twig_Extension_Debug());
        }
    }

    /**
     * @return Render
     */
    public static function getInstance(): Render
    {
        if (is_null(self::$instance)) {
            self::$instance = new Render();
        }
        return self::$instance;
    }

    /**
     * @param string $sentence
     * @return string
     */
    public function getTranslation(string $sentence): string
    {
        if (!is_null(self::$instance)) {
            return $this->translator->trans($sentence);
        }
        return $sentence;
    }

    /**
     * @param $view
     * @param array $data
     * @return mixed
     */
    public function get($view, $data = array())
    {
        $data['debugbar'] = $this->showDebugBar($this->twigLoader);
        try {
            return $this->twig->render($view, $data);
        } catch (Twig_Error_Loader $e) {
            echo $e->getMessage();
        } catch (\Twig_Error_Runtime $e) {
            echo $e->getMessage();
        } catch (\Twig_Error_Syntax $e) {
            echo $e->getMessage();
        }
        return null;
    }

    /**
     * @param string $view
     * @param array $data
     */
    public function show($view, $data = array())
    {
        echo $this->get($view, $data);
    }

    /**
     * @param string $url
     * @return string
     */
    public function getCssHtmlTag(string $url): string
    {
        return '<link href="' . $url . '" rel="stylesheet"/>';
    }


    /**
     * @param Twig_Loader_Filesystem $twigLoader
     * @return bool|\DebugBar\JavascriptRenderer
     */
    private function showDebugBar(Twig_Loader_Filesystem $twigLoader)
    {
        $debugBarData = false;
        if (AuthentificationHelper::isInDeveloperMode()) {
            $debugBar = new StandardDebugBar();
            $debugBarRenderer = $debugBar->getJavascriptRenderer();
            try {
                $debugBar->addCollector(new DataCollector\ConfigCollector(ConfigManager::getDefaultConfiguration()['core']));
                $debugBarData = $debugBarRenderer;
            } catch (\DebugBar\DebugBarException $e) {
                echo $e->getMessage();
            }
        }
        return $debugBarData;
    }
}
