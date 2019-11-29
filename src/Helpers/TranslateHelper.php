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
 */

namespace NextDom\Helpers;

use NextDom\Managers\ConfigManager;
use NextDom\Managers\PluginManager;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * Class TranslateHelper
 * @package NextDom\Helpers
 */
class TranslateHelper
{

    /**
     * @var array Data of translation
     */
    protected static $translation;

    /**
     * @var bool Etat du chargement des traductions
     */
    protected static $translationLoaded = false;

    /**
     * @var string Langage par défaut
     */
    protected static $language = null;

    /**
     * @var Translator Outil de traduction
     */
    protected static $translator = null;

    /**
     * @var array Informations de la configuration
     */
    protected static $config = null;

    /**
     * Traduction direct d'une phrase
     *
     * @param string $sentenceToTranslate Phrase à traduire
     * @param string $filename Nom du fichier appelant
     * @param bool $backslash @TODO: Toujours comprendre à quoi ça sert ?
     *
     * @return string Phrase traduite
     * @throws \Exception
     */
    public static function sentence(string $sentenceToTranslate, string $filename, bool $backslash = false): string
    {
        $result = '';
        // Ancienne méthode
        if (strpos($filename, '/plugins') === 0) {
            $result = self::exec("{{" . $sentenceToTranslate . "}}", $filename, $backslash);
        } // Nouvelle méthode
        else {
            // S'assure que la traduction est chargée
            self::getTranslation('fr_FR');
            $result = self::$translator->trans($sentenceToTranslate);
        }
        return $result;
    }

    /**
     *  Lance la traduction d'un texte
     *
     * @param string $content Contenu à traduire
     * @param string $filename Nom du fichier contenant les informations à traduire (ancienne version)
     * @param bool $backslash @TODO: Comprendre à quoi ça sert
     * @return string Texte traduit
     * @throws \Exception
     */
    public static function exec(string $content, string $filename = '', bool $backslash = false): string
    {
        if ($content == '') {// || $filename == '') {
            return '';
        }

        $oldTranslationMode = false;
        $translate = self::getTranslation('fr_FR');
        // Ancienne version pour les plugins
        $pluginsPos = strpos($filename, 'plugins');
        if ($pluginsPos === 0 || $pluginsPos === 1) {
            $filename = substr($filename, strpos($filename, 'plugins'));
            $oldTranslationMode = true;
        }

        $modify = false;
        $replace = [];
        preg_match_all("/{{(.*?)}}/s", $content, $matches);
        if ($oldTranslationMode) {
            foreach ($matches[1] as $text) {
                if (trim($text) == '') {
                    $replace["{{" . $text . "}}"] = $text;
                }
                if (isset($translate[$filename]) && isset($translate[$filename][$text])) {
                    $replace["{{" . $text . "}}"] = $translate[$filename][$text];
                }
                if (!isset($replace["{{" . $text . "}}"]) && isset($translate['common']) && isset($translate['common'][$text])) {
                    $replace["{{" . $text . "}}"] = $translate['common'][$text];
                }
                if (!isset($replace["{{" . $text . "}}"])) {
                    if (strpos($filename, '#') === false) {
                        $modify = true;
                        if (!isset($translate[$filename])) {
                            $translate[$filename] = [];
                        }
                        $translate[$filename][$text] = $text;
                    }
                }
                if ($backslash && isset($replace["{{" . $text . "}}"])) {
                    $replace["{{" . $text . "}}"] = str_replace("'", "\'", str_replace("\'", "'", $replace["{{" . $text . "}}"]));
                }
                if (!isset($replace["{{" . $text . "}}"]) || is_array($replace["{{" . $text . "}}"])) {
                    $replace["{{" . $text . "}}"] = $text;
                }
            }
        } else {
            foreach ($matches[1] as $text) {
                $replace['{{' . $text . '}}'] = self::$translator->trans($text);
            }
        }
        // @TODO: Refaire la génération de fichiers de traduction
        /*
          if ($language == 'fr_FR' && $modify) {
          static::$translation[self::getLanguage()] = $translate;
          self::saveTranslation($language);
          }
         */
        return str_replace(array_keys($replace), $replace, $content);
    }

    /**
     * Obtenir les traductions pour la langue courante
     *
     * @TODO: Vérifier le chargement pour les plugins
     *
     * @param string $language
     * @return mixed
     * @throws \Exception
     */
    public static function getTranslation($language): array
    {
        // Test si les traductions ont été mises en cache
        if (!self::$translationLoaded) {
            self::$translation = [
                self::getLanguage() => self::loadTranslation(),
            ];
            self::$translationLoaded = true;
        }
        return self::$translation[self::getLanguage()];
    }

    /**
     * Obtenir la langue configurée.
     * fr_FR par défaut
     *
     * @return string Langue
     * @throws \Exception
     */
    public static function getLanguage(): string
    {
        if (self::$language === null || self::$language === '') {
            self::$language = self::getConfig('language', 'fr_FR');
        }
        // @TODO: Pourquoi pas défault getConfig renvoie vide
        if (self::$language === '') {
            self::$language = 'fr_FR';
        }
        return self::$language;
    }

    /**
     * Définir la langue
     *
     * @param string $language Langue
     */
    public static function setLanguage(string $language)
    {
        self::$language = $language;
    }

    /**
     * Obtenir une des informations de la configuration liée à la traduction
     *
     * @param string $informationKey
     * @param string $defaultValue
     * @return mixed|string
     * @throws \Exception
     */
    public static function getConfig(string $informationKey, string $defaultValue = ''): string
    {
        $result = $defaultValue;
        // Lecture et mise en cache de la configuration
        if (self::$config === null) {
            self::$config = ConfigManager::byKeys(['language', 'generateTranslation'], 'core', ['language' => 'fr_FR']);
        }
        // Recherche de l'information
        if (isset(self::$config[$informationKey])) {
            $result = self::$config[$informationKey];
        }
        return $result;
    }

    /**
     * Charge les informations de traduction
     * @TODO: Même celles de tous les plugins. Les plugins sont chargés à l'ancienne
     * @return array Données chargées
     * @throws \Exception
     */
    public static function loadTranslation(): array
    {
        $result = [];
        $language = self::getLanguage();
        $filename = self::getPathTranslationFile($language);
        if (file_exists($filename)) {
            self::$translator = new Translator($language, null, NEXTDOM_DATA . '/cache/i18n');
            self::$translator->addLoader('yaml', new YamlFileLoader());
            self::$translator->addResource('yaml', $filename, $language);
            $pluginsDirList = scandir(NEXTDOM_ROOT . '/plugins');
            foreach ($pluginsDirList as $pluginDir) {
                if ($pluginDir !== '.' && $pluginDir !== '..' && is_dir(NEXTDOM_ROOT . '/plugins/' . $pluginDir)) {
                    $pluginTranslationFile = NEXTDOM_ROOT . '/plugins/' . $pluginDir . '/core/i18n/' . $language . '.json';
                    if (file_exists($pluginTranslationFile)) {
                        $pluginTranslationFileContent = file_get_contents($pluginTranslationFile);
                        if (Utils::isJson($pluginTranslationFileContent)) {
                            $result = array_merge($result, json_decode($pluginTranslationFileContent, true));
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Obtenir le chemin du fichier de traduction d'une langue
     *
     * @param string $language Langue du fichier
     *
     * @return string Chemin vers le fichier
     */
    public static function getPathTranslationFile(string $language): string
    {
        return NEXTDOM_ROOT . '/translations/' . $language . '.yml';
    }

    /**
     * @param $_name
     * @return string
     */
    public static function getPluginFromName($_name)
    {
        if (strpos($_name, 'plugins/') === false) {
            return 'core';
        }
        preg_match_all('/plugins\/(.*?)\//m', $_name, $matches, PREG_SET_ORDER, 0);
        if (isset($matches[0]) && isset($matches[0][1])) {
            return $matches[0][1];
        }
        if (!isset($matches[1])) {
            return 'core';
        }
        return $matches[1];
    }

    /**
     * @TODO: Génération des fichiers de traduction
     */
    public static function saveTranslation()
    {
        $core = [];
        $plugins = [];
        foreach (self::getTranslation(self::getLanguage()) as $page => $translation) {
            if (strpos($page, 'plugins/') === false) {
                $core[$page] = $translation;
            } else {
                $plugin = substr($page, strpos($page, 'plugins/') + 8);
                $plugin = substr($plugin, 0, strpos($plugin, '/'));
                if (!isset($plugins[$plugin])) {
                    $plugins[$plugin] = [];
                }
                $plugins[$plugin][$page] = $translation;
            }
        }
        file_put_contents(self::getPathTranslationFile(self::getLanguage()), json_encode($core, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        foreach ($plugins as $plugin_name => $translation) {
            try {
                $plugin = PluginManager::byId($plugin_name);
                $plugin->saveTranslation(self::getLanguage(), $translation);
            } catch (\Throwable $e) {

            }
        }
    }

}
