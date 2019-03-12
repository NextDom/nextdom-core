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
 * NextDom Software is free software: you can redistribute it and/or modify
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
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

if (php_sapi_name() != 'cli' || isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['argc'])) {
	header("Statut: 404 Page non trouvée");
	header('HTTP/1.0 404 Not Found');
	$_SERVER['REDIRECT_STATUS'] = 404;
	echo "<h1>404 Non trouvé</h1>";
	echo "La page que vous demandez ne peut être trouvée.";
	exit();
}
echo "==================================================\n";
echo "|    NextDom SICK SCRIPT " . date('Y-m-d H:i:s') . "    |";
echo "\n==================================================\n";

echo "\n**************************************************\n";
echo "*                 VARIABLES                      *";
echo "\n**************************************************\n";
$install_dir = dirname(__DIR__);
$processUser = posix_getpwuid(posix_geteuid());
echo "Dossier d'installation : " . $install_dir . "\n";
echo "User : " . $processUser['name'] . "\n";
if (trim(exec('sudo cat /etc/sudoers')) != "") {
	echo "Sudo : OUI\n";
} else {
	echo "Sudo : NON\n";
}

echo "\n**************************************************\n";
echo "*               DOSSIERS                         *";
echo "\n**************************************************\n";
echo "Charge l'environnement de Jeedom...";
try {
	require_once $install_dir . "/core/php/core.inc.php";
	echo "OK\n";
} catch (\Exeption $e) {
	echo "ERREUR\n";
	echo "Impossible de charger l'environnement de NextDom : " . $e->getMessage();
	echo "\n";
	die();
}

/* Check log dir */
echo "Vérifie si les log sont en écriture...";
if (!file_exists(NEXTDOM_LOG )) {
	echo "introuvable\n";
	echo "Faites : mkdir ". NEXTDOM_LOG . ".'\n";
	die();
}
if (!is_writable(NEXTDOM_LOG)) {
	echo "Impossible d'écrire\n";
	echo "Faites : chown  -R " . $processUser['name'] . " ".NEXTDOM_LOG."\n";
	die();
}
echo "OK\n";

echo "\n**************************************************\n";
echo "*              UTILISATEURS                      *";
echo "\n**************************************************\n";
try {
	$foundAdmin = false;
	foreach (user::all() as $user) {
		echo $user->getLogin();
		echo " => ";
		if ($user->getProfils() == 'admin') {
			$foundAdmin = true;
			echo " Admin\n";
		} else {
			echo " Regular\n";
		}
	}

	if (!$foundAdmin) {
		echo "Aucun utilisateur admin trouvé, veuillez en créer un...";
		$user = (new \user())
			->setLogin('admin')
			->setPassword(sha512('admin'))
			->setProfils('admin', 1);
		$user->save();
		echo "OK (admin/admin)\n";
	}
} catch (Exeption $e) {
	echo "ERREUR\n";
	echo "Description : " . $e->getMessage();
	echo "\n";
	die();
}

echo "\n**************************************************\n";
echo "*                 CRON                           *";
echo "\n**************************************************\n";
echo "Vérifie si cron est actif...";
if (config::byKey('enableCron', 'core', 1, true) == 0) {
	echo "NOK\n";
} else {
	echo "OK\n";
}
echo "Vérifie si scenario est actif...";
if (config::byKey('enableScenario') == 0) {
	echo "NOK\n";
} else {
	echo "OK\n";
}
echo "\n";
echo "NAME | STATE | SCHEDULE | DEAMON | ONCE | LAST RUN\n";
foreach (cron::all() as $cron) {
	echo $cron->getName();
	echo " | ";
	echo $cron->getState();
	echo " | ";
	echo $cron->getSchedule();
	echo " | ";
	echo $cron->getDeamon();
	echo " | ";
	echo $cron->getOnce();
	echo " | ";
	echo $cron->getLastRun();
	echo "\n";
}

echo "\n**************************************************\n";
echo "*                 DATE                           *";
echo "\n**************************************************\n";
echo "Vérifie si la date de NextDom est bonne...";
if (nextdom::isDateOk()) {
	echo "OK";
} else {
	echo "NOK";
}
$cache = cache::byKey('jeedom::lastDate');
echo " (" . $cache->getValue() . ")\n";

echo "\n**************************************************\n";
echo "*                 MESSAGE                        *";
echo "\n**************************************************\n";
echo "DATE | PLUGIN | LOGICALID | MESSAGE\n";
foreach (message::all() as $message) {
	echo $message->getDate();
	echo " | ";
	echo $message->getPlugin();
	echo " | ";
	echo $message->getLogicalId();
	echo " | ";
	echo $message->getMessage();
	echo "\n";
}

echo "\n**************************************************\n";
echo "*                 PLUGIN                         *";
echo "\n**************************************************\n";
echo "ID | NAME | STATE\n";
foreach (plugin::listPlugin() as $plugin) {
	echo $plugin->getId();
	echo " | ";
	echo $plugin->getName();
	echo " | ";
	echo $plugin->isActive();
	echo "\n";
}

foreach (plugin::listPlugin() as $plugin) {
	if (method_exists($plugin->getId(), 'sick')) {
		echo "\n**************************************************\n";
		echo "*          SICK  " . $plugin->getId() . "         *";
		echo "\n**************************************************\n";
		$plugin_id = $plugin->getId();
		$plugin_id::sick();
	}
}

echo "\n\n";
echo "\n==================================================\n";
echo "|         TOUTES LES VERIFICATIONS SONT FAITES    |";
echo "\n==================================================\n";
