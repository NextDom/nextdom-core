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

if (php_sapi_name() != 'cli' || isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['argc'])) {
    header("Statut: 404 Page non trouvée");
    header('HTTP/1.0 404 Not Found');
    $_SERVER['REDIRECT_STATUS'] = 404;
    echo "<h1>404 Non trouvé</h1>";
    echo "La page que vous demandez ne peut être trouvée.";
    exit();
}

echo  "[ BACKUP START ]" . "\n";
$starttime = strtotime('now');
if (isset($argv)) {
    foreach ($argv as $arg) {
        $argList = explode('=', $arg);
        if (isset($argList[0]) && isset($argList[1])) {
            $_GET[$argList[0]] = $argList[1];
        }
    }
}

try {
    require_once __DIR__ . '/../core/php/core.inc.php';
    echo "*************** " . "Starting procedure at " . date('Y-m-d H:i:s') . " ***************\n";

    try {
        echo "Sends the start event of the backup...";
        nextdom::event('begin_backup', true);
        echo " OK" . "\n";
    } catch (Exception $e) {
        echo " OK" . "\n";
        log::add('backup', 'error', $e->getMessage());
        echo '*** ' . "ERROR" . '*** ' . $e->getMessage();
    }

    try {
        echo "Checking rights...";
        nextdom::cleanFileSytemRight();
        echo " OK" . "\n";
    } catch (Exception $e) {
        echo " NOK" . "\n";
        log::add('backup', 'error', $e->getMessage());
        echo '*** ' . "ERROR" . '*** ' . $e->getMessage();
    }

    global $CONFIG;
    $nextdom_dir = realpath(__DIR__ . '/..');
    $backup_dir = calculPath(config::byKey('backup::path'));
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0770, true);
    }
    if (!is_writable($backup_dir)) {
        throw new Exception('Cannot access to the backup directory, check the rights of : ' . $backup_dir);
    }
    $replace_name = array(
        '&' => '',
        ' ' => '_',
        '#' => '',
        "'" => '',
        '"' => '',
        '+' => '',
        '-' => '',
    );
    $nextdom_name = str_replace(array_keys($replace_name), $replace_name, config::byKey('name', 'core', 'NextDom'));
    $backup_name = str_replace(' ', '_', 'backup-' . $nextdom_name . '-' . nextdom::version() . '-' . date("Y-m-d-H\hi") . '.tar.gz');

    echo "Start plugins backup...";
    global $NO_PLUGIN_BACKUP;
    if (!isset($NO_PLUGIN_BACKUP) || $NO_PLUGIN_BACKUP === false) {
        foreach (plugin::listPlugin(true) as $plugin) {
            $plugin_id = $plugin->getId();
            if (method_exists($plugin_id, 'backup')) {
                echo "Plugin backup : " . $plugin_id . '...';
                $plugin_id::backup();
                echo " OK" . "\n";
            }
        }
    }

    echo "Checking database...";
    system("mysqlcheck --host=" . $CONFIG['db']['host'] . " --port=" . $CONFIG['db']['port'] . " --user=" . $CONFIG['db']['username'] . " --password='" . $CONFIG['db']['password'] . "' " . $CONFIG['db']['dbname'] . ' --auto-repair --silent');
    echo " OK" . "\n";

    echo "Database backup...";
    if (file_exists($nextdom_dir . "/DB_backup.sql")) {
        unlink($nextdom_dir . "/DB_backup.sql");
        if (file_exists($nextdom_dir . "/DB_backup.sql")) {
            system("sudo rm " . $nextdom_dir . "/DB_backup.sql");
        }
    }
    if (file_exists($nextdom_dir . "/DB_backup.sql")) {
        throw new Exception('Cannot delete the database backup directory, check the rights.');
    }
    system("mysqldump --host=" . $CONFIG['db']['host'] . " --port=" . $CONFIG['db']['port'] . " --user=" . $CONFIG['db']['username'] . " --password='" . $CONFIG['db']['password'] . "' " . $CONFIG['db']['dbname'] . "  > " . $nextdom_dir . "/DB_backup.sql", $rc);
    if ($rc != 0) {
        throw new Exception('Error during database backup, check that mysqldump is installed. Error code : ' . $rc);
    }
    if (filemtime($nextdom_dir . "/DB_backup.sql") < (strtotime('now') - 1200)) {
        throw new Exception('Error during database backup, the database backup file is too old.');
    }
    echo " OK" . "\n";

    echo "Persistent cache backup...";
    try {
        cache::persist();
        echo " OK" . "\n";
    } catch (Exception $e) {
        echo " NOK" . "\n";
        log::add('backup', 'error', $e->getMessage());
        echo '*** ' . "ERROR" . '*** ' . $e->getMessage();
    }

    echo "Archive backup creation...";
    system('cd ' . $nextdom_dir . ';tar cfz "' . $backup_dir . '/' . $backup_name . '" ' . $exclude . ' -T ' . $nextdom_dir . '/install/backup_include_files > /dev/null');
    if (!file_exists($backup_dir . '/' . $backup_name)) {
        echo " NOK" . "\n";
        throw new Exception('Backup error, cannot locate : ' . $backup_dir . '/' . $backup_name);
    }
    echo " OK" . "\n";

    echo "Cleaning old backup...";
    shell_exec('find "' . $backup_dir . '" -mtime +' . config::byKey('backup::keepDays') . ' -delete');
    echo " OK" . "\n";

    echo "Limitation the backup folder size by deleting files to " . config::byKey('backup::maxSize') . " Mo...\n";
    $max_size = config::byKey('backup::maxSize') * 1024 * 1024;
    $i = 0;
    while (getDirectorySize($backup_dir) > $max_size) {
        $older = array('file' => null, 'datetime' => null);

        foreach (ls($backup_dir, '*') as $file) {
            if (count(ls($backup_dir, '*')) < 2) {
                break (2);
            }
            if (is_dir($backup_dir . '/' . $file)) {
                foreach (ls($backup_dir . '/' . $file, '*') as $file2) {
                    if ($older['datetime'] === null) {
                        $older['file'] = $backup_dir . '/' . $file . '/' . $file2;
                        $older['datetime'] = filemtime($backup_dir . '/' . $file . '/' . $file2);
                    }
                    if ($older['datetime'] > filemtime($backup_dir . '/' . $file . '/' . $file2)) {
                        $older['file'] = $backup_dir . '/' . $file . '/' . $file2;
                        $older['datetime'] = filemtime($backup_dir . '/' . $file . '/' . $file2);
                    }
                }
            }
            if (!is_file($backup_dir . '/' . $file)) {
                continue;
            }
            if ($older['datetime'] === null) {
                $older['file'] = $backup_dir . '/' . $file;
                $older['datetime'] = filemtime($backup_dir . '/' . $file);
            }
            if ($older['datetime'] > filemtime($backup_dir . '/' . $file)) {
                $older['file'] = $backup_dir . '/' . $file;
                $older['datetime'] = filemtime($backup_dir . '/' . $file);
            }
        }
        if ($older['file'] === null) {
            echo 'No file deleting necessary because of directory size : ' . getDirectorySize($backup_dir) . "\n";
        }
        echo "Deleting of : " . $older['file'] . "\n";
        if (!unlink($older['file'])) {
            $i = 50;
        }
        $i++;
        if ($i > 50) {
            echo "More than 50 backup file deleted, so Stop.\n";
            break;
        }
    }
    echo " OK" . "\n";
    global $NO_CLOUD_BACKUP;
    if ((!isset($NO_CLOUD_BACKUP) || $NO_CLOUD_BACKUP === false)) {
        foreach (update::listRepo() as $key => $value) {
            if ($value['scope']['backup'] === false) {
                continue;
            }
            if (config::byKey($key . '::enable') == 0) {
                continue;
            }
            if (config::byKey($key . '::cloudUpload') == 0) {
                continue;
            }
            $class = 'repo_' . $key;
            echo 'Send backup ' . $value['name'] . '...';
            try {
                $class::backup_send($backup_dir . '/' . $backup_name);
                echo " OK" . "\n";
            } catch (Exception $e) {
                echo " OK" . "\n";
                log::add('backup', 'error', $e->getMessage());
                echo '*** ' . "ERROR" . '*** ' . br2nl($e->getMessage()) . "\n";
            }
        }
    }
    echo "Backup name : " . $backup_dir . '/' . $backup_name . "\n";

    try {
        echo "Restoring rights...";
        nextdom::cleanFileSytemRight();
        echo " OK" . "\n";
    } catch (Exception $e) {
        echo " NOK" . "\n";
        log::add('backup', 'error', $e->getMessage());
        echo '*** ' . "ERROR" . '*** ' . $e->getMessage() . "\n";
    }

    try {
        echo "Sends the end event of the backup...";
        nextdom::event('end_backup');
        echo " OK" . "\n";
    } catch (Exception $e) {
        echo " NOK" . "\n";
        log::add('backup', 'error', $e->getMessage());
        echo '*** ' . "ERROR" . '*** ' . $e->getMessage() . "\n";
    }
    echo "Time of backup : " . (strtotime('now') - $starttime) . "s\n";
    echo "*************** " . "End of procedure at " . date('Y-m-d H:i:s') . "***************\n";
    echo "[ BACKUP END ]";
    echo " > SUCCES" . "\n";
    /* Ne pas supprimer la ligne suivante */
    echo "Closing with success" . "\n";
} catch (Exception $e) {
    echo "[ BACKUP END ]";
    echo "\n > ERREUR : " . br2nl($e->getMessage()) . "\n";
    echo "Details : " . print_r($e->getTrace(), true) . "\n";
    /* Ne pas supprimer la ligne suivante */
    echo "Closing with error" . "\n";
    throw $e;
}
