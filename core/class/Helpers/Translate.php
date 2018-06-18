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

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class Translate
{
    /**
     * @var array Données de traduction
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
     * Obtenir une des informations de la configuration liée à la traduction
     *
     * @param $informationKey
     * @param string $defaultValue
     * @return mixed|string
     */
    public static function getConfig(string $informationKey, string $defaultValue = '') : string
    {
        $result = $defaultValue;
        // Lecture et mise en cache de la configuration
        if (self::$config === null) {
            self::$config = \config::byKeys(array('language', 'generateTranslation'), 'core', array('language' => 'fr_FR'));
        }
        // Recherche de l'information
        if (isset(self::$config[$informationKey])) {
            $result = self::$config[$informationKey];
        }
        return $result;
    }

    /**
     * Obtenir la langue configurée.
     * fr_FR par défaut
     *
     * @return string Langue
     */
    public static function getLanguage() : string
    {
        if (self::$language === null) {
            self::$language = self::getConfig('language', 'fr_FR');
        }
        return self::$language;
    }

    /**
     * Charge les informations de traduction
     * TODO: Même celles de tous les plugins. Les plugins sont chargés à l'ancienne
     * @return array Données chargées
     */
    public static function loadTranslation() : array
    {
        $result = array();
        $language = self::getLanguage();
        $filename = self::getPathTranslationFile($language);
        if (file_exists($filename)) {
            self::$translator = new Translator($language);
            self::$translator->addLoader('yaml', new YamlFileLoader());
            self::$translator->addResource('yaml', $filename, $language);
            foreach (\plugin::listPlugin(false, false, false) as $plugin) {
                $result = array_merge($result, $plugin->getTranslation($language));
            }
        }
        return $result;
    }

    /**
     * Obtenir les traductions pour la langue courante
     *
     * @return mixed
     */
    public static function getTranslation() : array
    {
        // Test si les traductions ont été mises en cache
        if (!self::$translationLoaded) {
            self::$translation = array(
                self::getLanguage() => self::loadTranslation(),
            );
            self::$translationLoaded = true;
        }
        return self::$translation[self::getLanguage()];
    }

    /**
     *  Lance la traduction d'un texte
     *
     * @param string $content Contenu à traduire
     * @param string $filename Nom du fichier contenant les informations à traduire (ancienne version)
     * @param bool $_backslash TODO: Comprendre à quoi ça sert
     * @return string Texte traduit
     */
    public static function exec(string $content, string $filename = '', bool $_backslash = false) : string
    {
        if ($content == '') {// || $filename == '') {
            return '';
        }
        $language = self::getLanguage();
        $oldTranslationMode = false;

        $translate = self::getTranslation();
        // Ancienne version pour les plugins
        if (strpos($filename, '/plugins') === 0) {
            $filename = substr($filename, strpos($filename, 'plugins'));
            $oldTranslationMode = true;
        }

        $modify = false;
        $replace = array();
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
                            $translate[$filename] = array();
                        }
                        $translate[$filename][$text] = $text;
                    }
                }
                if ($_backslash && isset($replace["{{" . $text . "}}"])) {
                    $replace["{{" . $text . "}}"] = str_replace("'", "\'", str_replace("\'", "'", $replace["{{" . $text . "}}"]));
                }
                if (!isset($replace["{{" . $text . "}}"]) || is_array($replace["{{" . $text . "}}"])) {
                    $replace["{{" . $text . "}}"] = $text;
                }
            }
        } else {
            foreach ($matches[1] as $text) {
                $replace['{{'.$text.'}}'] = self::$translator->trans($text);
            }
        }
        // TODO: Refaire la génération de fichiers de traduction
        /*
        if ($language == 'fr_FR' && $modify) {
            static::$translation[self::getLanguage()] = $translate;
            self::saveTranslation($language);
        }
        */
        return str_replace(array_keys($replace), $replace, $content);
    }

    /**
     * Traduction direct d'une phrase
     *
     * @param string $sentenceToTranslate Phrase à traduire
     * @param string $filename Nom du fichier appelant
     * @param bool $backslash TODO: Toujours comprendre à quoi ça sert ?
     *
     * @return string Phrase traduite
     */
    public static function sentence(string $sentenceToTranslate, string $filename, bool $backslash = false) : string
    {
        $result = '';
        // Ancienne méthode
        if (strpos($filename, '/plugins') === 0) {
            $result = self::exec("{{" . $sentenceToTranslate . "}}", $filename, $backslash);
        }
        // Nouvelle méthode
        else {
            // S'assure que la traduction est chargée
            self::getTranslation();
            $result = self::$translator->trans($sentenceToTranslate);
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
    public static function getPathTranslationFile(string $language) : string
    {
        //return __DIR__ . '/../i18n/' . $language . '.json';
        return NEXTDOM_ROOT . '/translations/' . $language . '.yml';
    }

    /**
     * TODO: Génération des fichiers de traduction
     */
    public static function saveTranslation()
    {
        $core = array();
        $plugins = array();
        foreach (self::getTranslation(self::getLanguage()) as $page => $translation) {
            if (strpos($page, 'plugins/') === false) {
                $core[$page] = $translation;
            } else {
                $plugin = substr($page, strpos($page, 'plugins/') + 8);
                $plugin = substr($plugin, 0, strpos($plugin, '/'));
                if (!isset($plugins[$plugin])) {
                    $plugins[$plugin] = array();
                }
                $plugins[$plugin][$page] = $translation;
            }
        }
        file_put_contents(self::getPathTranslationFile(self::getLanguage()), json_encode($core, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        foreach ($plugins as $plugin_name => $translation) {
            try {
                $plugin = \plugin::byId($plugin_name);
                $plugin->saveTranslation(self::getLanguage(), $translation);
            } catch (\Exception $e) {

            } catch (\Error $e) {

            }
        }
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
}