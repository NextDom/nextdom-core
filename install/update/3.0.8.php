<?php
require_once __DIR__ . '/../../core/php/core.inc.php';
echo 'Move cache and tmp nextdom to new folder (/tmp/nextdom). It can take some times....';
nextdom::stop();
shell_exec('sudo mkdir -p /tmp/nextdom');
shell_exec('sudo rm -rf  /tmp/nextdom/cache;sudo mv /tmp/nextdom-cache /tmp/nextdom/cache');
shell_exec('sudo touch /tmp/nextdom/started');
shell_exec('sudo chmod 777 -R /tmp/nextdom');
nextdom::start();
echo "OK\n";
?>