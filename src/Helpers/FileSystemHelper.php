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

/**
 * Class FileSystemHelper
 * @package NextDom\Helpers
 */
class FileSystemHelper
{
    /**
     * Inclut un fichier à partir de son type et son nom.
     * @TODO: Doit être revue
     * @param string $_folder Répertoire du fichier
     * @param string $_filename Nom du fichier
     * @param string $_type Type de fichier
     * @param string $_plugin Nom du plugin ou vide pour le core
     * @param bool $translate
     * @throws CoreException
     */
    public static function includeFile($_folder, $_filename, $_type, $_plugin = '', $translate = false)
    {
        if (strpos($_folder, '..') !== false || strpos($_filename, '..') !== false) {
            return;
        }        // Aucune particularité pour les 3rdparty
        if ($_folder == '3rdparty') {
            if ($_plugin === '') {
                $file = sprintf("%s/%s.%s", $_folder, $_filename, $_type);
                $_folder = null;
                $_filename = self::getAssetPath($file);
                if (null === $_filename) {
                    $_filename = 'assets/3rdparty/' . $_filename . '.' . $_type;
                }
            } else {
                $_filename .= '.' . $_type;
            }
            $type = $_type;
        } else {
            // Tableau de mappage des fichiers
            $config = [
                'class' => ['/class', '.class.php', 'php'],
                'com' => ['/com', '.com.php', 'php'],
                'repo' => ['/repo', '.repo.php', 'php'],
                'config' => ['/config', '.config.php', 'php'],
                'modal' => ['/modal', '.php', 'php'],
                'modalhtml' => ['/modal', '.html', 'php'],
                'php' => ['/php', '.php', 'php'],
                'css' => ['/css', '.css', 'css'],
                'js' => ['/js', '.js', 'js'],
                'class.js' => ['/js', '.class.js', 'js'],
                'custom.js' => ['/custom', 'custom.js', 'js'],
                'custom.css' => ['/custom', 'custom.css', 'css'],
                'themes.js' => ['/themes', '.js', 'js'],
                'themes.css' => ['/themes', '.css', 'css'],
                'api' => ['/api', '.api.php', 'php'],
                'html' => ['/html', '.html', 'php'],
                'configuration' => ['', '.php', 'php'],
            ];
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
        } else {
            $path = NEXTDOM_ROOT . '/' . $_folder . '/' . $_filename;
        }
        if (!file_exists($path)) {
            throw new CoreException('Fichier introuvable : ' . Utils::secureXSS($path), 35486);
        }
        if ($type == 'php') {
            // Les fichiers php sont traduits
            if ($_type != 'class') {
                ob_start();
                require_once $path;
                if ($translate) {
                    echo TranslateHelper::exec(ob_get_clean(), $_folder . '/' . $_filename);
                } else {
                    echo ob_get_clean();
                }
            } else {
                require_once $path;
            }
        } elseif ($type == 'css') {
            // @TODO : MD5
            echo '<link href="' . $_folder . '/' . $_filename . '?md5=' . md5_file($path) . '" rel="stylesheet" />';
        } elseif ($type == 'js') {
            // @TODO : MD5
            echo '<script type="text/javascript" src="src/Api/getResource.php?file=' . $_folder . '/' . $_filename . '&md5=' . md5_file($path) . '&lang=' . TranslateHelper::getLanguage() . '"></script>';
        }
    }

    /**
     * Returns paths to requested 3rdparty file with jeedom backward compatibility
     *
     * The function checks that returned file belongs to nextdom-core root directory
     *
     * @param string $path requested path under folder
     * @return null|string|string[]
     */
    public static function getAssetPath($path)
    {
        $staticMapping = [
            '3rdparty/bootstrap.slider/css/slider' => 'vendor/node_modules/bootstrap-slider/dist/css/bootstrap-slider.min',
            '3rdparty/bootstrap.slider/js/bootstrap-slider' => 'vendor/node_modules/bootstrap-slider/dist/bootstrap-slider.min',
            '3rdparty/bootstrap/css/bootstrap.min' => 'vendor/node_modules/bootstrap/dist/css/bootstrap.min',
            '3rdparty/bootstrap/js/bootstrap.min' => 'vendor/node_modules/bootstrap/dist/js/bootstrap.min',
            '3rdparty/codemirror/lib/codemirror' => 'vendor/node_modules/codemirror/lib/codemirror',
            '3rdparty/datetimepicker/jquery.datetimepicker' => 'vendor/node_modules/jquery-datetimepicker/jquery.datetimepicker',
            '3rdparty/highstock/highcharts-more' => 'vendor/node_modules/highcharts/highcharts-more',
            '3rdparty/highstock/highstock' => 'vendor/node_modules/highcharts/highstock',
            '3rdparty/jquery.fileupload/jquery.fileupload' => 'vendor/node_modules/blueimp-file-upload/js/jquery.fileupload',
            '3rdparty/jquery.fileupload/jquery.iframe-transport' => 'vendor/node_modules/blueimp-file-upload/js/jquery.iframe-transport',
            '3rdparty/jquery.fileupload/jquery.ui.widget' => 'vendor/node_modules/blueimp-file-upload/js/vendor/jquery.ui.widget',
            '3rdparty/jquery.lazyload/jquery.lazyload' => 'vendor/node_modules/jquery-lazyload/jquery.lazyload',
            '3rdparty/jquery.packery/jquery.packery' => 'vendor/node_modules/packery/dist/packery.pkgd',
            '3rdparty/jquery.tablesorter/jquery.tablesorter.min' => 'vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.min',
            '3rdparty/jquery.tablesorter/jquery.tablesorter.widgets.min' => 'vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.widgets.min',
            '3rdparty/jquery.tablesorter/theme.bootstrap' => 'vendor/node_modules/tablesorter/dist/css/theme.bootstrap.min',
            '3rdparty/jquery.ui/jquery-ui.min' => 'vendor/node_modules/jquery-ui-dist/jquery-ui.min',
            '3rdparty/jquery/jquery.min' => 'vendor/node_modules/jquery/dist/jquery.min',
            '3rdparty/roboto/roboto' => 'vendor/node_modules/roboto-fontface/css/roboto-fontface',
            '3rdparty/waves/waves.min' => 'vendor/node_modules/node-waves/waves.min',
            '3rdparty/jquery.ui/jquery-ui-bootstrap/jquery-ui' => 'vendor/node_modules/jquery-ui-bootstrap/jquery.ui.theme'
        ];
        $reMapping = [
            '%3rdparty/codemirror/(mode|addon)/(.*)%' => 'vendor/node_modules/codemirror/${1}/${2}'
        ];

        $pathinfo = pathinfo($path);
        $extension = Utils::array_key_default($pathinfo, "extension", "");
        $dirname = Utils::array_key_default($pathinfo, "dirname", "");
        $filename = Utils::array_key_default($pathinfo, "filename", "");
        $needle = sprintf("%s/%s", trim($dirname, "/"), $filename);
        $mappedValue = Utils::array_key_default($staticMapping, $needle, false);
        $staticValue = sprintf("assets/%s.%s", $needle, $extension);

        if (false !== $mappedValue) {
            // try conversion from static mapping
            $path = sprintf("%s.%s", $mappedValue, $extension);
        } elseif (true === file_exists(NEXTDOM_ROOT . '/' . $staticValue)) {
            // try conversion existing asset file
            $path = $staticValue;
        } else {
            // try conversion from regexp mapping (slowest mode)
            foreach ($reMapping as $c_match => $c_replace) {
                $path = preg_replace($c_match, $c_replace, $needle);
                if (($path !== null) && ($path !== $needle)) {
                    $path = sprintf("%s.%s", $path, $extension);
                    break;
                }
            }
        }

        // ensure that returned file belongs to NEXTDOM_ROOT
        $abspath = realpath(NEXTDOM_ROOT . '/' . $path);
        if ((false === $abspath) ||
            (0 !== strpos($abspath, NEXTDOM_ROOT))) {
            return null;
        }
        return $path;
    }

    /**
     * Read content of a core template file
     * @param string $version View version
     * @param string $filename Name of the template file
     * @param string $pluginId Plugin (todo: remove)
     * @param string $theme Theme if necessary
     * @return string
     */
    public static function getCoreTemplateFileContent($version, $filename, $pluginId = '', $theme = ''): string
    {
        return self::getTemplateFileContent('views', $version, $filename, $pluginId, $theme);
    }

    /**
     * Obtenir le contenu d'un fichier template.
     *
     * @param string $folder Répertoire dans lequel se trouve le fichier de template
     * @param string $version Version du template
     * @param string $filename Nom du fichier
     * @param string $pluginId Identifiant du plugin
     * @param string $theme Identifiant du plugin
     *
     * @return string Contenu du fichier ou une chaine vide.
     */
    public static function getTemplateFileContent($folder, $version, $filename, $pluginId = '', $theme = ''): string
    {
        $result = '';
        $filePath = NEXTDOM_ROOT . '/';
        if ($pluginId == '') {
            if ($folder === 'core') {
                $folder = 'views';
            }
            if ($theme == '') {
                $filePath .= $folder . '/templates/' . $version . '/' . $filename . '.html';
            } else {
                $filePath .= $folder . '/templates/' . $version . '/themes/' . $theme . '/' . $filename . '.html';
            }
        } else {
            $filePath .= 'plugins/' . $pluginId . '/core/template/' . $version . '/' . $filename . '.html';
        }
        if (file_exists($filePath)) {
            $result = file_get_contents($filePath);
        }
        return $result;
    }

    /**
     * @param $allowPath
     * @param $path
     * @return bool
     */
    public static function hadFileRight($allowPath, $path): bool
    {
        $path = Utils::cleanPath($path);
        foreach ($allowPath as $right) {
            if (strpos($right, '/') !== false || strpos($right, '\\') !== false) {
                if (strpos($right, '/') !== 0 || strpos($right, '\\') !== 0) {
                    $right = NEXTDOM_ROOT . '/' . $right;
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

    /**
     * Get content list of a folder
     *
     * @param string $folder Folder to list
     * @param string $pattern Pattern for filtering (*.php, *test*, etc.)
     * @param bool $recursivly List all files/folders in subfolders
     * @param array $options Limit files or folders
     *
     * @return array Content list
     */
    public static function ls($folder = "", $pattern = "*", $recursivly = false, $options = ['files', 'folders'])
    {
        $currentFolder = '';
        if ($folder) {
            $currentFolder = realpath('.');
            if (in_array('quiet', $options)) {
                // If quiet is on, we will suppress the 'no such folder' error
                if (!file_exists($folder)) {
                    return [];
                }

            }
            if (!is_dir($folder) || !chdir($folder)) {
                return [];
            }

        }
        $getFiles = in_array('files', $options);
        $getFolders = in_array('folders', $options);
        $both = [];
        $folders = [];
        // Get the all files and folders in the given directory.
        if ($getFiles) {
            $both = [];
            foreach (Utils::globBrace($pattern, GLOB_MARK) as $file) {
                if (!is_dir($folder . '/' . $file)) {
                    $both[] = $file;
                }
            }
        }
        if ($recursivly || $getFolders) {
            $folders = glob("*", GLOB_ONLYDIR + GLOB_MARK);
        }

        //If a pattern is specified, make sure even the folders match that pattern.
        $matching_folders = [];
        if ($pattern !== '*') {
            $matching_folders = glob($pattern, GLOB_ONLYDIR + GLOB_MARK);
        }

        //Get just the files by removing the folders from the list of all files.
        $all = array_values(array_diff($both, $folders));
        if ($recursivly || $getFolders) {
            foreach ($folders as $this_folder) {
                if ($getFolders) {
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
                    $folderItems = self::ls($this_folder, $pattern, $recursivly, $options); # :RECURSION:
                    foreach ($folderItems as $item) {
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

    /**
     * @param       $src
     * @param       $dst
     * @param bool $_emptyDest
     * @param array $_exclude
     * @param bool $_noError
     * @param array $_params
     * @return bool
     */
    public static function rcopy($src, $dst, $_emptyDest = true, $_exclude = [], $_noError = false, $_params = [])
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
                    $output = [];
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
                    } elseif (isset($_params['log']) && $_params['log']) {
                        echo 'Error on copy ' . $src . ' to ' . $dst . "\n";
                    }
                }
                return true;
            }
        }
        return true;
    }

    /**
     * @abstract removes files and non-empty directories
     * @param $dir
     * @return bool
     */
    public static function rrmdir($dir): bool
    {
        // Check if we remove our own folders/files only
        if (!(substr($dir, 0, strlen(NEXTDOM_ROOT)) === NEXTDOM_ROOT
            || substr($dir, 0, strlen(NEXTDOM_DATA)) === NEXTDOM_DATA
            || substr($dir, 0, strlen(NEXTDOM_LOG)) === NEXTDOM_LOG
            || substr($dir, 0, strlen(NEXTDOM_TMP)) === NEXTDOM_TMP
            || substr($dir, 0, strlen('/tmp')) === '/tmp')) {

            return false;
        }

        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    self::rrmdir("$dir/$file");
                }
            }
            if (!is_writable($dir) || !rmdir($dir)) {
                $output = [];
                $retval = 0;
                exec('sudo rm -rf ' . $dir, $output, $retval);
                if ($retval != 0) {
                    return false;
                }
            }
        } elseif (file_exists($dir)) {
            if (!is_writable($dir) || !unlink($dir)) {
                $output = [];
                $retval = 0;
                exec('sudo rm -rf ' . $dir, $output, $retval);
                if ($retval != 0) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @abstract removes file
     * @param $file
     * @return bool
     */
    public static function rrmfile($file): bool
    {
        if (file_exists($file)) {
            return unlink($file);
        }
        return false;
    }

    /**
     * @param       $src
     * @param       $dst
     * @param bool $_emptyDest
     * @param array $_exclude
     * @param bool $_noError
     * @param array $_params
     * @return bool
     */
    public static function rmove($src, $dst, $_emptyDest = true, $_exclude = [], $_noError = false, $_params = [])
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
                    $output = [];
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

    /**
     * @param       $source_arr
     * @param       $destination
     * @param array $_excludes
     * @return bool
     * @throws CoreException
     */
    public static function createZip($source_arr, $destination, $_excludes = [])
    {
        if (is_string($source_arr)) {
            $source_arr = [$source_arr];
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

    /**
     * @param $path
     * @return false|int
     */
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

    /**
     * Get the free space of a directory
     * @param string $directory Directory in which we want free space information
     * @return int free space in Byte (Octet)
     */
    public static function getDirectoryFreeSpace($directory)
    {
        return disk_free_space($directory);
    }


    /**
     * Get true if the file exists
     * @param string $file File we want to know if exists
     * @return bool exists or not
     */
    public static function isFileExists($file)
    {
        return file_exists($file);
    }

    /**
     * Moves input file or directory to given destination (acts like mv)
     *
     * @param string $src source file or directory
     * @param string $dst destination file or directory
     * @return bool true if no error
     */
    public static function mv($src, $dst): bool
    {
        $status = -1;
        $cmd = sprintf("mv %s %s", $src, $dst);
        system($cmd, $status);
        return ($status === 0);
    }

    /**
     * Create directory if not already exists
     *
     * @param string $path , path to create
     * @param int $mode , see mkdir parameter
     * @param boolean $recursive , see mkdir parameter
     * @throws CoreException when cannot create directory
     */
    public static function mkdirIfNotExists($path, $mode = 0775, $recursive = false)
    {
        if (false === is_dir($path)) {
            if (false === mkdir($path, $mode, $recursive)) {
                throw new CoreException("unable to create directory : " . $path);
            }
        }
    }

}
