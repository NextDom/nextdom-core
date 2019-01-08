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
        if ($folder) {
            $current_folder = realpath('.');
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

        if ($folder && is_dir($current_folder)) {
            chdir($current_folder);
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
}
