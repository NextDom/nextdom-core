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
echo "[START MIGRATION]\n";
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
    echo "***************Début de la migration de jeedom vers nextdom " . date('Y-m-d H:i:s') . "***************\n";

    try {
        echo "Envoie l'événement de début de la migration...";
        nextdom::event('begin_restore', true);
        echo "OK\n";
    } catch (Exception $e) {
        echo '***ERREUR*** ' . $e->getMessage();
    }

    global $CONFIG;
    global $BACKUP_FILE;
    if (isset($BACKUP_FILE)) {
        $_GET['backup'] = $BACKUP_FILE;
    }
    if (!isset($_GET['backup']) || $_GET['backup'] == '') {
        if (substr(config::byKey('backup::path'), 0, 1) != '/') {
            $backup_dir = dirname(__FILE__) . '/../' . config::byKey('backup::path');
        } else {
            $backup_dir = config::byKey('backup::path');
        }
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir);
        }
        $backup = null;
        $mtime = null;
        foreach (scandir($backup_dir) as $file) {
            if ($file != "." && $file != ".." && $file != ".htaccess" && strpos($file, '.tar.gz') !== false) {
                $s = stat($backup_dir . '/' . $file);
                if ($backup === null || $mtime === null) {
                    $backup = $backup_dir . '/' . $file;
                    $mtime = $s['mtime'];
                }
                if ($mtime < $s['mtime']) {
                    $backup = $backup_dir . '/' . $file;
                    $mtime = $s['mtime'];
                }
            }
        }
    } else {
        $backup = $_GET['backup'];
    }
    if (substr($backup, 0, 1) != '/') {
        $backup = __DIR__ . '/../' . $backup;
    }

    if (!file_exists($backup)) {
        throw new Exception('Backup not found.' . $backup);
    }

    try {
        echo "Vérifiez les droits...";
        nextdom::cleanFileSytemRight();
        echo "OK\n";
    } catch (Exception $e) {
        echo '***ERREUR*** ' . $e->getMessage();
    }

    $nextdom_dir = realpath(__DIR__ . '/../');

    echo "Fichier utilisé pour la restauration : " . $backup . "\n";

    echo "Backup database access configuration...";

    echo "OK\n";

    try {
        nextdom::stop();
    } catch (Exception $e) {
        $e->getMessage();
    }

    echo "Décompression de la sauvegarde...";
    	$excludes = array();
	$exclude = '';
    foreach ($excludes as $folder) {
        $exclude .= ' --exclude="' . $folder . '"';
    }
    $rc = 0;
  	system('mkdir -p /tmp/nextdombackup');
    system('cd /tmp/nextdombackup; rm * -rf; tar xfz "' . $backup . '" ' . $exclude);

    echo "OK\n";
    if (!file_exists("/tmp/nextdombackup/DB_backup.sql")) {
        throw new Exception('Impossible de trouver le fichier de la base de données de la sauvegarde : DB_backup.sql');
    } else {
        shell_exec("sed -i -e s/jeedom/nextdom/g /tmp/nextdombackup/DB_backup.sql");
    }
    echo "Supprimer la table de la sauvegarde";
    $tables = DB::Prepare("SHOW TABLES", array(), DB::FETCH_TYPE_ALL);
    echo "Désactive les contraintes...";
    DB::Prepare("SET foreign_key_checks = 0", array(), DB::FETCH_TYPE_ROW);
    echo "OK\n";
    foreach ($tables as $table) {
        $table = array_values($table);
        $table = $table[0];
        echo "Supprimer la table : " . $table . '...';
        DB::Prepare('DROP TABLE IF EXISTS `' . $table . '`', array(), DB::FETCH_TYPE_ROW);
        echo "OK\n";
    }

    echo "Restauration de la base de données...";
    shell_exec("mysql --host=" . $CONFIG['db']['host'] . " --port=" . $CONFIG['db']['port'] . " --user=" . $CONFIG['db']['username'] . " --password=" . $CONFIG['db']['password'] . " " . $CONFIG['db']['dbname'] . " < /tmp/nextdombackup/DB_backup.sql");
    echo "OK\n";

    echo "Mise a jour SQL...";
    echo shell_exec('php ' . __DIR__ . '/migrate/migrate.php');
    echo "OK\n";
    echo "Active les contraintes...";
    try {
        DB::Prepare("SET foreign_key_checks = 1", array(), DB::FETCH_TYPE_ROW);
    } catch (Exception $e) {

    }
    echo "OK\n";

   	if (copy(__DIR__ . '/../core/config/jeedom.config.php', '/tmp/nextdom.config.php')) {
        echo 'Can not copy ' . __DIR__ . "/../core/config/common.config.php\n";
    }
    if (!file_exists(__DIR__ . '/../core/config/nextdom.config.php')) {
        echo "Restauration du fichier de configuration de la base de données...";
        copy('/tmp/nextdombackup/nextdom.config.php', __DIR__ . '/../core/config/common.config.php');
        echo "OK\n";
    }

    echo "Restauration des droits...";
    system('chmod 1777 /tmp -R');
    echo "OK\n";
    
  	echo "Restauration du cache...";
  	system('cd /tmp/nextdom/cache; tar xfz "/tmp/nextdombackup/data/cache.tar.gz"');
	echo "OK\n";

	echo "Restauration des plugins...";
  	system('cp -fr /tmp/nextdombackup/plugins/* ' . $nextdom_dir . '/plugins' );
    foreach (plugin::listPlugin(true) as $plugin) {
        $plugin_id = $plugin->getId();
        $dependancy_info = $plugin->dependancy_info(true);
        if (method_exists($plugin_id, 'restore')) {
            echo 'Plugin restoration : ' . $plugin_id . '...';
            $plugin_id::restore();
            echo "OK\n";
        }
    }
  	echo "OK\n";

    echo "Mise à jour SQL post plugins";
    shell_exec('php ' . __DIR__ . '/../install/migrate/migrate.php');
    echo "OK\n";

    config::save('hardware_name', '');
    $cache = cache::byKey('nextdom::isCapable::sudo');
    $cache->remove();

    try {
        echo "Check nextdom consistency...";
        require_once __DIR__ . '/consistency.php';
        echo "OK\n";
    } catch (Exception $ex) {
        echo "***ERREUR*** " . $ex->getMessage() . "\n";
    }

    echo "Restauration des droits...";
    shell_exec('chmod 775 -R ' . __DIR__ );
    shell_exec('chown -R www-data:www-data ' . __DIR__ );
    shell_exec('chmod 777 -R /tmp/');
    shell_exec('chown www-data:www-data -R /tmp/');
    echo "OK\n";

    try {
        nextdom::start();
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    try {
        echo "Envoie l'événement de la fin de la sauvegarde...";
        nextdom::event('end_migration');
        echo "OK\n";
    } catch (Exception $e) {
        echo '***ERREUR*** ' . $e->getMessage();
    }
    echo "Temps de la migration : " . (strtotime('now') - $starttime) . "s\n";
    echo "***************Fin de la restauration de NextDom***************\n";
    echo "[END MIGRATION SUCCESS]\n";
} catch (Exception $e) {
    echo 'Erreur durant la migration : ' . $e->getMessage();
    echo 'Détails : ' . print_r($e->getTrace(), true);
    echo "[END MIGRATION ERROR]\n";
    nextdom::start();
    throw $e;
}
