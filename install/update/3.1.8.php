<?php
require_once dirname(__FILE__) . '/../../core/php/core.inc.php';
foreach (interactDef::all() as $interactDef) {
	$interactDef->setEnable(1);
	$interactDef->save();
}
if (file_exists('/media/boot/multiboot/meson64_odroidc2.dtb.linux')) {
	echo 'add nextdom repo gpg key';
	exec('sudo wget -O - http://repo.nextdom.com/odroid/conf/nextdom.gpg.key | sudo apt-key add -');
	echo 'Update NextDom repository';
	exec('sudo rm -rf /etc/apt/sources.list.d/nextdom.list');
	exec('sudo apt-add-repository "deb http://repo.nextdom.com/odroid/ stable main"');
	echo " OK\n";
	echo 'Update APT sources\n';
	exec('sudo apt-get update');
}
