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

set -e

set_root() {
    local this=`readlink -n -f $1`
    root=`dirname $this`
}
set_root $0

function run_as_superuser {
  cmd=$@
  if [ -z "${TRAVIS}" ] && [ ${EUID} != "0" ]; then
      sudo $@
  else
    $@
  fi
}

function install_nodemodules {
  echo " >>> Installing the npm modules"
  [[ ! -d ./vendor ]] && mkdir vendor
  cp package.json ./vendor/
  npm install --unsafe-perm --no-save --prefix ./vendor
}

function install_dep_composer {
  echo " >>> Installation dependencies composer"
  if [[ "$1" = "--no-dev" ]] ; then
      composer install -o --no-dev
  else
      composer install
  fi
}

function init_dependencies {
	npm --version > /dev/null 2>&1 || {
		echo " >>> Installation of node and npm"
    tmpFile=$(mktemp)
		wget -q https://deb.nodesource.com/setup_10.x -O ${tmpFile}
    run_as_superuser bash ${tmpFile}
		run_as_superuser apt install -y nodejs
    rm -f ${tmpFile}
  }

	sass --version > /dev/null 2>&1 || {
		echo " >>> Installation of sass"
    run_as_superuser npm install -g sass
	}

	python -c "import jsmin" 2>&1 /dev/null || {
	  . /etc/os-release
	  if [[ "$NAME" == *Debian* ]]; then
	      run_as_superuser apt install -y python-jsmin;
	  else
	    run_as_superuser pip install jsmin;
	  fi
  }
}

cd ${root}/..

init_dependencies
install_dep_composer --no-dev
install_nodemodules
