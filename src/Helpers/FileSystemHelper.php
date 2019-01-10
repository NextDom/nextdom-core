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

use NextDom\Exceptions\CoreException;

class FileSystemHelper
{
    /**
     * Inclut un fichier à partir de son type et son nom.
     * TODO: Doit être revue
     * @param string $_folder Répertoire du fichier
     * @param string $_filename Nom du fichier
     * @param string $_type Type de fichier
     * @param string $_plugin Nom du plugin ou vide pour le core
     * @param bool $translate
     * @throws CoreException
     */
    public static function includeFile($_folder, $_filename, $_type, $_plugin = '', $translate = false)
    {
        // Aucune particularité pour les 3rdparty
        if ($_folder == '3rdparty') {
            if ($_plugin === '') {
                //TODO : A améliorer avec une Regex en fonction des utilisations
                $router3rdParty = [
                    'bootstrap/css/bootstrap.min' => 'vendor/node_modules/bootstrap/dist/css/bootstrap.min',
                    'bootstrap/js/bootstrap.min' => 'vendor/node_modules/bootstrap/dist/js/bootstrap.min',
                    'codemirror/lib/codemirror' => 'vendor/node_modules/codemirror/lib/codemirror',
                    'codemirror/addon/edit/matchbrackets' => 'vendor/node_modules/codemirror/addon/edit/matchbrackets',
                    'codemirror/mode/htmlmixed/htmlmixed' => 'vendor/node_modules/codemirror/mode/htmlmixed/htmlmixed',
                    'codemirror/mode/clike/clike' => 'vendor/node_modules/codemirror/mode/clike/clike',
                    'codemirror/mode/css/css' => 'vendor/node_modules/codemirror/mode/css/css',
                    'codemirror/mode/javascript/javascript' => 'vendor/node_modules/codemirror/mode/javascript/javascript',
                    'codemirror/mode/php/php' => 'vendor/node_modules/codemirror/mode/php/php',
                    'codemirror/mode/shell/shell' => 'vendor/node_modules/codemirror/mode/shell/shell',
                    'codemirror/mode/python/python' => 'vendor/node_modules/codemirror/mode/python/python',
                    'codemirror/mode/ruby/ruby' => 'vendor/node_modules/codemirror/mode/ruby/ruby',
                    'codemirror/mode/perl/perl' => 'vendor/node_modules/codemirror/mode/perl/perl',
                    'codemirror/mode/xml/xml' => 'vendor/node_modules/codemirror/mode/xml/xml',
                    'jquery/jquery.min' => 'vendor/node_modules/jquery/dist/jquery.min',
                    'datetimepicker/jquery.datetimepicker' => 'vendor/node_modules/jquery-datetimepicker/jquery.datetimepicker',
                    'jquery.fileupload/jquery.fileupload' => 'vendor/node_modules/blueimp-file-upload/js/jquery.fileupload',
                    'jquery.fileupload/jquery.ui.widget' => 'vendor/node_modules/blueimp-file-upload/js/vendor/jquery.ui.widget',
                    'jquery.fileupload/jquery.iframe-transport' => 'vendor/node_modules/blueimp-file-upload/js/jquery.iframe-transport',
                    'jquery.lazyload/jquery.lazyload' => 'vendor/node_modules/jquery-lazyload/jquery.lazyload',
                    'jquery.packery/jquery.packery' => 'vendor/node_modules/packery/dist/packery.pkgd',
                    'jquery.tablesorter/theme.bootstrap' => 'vendor/node_modules/tablesorter/dist/css/theme.bootstrap.min',
                    'jquery.tablesorter/jquery.tablesorter.min' => 'vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.min',
                    'jquery.tablesorter/jquery.tablesorter.widgets.min' => 'vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.widgets.min',
                    'highstock/highstock' => 'vendor/node_modules/highcharts/highstock',
                    'highstock/highcharts-more' => 'vendor/node_modules/highcharts/highcharts-more',
                    'roboto/roboto' => 'vendor/node_modules/roboto-fontface/css/roboto-fontface',
                    'waves/waves.min' => 'vendor/node_modules/node-waves/waves.min',
                    'bootstrap.slider/css/slider' => 'vendor/node_modules/bootstrap-slider/dist/css/bootstrap-slider.min',
                    'bootstrap.slider/js/bootstrap-slider' => 'vendor/node_modules/bootstrap-slider/dist/bootstrap-slider.min',
                    'jquery.ui/jquery-ui.min' => 'vendor/node_modules/jquery-ui-dist/jquery-ui.min',
                    //TODO : A remettre en 3rdparty
                    'jquery.ui/jquery-ui-bootstrap/jquery-ui' => 'assets/css/jquery-ui-bootstrap/jquery-ui.css'
                ];

                if (array_key_exists($_filename, $router3rdParty)) {
                    $_filename = $router3rdParty[$_filename] . '.' . $_type;
                }
                else {
                    $_filename = 'assets/3rdparty/' . $_filename . '.' . $_type;
                }
                $_folder = null;
            }
            else {
                $_filename .= '.'.$_type;
            }
            $type = $_type;
        } else {
            // Tableau de mappage des fichiers
            $config = array(
                'class' => array('/class', '.class.php', 'php'),
                'com' => array('/com', '.com.php', 'php'),
                'repo' => array('/repo', '.repo.php', 'php'),
                'config' => array('/config', '.config.php', 'php'),
                'modal' => array('/modal', '.php', 'php'),
                'modalhtml' => array('/modal', '.html', 'php'),
                'php' => array('/php', '.php', 'php'),
                'css' => array('/css', '.css', 'css'),
                'js' => array('/js', '.js', 'js'),
                'class.js' => array('/js', '.class.js', 'js'),
                'custom.js' => array('/custom', 'custom.js', 'js'),
                'custom.css' => array('/custom', 'custom.css', 'css'),
                'themes.js' => array('/themes', '.js', 'js'),
                'themes.css' => array('/themes', '.css', 'css'),
                'api' => array('/api', '.api.php', 'php'),
                'html' => array('/html', '.html', 'php'),
                'configuration' => array('', '.php', 'php'),
            );
            $_folder .= $config[$_type][0];
            $_filename .= $config[$_type][1];
            $type = $config[$_type][2];
        }
        if ($_plugin != '') {
            $_folder = 'plugins/' . $_plugin . '/' . $_folder;
        }
        /**
         * Modification pour la gestion du dossier public
         */
        if ($_folder === 'desktop/js') {
            $_folder = 'public/js/desktop';
        }
        if ($_folder === null) {
            $path = NEXTDOM_ROOT . '/' . $_filename;
        }
        else {
            $path = NEXTDOM_ROOT . '/' . $_folder . '/' . $_filename;
        }
        if (!file_exists($path)) {
            throw new CoreException('Fichier introuvable : ' . $path, 35486);
        }
        if ($type == 'php') {
            // Les fichiers php sont traduits
            if ($_type != 'class') {
                ob_start();
                require_once $path;
                if (Utils::init('rescue', 0) == 1) {
                    echo str_replace(array('{{', '}}'), '', ob_get_clean());
                } else {
                    if ($translate) {
                        echo translate::exec(ob_get_clean(), $_folder . '/' . $_filename);
                    } else {
                        echo ob_get_clean();
                    }
                }
            } else {
                require_once $path;
            }
        } elseif ($type == 'css') {
            // TODO : MD5
            echo '<link href="' . $_folder . '/' . $_filename . '?md5=' . md5_file($path) . '" rel="stylesheet" />';
        } elseif ($type == 'js') {
            // TODO : MD5
            echo '<script type="text/javascript" src="core/php/getResource.php?file=' . $_folder . '/' . $_filename . '&md5=' . md5_file($path) . '&lang=' . \translate::getLanguage() . '"></script>';
        }
    }

    /**
     * Obtenir le contenu d'un fichier template.
     *
     * @param string $folder Répertoire dans lequel se trouve le fichier de template
     * @param string $version Version du template
     * @param string $filename Nom du fichier
     * @param string $pluginId Identifiant du plugin
     *
     * @return string Contenu du fichier ou une chaine vide.
     */
    public static function getTemplateFilecontent(string $folder, string $version, string $filename, string $pluginId = ''): string
    {
        $result = '';
        $filePath = NEXTDOM_ROOT . '/plugins/' . $pluginId . '/core/template/' . $version . '/' . $filename . '.html';
        if ($pluginId == '') {
            $filePath = NEXTDOM_ROOT . '/' . $folder . '/template/' . $version . '/' . $filename . '.html';
        }
        if (file_exists($filePath)) {
            $result = file_get_contents($filePath);
        }
        return $result;
    }

    public static function hadFileRight($_allowPath, $_path)
    {
        $path = cleanPath($_path);
        foreach ($_allowPath as $right) {
            if (strpos($right, '/') !== false || strpos($right, '\\') !== false) {
                if (strpos($right, '/') !== 0 || strpos($right, '\\') !== 0) {
                    $right = getRootPath() . '/' . $right;
                }
                if (dirname($path) == $right || $path == $right) {
                    return true;
                }
            } else {
                if (basename(dirname($path)) == $right || basename($path) == $right) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function ls($folder = "", $pattern = "*", $recursivly = false, $options = array('files', 'folders'))
    {
        $currentFolder = '';
        if ($folder) {
            $currentFolder = realpath('.');
            if (in_array('quiet', $options)) {
                // If quiet is on, we will suppress the 'no such folder' error
                if (!file_exists($folder)) {
                    return array();
                }

            }
            if (!is_dir($folder) || !chdir($folder)) {
                return array();
            }

        }
        $get_files = in_array('files', $options);
        $get_folders = in_array('folders', $options);
        $both = array();
        $folders = array();
        // Get the all files and folders in the given directory.
        if ($get_files) {
            $both = array();
            foreach (Utils::globBrace($pattern, GLOB_MARK) as $file) {
                if (!is_dir($folder . '/' . $file)) {
                    $both[] = $file;
                }
            }
        }
        if ($recursivly || $get_folders) {
            $folders = glob("*", GLOB_ONLYDIR + GLOB_MARK);
        }

        //If a pattern is specified, make sure even the folders match that pattern.
        $matching_folders = array();
        if ($pattern !== '*') {
            $matching_folders = glob($pattern, GLOB_ONLYDIR + GLOB_MARK);
        }

        //Get just the files by removing the folders from the list of all files.
        $all = array_values(array_diff($both, $folders));
        if ($recursivly || $get_folders) {
            foreach ($folders as $this_folder) {
                if ($get_folders) {
                    //If a pattern is specified, make sure even the folders match that pattern.
                    if ($pattern !== '*') {
                        if (in_array($this_folder, $matching_folders)) {
                            array_push($all, $this_folder);
                        }

                    } else {
                        array_push($all, $this_folder);
                    }

                }

                if ($recursivly) {
                    // Continue calling this function for all the folders
                    $deep_items = self::ls($pattern, $this_folder, $recursivly, $options); # :RECURSION:
                    foreach ($deep_items as $item) {
                        array_push($all, $this_folder . $item);
                    }
                }
            }
        }

        if ($folder && is_dir($currentFolder)) {
            chdir($currentFolder);
        }

        if (in_array('datetime_asc', $options)) {
            global $current_dir;
            $current_dir = $folder;
            usort($all, function ($a, $b) {
                return filemtime($GLOBALS['current_dir'] . '/' . $a) < filemtime($GLOBALS['current_dir'] . '/' . $b);
            });
        }
        if (in_array('datetime_desc', $options)) {
            global $current_dir;
            $current_dir = $folder;
            usort($all, function ($a, $b) {
                return filemtime($GLOBALS['current_dir'] . '/' . $a) > filemtime($GLOBALS['current_dir'] . '/' . $b);
            });
        }

        return $all;
    }

    public static function rcopy($src, $dst, $_emptyDest = true, $_exclude = array(), $_noError = false, $_params = array())
    {
        if (!file_exists($src)) {
            return true;
        }
        if ($_emptyDest) {
            self::rrmdir($dst);
        }
        if (is_dir($src)) {
            if (!file_exists($dst)) {
                @mkdir($dst);
            }
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file != "." && $file != ".." && !in_array($file, $_exclude) && !in_array(realpath($src . '/' . $file), $_exclude)) {
                    if (!self::rcopy($src . '/' . $file, $dst . '/' . $file, $_emptyDest, $_exclude, $_noError, $_params) && !$_noError) {
                        return false;
                    }
                }
            }
        } else {
            if (!in_array(basename($src), $_exclude) && !in_array(realpath($src), $_exclude)) {
                $srcSize = filesize($src);
                if (isset($_params['ignoreFileSizeUnder']) && $srcSize < $_params['ignoreFileSizeUnder']) {
                    if (strpos(realpath($src), 'empty') !== false) {
                        return true;
                    }
                    if (strpos(realpath($src), '.git') !== false) {
                        return true;
                    }
                    if (strpos(realpath($src), '.html') !== false) {
                        return true;
                    }
                    if (strpos(realpath($src), '.txt') !== false) {
                        return true;
                    }
                    if (isset($_params['log']) && $_params['log']) {
                        echo 'Ignore file ' . $src . ' because size is ' . $srcSize . "\n";
                    }
                    return true;
                }
                if (!copy($src, $dst)) {
                    $output = array();
                    $retval = 0;
                    exec('sudo cp ' . $src . ' ' . $dst, $output, $retval);
                    if ($retval != 0) {
                        if (!$_noError) {
                            return false;
                        } else if (isset($_params['log']) && $_params['log']) {
                            echo 'Error on copy ' . $src . ' to ' . $dst . "\n";
                        }
                    }
                }
                if ($srcSize != filesize($dst)) {
                    if (!$_noError) {
                        return false;
                    } else if (isset($_params['log']) && $_params['log']) {
                        echo 'Error on copy ' . $src . ' to ' . $dst . "\n";
                    }
                }
                return true;
            }
        }
        return true;
    }

    public static function rmove($src, $dst, $_emptyDest = true, $_exclude = array(), $_noError = false, $_params = array()) {
        if (!file_exists($src)) {
            return true;
        }
        if ($_emptyDest) {
            self::rrmdir($dst);
        }
        if (is_dir($src)) {
            if (!file_exists($dst)) {
                @mkdir($dst);
            }
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file != "." && $file != ".." && !in_array($file, $_exclude) && !in_array(realpath($src . '/' . $file), $_exclude)) {
                    if (!self::rmove($src . '/' . $file, $dst . '/' . $file, $_emptyDest, $_exclude, $_noError, $_params) && !$_noError) {
                        return false;
                    }
                }
            }
        } else {
            if (!in_array(basename($src), $_exclude) && !in_array(realpath($src), $_exclude)) {
                $srcSize = filesize($src);
                if (isset($_params['ignoreFileSizeUnder']) && $srcSize < $_params['ignoreFileSizeUnder']) {
                    if (strpos(realpath($src), 'empty') !== false) {
                        return true;
                    }
                    if (strpos(realpath($src), '.git') !== false) {
                        return true;
                    }
                    if (strpos(realpath($src), '.html') !== false) {
                        return true;
                    }
                    if (strpos(realpath($src), '.txt') !== false) {
                        return true;
                    }
                    if (isset($_params['log']) && $_params['log']) {
                        echo 'Ignore file ' . $src . ' because size is ' . $srcSize . "\n";
                    }
                    return true;
                }
                if (!rename($src, $dst)) {
                    $output = array();
                    $retval = 0;
                    exec('sudo mv ' . $src . ' ' . $dst, $output, $retval);
                    if ($retval != 0) {
                        if (!$_noError) {
                            return false;
                        } else if (isset($_params['log']) && $_params['log']) {
                            echo 'Error on move ' . $src . ' to ' . $dst . "\n";
                        }
                    }
                }
                if ($srcSize != filesize($dst)) {
                    if (!$_noError) {
                        return false;
                    } else if (isset($_params['log']) && $_params['log']) {
                        echo 'Error on move ' . $src . ' to ' . $dst . "\n";
                    }
                }
                return true;
            }
        }
        return true;
    }

// removes files and non-empty directories
    public static function rrmdir($dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    self::rrmdir("$dir/$file");
                }
            }
            if (!rmdir($dir)) {
                $output = array();
                $retval = 0;
                exec('sudo rm -rf ' . $dir, $output, $retval);
                if ($retval != 0) {
                    return false;
                }
            }
        } else if (file_exists($dir)) {
            if (!unlink($dir)) {
                $output = array();
                $retval = 0;
                exec('sudo rm -rf ' . $dir, $output, $retval);
                if ($retval != 0) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function createZip($source_arr, $destination, $_excludes = array())
    {
        if (is_string($source_arr)) {
            $source_arr = array($source_arr);
        }
        if (!extension_loaded('zip')) {
            throw new CoreException('Extension php ZIP non chargée');
        }
        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZIPARCHIVE::CREATE)) {
            throw new CoreException('Impossible de créer l\'archive ZIP dans le dossier de destination : ' . $destination);
        }
        foreach ($source_arr as $source) {
            if (!file_exists($source)) {
                continue;
            }
            $source = str_replace('\\', '/', realpath($source));
            if (is_dir($source) === true) {
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);
                foreach ($files as $file) {
                    if (strpos($file, $source) === false) {
                        continue;
                    }
                    if ($file == $source . '/.' || $file == $source . '/..' || in_array(basename($file), $_excludes) || in_array(realpath($file), $_excludes)) {
                        continue;
                    }
                    foreach ($_excludes as $exclude) {
                        if (strpos($file, trim('/' . $exclude . '/', '/')) !== false) {
                            continue (2);
                        }
                    }
                    $file = str_replace('\\', '/', realpath($file));
                    if (is_dir($file) === true) {
                        $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                    } else if (is_file($file) === true) {
                        $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                    }
                }
            } else if (is_file($source) === true) {
                $zip->addFromString(basename($source), file_get_contents($source));
            }
        }
        return $zip->close();
    }

    public static function getDirectorySize($path)
    {
        $totalsize = 0;
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                $nextpath = $path . '/' . $file;
                if ($file != '.' && $file != '..' && !is_link($nextpath)) {
                    if (is_dir($nextpath)) {
                        $totalsize += self::getDirectorySize($nextpath);
                    } elseif (is_file($nextpath)) {
                        $totalsize += filesize($nextpath);
                    }
                }
            }
            closedir($handle);
        }
        return $totalsize;
    }

}
