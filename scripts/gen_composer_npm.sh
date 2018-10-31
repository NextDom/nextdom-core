#!/bin/bash
# This file is part of NextDom Software.
#
# NextDom is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# NextDom Software is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
# function install_nodemodules

currentDir=$PWD
scriptDir=$(dirname -- "$(readlink -f -- "$BASH_SOURCE")")
nextdomDir=$scriptDir/../

function install_nodemodules {
	echo " >>> Installation des modules npm"
	cp $nextdomDir/package.json $nextdomDir/vendor
	npm install --prefix $nextdomDir/vendor
}

function install_dep_composer {
	echo " >>> Installation des dependances composer"
	cd $nextdomDir
	composer install
	cd $currentDir
}

function init_dependencies {
	npm --version > /dev/null 2>&1
	if [ $? -ne 0 ]; then
		echo " >>> Installation de node et npm"
		cd $scriptDir
		wget https://deb.nodesource.com/setup_10.x -O install_npm.sh
		bash install_npm.sh
		cd $currentDir
		apt install -y nodejs
	fi
	sass --version > /dev/null 2>&1
	if [ $? -ne 0 ]; then
		echo " >>> Installation de sass"
		npm install -g sass
	fi
	python -c "import jsmin" 2>&1 /dev/null
	if [ $? -ne 0 ]; then
	    . /etc/os-release
	    if [[ "$NAME" == *Debian* ]]; then
	        apt install -y python-jsmin;
	    else
	        pip install jsmin;
	    fi
	fi
}

init_dependencies
install_dep_composer
install_nodemodules
