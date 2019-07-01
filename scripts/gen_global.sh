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

set -e

# Get current directory
set_root() {
    local this=`readlink -n -f $1`
    root=`dirname $this`
}
set_root $0

# Initialise npm
echo " > Initialise NPM"
${root}/gen_composer_npm.sh

# Initialise assets
echo " > Initialise Assets"
${root}/gen_assets.sh

# Initialise docs
if [ "$1" == "--docs" ]; then
    echo " > Initialise Docs"
    ${root}/gen_docs.sh
fi

# Gestion vulnerability or obesolescences
echo " > Cleaning vulnerability and obesolescences"
rm -rf ./vendor/node_modules/morris.js/
