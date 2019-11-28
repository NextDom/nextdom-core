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

/**
 * Class Render
 * @package NextDom\Helpers
 */
class Render
{
    const DEFAULT_LANGUAGE = 'fr';
    private static $instance;
    /**
     * @var Translator
     */
    private $translator;
    /**
     * @var \Twig\Environment
     */
    private $twig;
    private $twigLoader;

    private function __construct()
    {
        $language = ConfigManager::byKey('language', 'core', 'fr_FR');
        $this->initTranslation($language);
        $this->initRenderer();
    }

    /**
     * @param $language
     */
    private function initTranslation(string $language)
    {
        $this->translator = new Translator($language, null, NEXTDOM_DATA . '/cache/i18n');
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
        $loader = new \Twig\Loader\FilesystemLoader(realpath('views'));
        $this->twigLoader = $loader;
        $twigConfig = [
            'cache' => NEXTDOM_DATA . '/cache/twig',
            'debug' => $developerMode,
        ];

        if ($developerMode) {
            $twigConfig['auto_reload'] = true;
        }

        $this->twig = new \Twig\Environment($loader, $twigConfig);
        $this->twig->addExtension(new I18nExtension());
        $this->twig->addExtension(new DateExtension($this->translator));
        $this->twig->addExtension(new TextExtension());
        $this->twig->addExtension(new TranslationExtension($this->translator));
        if ($developerMode) {
            $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        }
    }

    /**
     * Get render instance
     *
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
     * @param string $view
     * @param array $data
     */
    public function show($view, $data = [])
    {
        echo $this->get($view, $data);
    }

    /**
     * @param $view
     * @param array $data
     * @return mixed
     */
    public function get($view, $data = [])
    {
        $data['debugbar'] = $this->showDebugBar();
        try {
            return $this->twig->render($view, $data);
        } catch (\Twig\Error\LoaderError $e) {
            echo $e->getMessage();
        } catch (\Twig\Error\RuntimeError $e) {
            echo $e->getMessage();
        } catch (\Twig\Error\SyntaxError $e) {
            echo $e->getMessage();
        }
        return null;
    }

    /**
     * @return bool|\DebugBar\JavascriptRenderer
     */
    private function showDebugBar()
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

    /**
     * @param string $url
     * @return string
     */
    public function getCssHtmlTag(string $url): string
    {
        return '<link href="' . $url . '" rel="stylesheet"/>';
    }
}
